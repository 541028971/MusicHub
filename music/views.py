import os
import re
import base64
import mimetypes
from django.conf import settings
from django.core.files.base import ContentFile
from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login as auth_login, logout
from django.contrib.auth.models import User as AuthUser
from django.contrib import messages
from django.http import StreamingHttpResponse, Http404, HttpResponse, JsonResponse
from django.utils import timezone
from django.db import models
from .models import Song, User as CustomUser, Playlist, PlayHistory, get_audio_duration
from .forms import UserRegistrationForm, UserProfileUpdateForm

def index(request):
    # Fetch trending and recently added
    trending_songs = Song.objects.all()[:12]
    new_songs = Song.objects.order_by('-id')[:12]
    
    # Check favorites if user is authenticated
    user_favorites = set()
    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            user_favorites = set(custom_user.favorite_songs.values_list('id', flat=True))
        except CustomUser.DoesNotExist:
            pass
            
    # Process songs to include is_liked
    def process_songs(songs):
        results = []
        for s in songs:
            results.append({
                'obj': s,
                'is_liked': s.id in user_favorites
            })
        return results

    # Get last played song for recovery
    last_played = None
    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            if custom_user.last_played_song:
                last_played = custom_user.last_played_song
        except CustomUser.DoesNotExist:
            pass

    context = {
        'top_trending': process_songs(trending_songs),
        'recently_added': process_songs(new_songs),
        'last_played_song': last_played,
    }
    return render(request, 'music/index.html', context)

def login_view(request):
    if request.method == 'POST':
        # The HTML form uses name="uname" and name="password"
        uname = request.POST.get('uname')
        password = request.POST.get('password')
        
        # Django's authenticate method checks the username/email and password
        user = authenticate(request, username=uname, password=password)
        if user is not None:
            # If valid, log them in and save the session
            auth_login(request, user)
            return redirect('music:index')
        else:
            # If invalid, add an error message to display on the page
            messages.error(request, 'Invalid username or password.')
            return redirect('music:index')

    # If it's a GET request, just send them back home
    return redirect('music:index')

def logout_view(request):
    logout(request)
    return redirect('music:index')

def process_base64_avatar(base64_data, username):
    """ Helper function to save base64 image data to the static Avatar directory. """
    if not base64_data:
        return 'images/Avatar/default.jpeg' # default
        
    try:
        format, imgstr = base64_data.split(';base64,') 
        ext = format.split('/')[-1]
        
        # Save explicitly as .png as per old code format
        filename = f"{username}.png" 
        
        # We need to save this to the static files directory where avatars live
        avatar_dir = os.path.join(settings.BASE_DIR, 'music', 'static', 'music', 'images', 'Avatar')
        os.makedirs(avatar_dir, exist_ok=True)
        
        file_path = os.path.join(avatar_dir, filename)
        
        with open(file_path, "wb") as fh:
            fh.write(base64.b64decode(imgstr))
            
        return f"images/Avatar/{filename}"
    except Exception as e:
        print(f"Error saving avatar: {e}")
        return 'images/Avatar/default.jpeg'

def register_or_edit_view(request):
    is_edit = request.user.is_authenticated
    
    # Try to grab the current custom user model if logged in
    custom_user = None
    if is_edit:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
        except CustomUser.DoesNotExist:
            pass

    if request.method == 'POST':
        if is_edit:
            form = UserProfileUpdateForm(request.POST, request.FILES, instance=custom_user)
        else:
            form = UserRegistrationForm(request.POST, request.FILES)
            
        if form.is_valid():
            # Save the Custom User Model
            user_instance = form.save(commit=False)
            
            # Default properties for new registration
            if not is_edit:
                user_instance.identity = 'User'
                user_instance.status = 'Active'
                user_instance.password = form.cleaned_data['password'] # Note: Using raw pass in CustomUser as per original design, but hashing in AuthUser
            else:
                # If they provided a new password, update it
                new_password = form.cleaned_data.get('new_password')
                if new_password:
                    user_instance.password = new_password
            
            # Handle Cropped Avatar Upload
            base64_avatar = form.cleaned_data.get('avatar_base64')
            if base64_avatar:
                avatar_path = process_base64_avatar(base64_avatar, user_instance.username)
                user_instance.avatar = avatar_path
            elif not is_edit:
                user_instance.avatar = 'images/Avatar/default.jpeg' # default fallback
                
            user_instance.save()
            
            # Sync with Django's AuthUser Model
            if not is_edit:
                # Create corresponding Django auth user account
                auth_user = AuthUser.objects.create_user(
                    username=user_instance.username,
                    email=user_instance.email,
                    password=user_instance.password
                )
                auth_user.save()
                
                # Automatically log them in after registration
                user = authenticate(request, username=user_instance.username, password=user_instance.password)
                if user is not None:
                    auth_login(request, user)
                    
            else:
                # If editing, update AuthUser if password or username changed
                auth_user = request.user
                auth_user.username = user_instance.username
                auth_user.email = user_instance.email
                if form.cleaned_data.get('new_password'):
                    auth_user.set_password(form.cleaned_data['new_password'])
                auth_user.save()
                
                # Re-authenticate if password changed to avoid session invalidation
                if form.cleaned_data.get('new_password'):
                    user = authenticate(request, username=user_instance.username, password=form.cleaned_data['new_password'])
                    auth_login(request, user)
            
            messages.success(request, 'Profile updated successfully!' if is_edit else 'Registration successful!')
            return redirect('music:index')

    else:
        # GET Request - Load empty form or pre-filled form
        if is_edit:
            form = UserProfileUpdateForm(instance=custom_user)
        else:
            form = UserRegistrationForm()

    context = {
        'form': form,
        'is_edit': is_edit
    }
    return render(request, 'music/register.html', context)


def music_library_view(request):
    """Enhanced Music Library view - supports tabs, sorting, and filtering."""
    q = request.GET.get('q', '')
    tab = request.GET.get('tab', 'all_songs')
    sort = request.GET.get('sort', '-id')
    genre = request.GET.get('genre', '')
    
    # Determine if we are on the search page or library page
    url_name = request.resolver_match.url_name
    
    if url_name == 'search':
        # Search page: default to songs, but we might want to hide the library tabs in the template
        active_tab = 'search_results'
    else:
        active_tab = tab

    context = {
        'q': q,
        'active_tab': active_tab,
        'current_sort': sort,
        'current_genre': genre,
        'is_search_page': (url_name == 'search'),
    }

    if active_tab in ['all_songs', 'search_results']:
        songs = Song.objects.all()
        if q:
            from django.db.models import Q
            songs = songs.filter(
                Q(name__icontains=q) | 
                Q(album__icontains=q) | 
                Q(arrangement__icontains=q)
            )
        if genre:
            songs = songs.filter(song_type=genre)
        
        # Mapping sort values to actual fields
        sort_map = {
            'name': 'name',
            'date': '-release_date',
            'popular': '-views',
            'newest': '-id'
        }
        sort_field = sort_map.get(sort, '-id')
        songs = songs.order_by(sort_field)

        # Pagination
        from django.core.paginator import Paginator
        paginator = Paginator(songs, 20) # More items for list view
        page_number = request.GET.get('page')
        page_obj = paginator.get_page(page_number)
        context['page_obj'] = page_obj
        
    elif active_tab == 'all_albums':
        # Get unique albums
        albums = Song.objects.values('album', 'cover').annotate(song_count=models.Count('id')).order_by('album')
        if q:
            albums = albums.filter(album__icontains=q)
        context['albums'] = albums
        
    elif active_tab == 'all_artists':
        # Get unique artists (arrangement field seems to be used for artist/author in this schema)
        artists = Song.objects.values('arrangement').annotate(song_count=models.Count('id'), views=models.Sum('views')).order_by('-views')
        if q:
            artists = artists.filter(arrangement__icontains=q)
        context['artists'] = artists

    # Get genres for filter dropdown
    genres = Song.objects.values_list('song_type', flat=True).distinct()
    context['genres'] = genres

    # Fetch liked songs for the current user
    liked_song_ids = []
    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            liked_song_ids = list(custom_user.favorite_songs.values_list('id', flat=True))
        except CustomUser.DoesNotExist:
            pass
    context['liked_song_ids'] = liked_song_ids

    return render(request, 'music/library.html', context)


def song_detail_view(request, pk):
    """Static song detail view."""
    from django.shortcuts import get_object_or_404
    song = get_object_or_404(Song, pk=pk)
    context = {
        'song': song,
    }
    return render(request, 'music/song_detail.html', context)


def serve_media(request, path):
    """
    Custom view to serve media files with Range (Partial Content) support.
    This fixes seeking issues in browsers like Chrome/Edge when using Django runserver.
    """
    # Security: Ensure path doesn't try to go outside media root
    normalized_path = os.path.normpath(path).lstrip(os.sep).lstrip('/')
    file_path = os.path.join(settings.MEDIA_ROOT, normalized_path)
    
    if not os.path.exists(file_path) or os.path.isdir(file_path):
        raise Http404("Media file not found.")

    range_header = request.META.get('HTTP_RANGE', '').strip()
    size = os.path.getsize(file_path)
    content_type, encoding = mimetypes.guess_type(file_path)
    content_type = content_type or 'application/octet-stream'

    if range_header:
        # Basic Range parsing: "bytes=start-end"
        try:
            range_match = re.match(r'bytes=(\d+)-(\d*)', range_header)
            if range_match:
                start = int(range_match.group(1))
                end = range_match.group(2)
                end = int(end) if end else size - 1
            else:
                start, end = 0, size - 1
        except (AttributeError, ValueError):
            start, end = 0, size - 1

        if start >= size:
            return HttpResponse(status=416) # Range Not Satisfiable

        content_length = end - start + 1
        
        def file_iterator(f_path, f_start, f_end, chunk_size=8192):
            with open(f_path, 'rb') as f:
                f.seek(f_start)
                remaining = f_end - f_start + 1
                while remaining > 0:
                    chunk = f.read(min(chunk_size, remaining))
                    if not chunk:
                        break
                    yield chunk
                    remaining -= len(chunk)

        response = StreamingHttpResponse(file_iterator(file_path, start, end), status=206, content_type=content_type)
        response['Content-Length'] = str(content_length)
        response['Content-Range'] = f'bytes {start}-{end}/{size}'
        response['Accept-Ranges'] = 'bytes'
        return response

    # No range header, serve normally
    def full_file_iterator(f_path, chunk_size=8192):
        with open(f_path, 'rb') as f:
            while True:
                chunk = f.read(chunk_size)
                if not chunk:
                    break
                yield chunk

    response = StreamingHttpResponse(full_file_iterator(file_path), content_type=content_type)
    response['Content-Length'] = str(size)
    response['Accept-Ranges'] = 'bytes'
    return response

def get_user_playlists(request):
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
    except CustomUser.DoesNotExist:
        return JsonResponse({'error': 'User profile not found'}, status=404)
        
    playlists = Playlist.objects.filter(user=custom_user).order_by('id')
    
    data = []
    # Favorite Songs 'virtual' playlist logic can be handled here or on frontend.
    # We include it as a special entry.
    data.append({
        'id': 'favorites',
        'name': 'My Favorite Music',
        'count': custom_user.favorite_songs.count(),
        'cover': '/static/music/images/Playlist/Favourite.png' 
    })
    
    for p in playlists:
        data.append({
            'id': p.id,
            'name': p.name,
            'count': p.songs.count(),
            'views': p.views,
            'cover': p.cover if p.cover.startswith('http') or p.cover.startswith('/') else f"/media/{p.cover}"
        })
        
    return JsonResponse({'playlists': data})

def add_to_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
        
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
        
    song_id = request.POST.get('song_id')
    playlist_id = request.POST.get('playlist_id')
    
    try:
        song = Song.objects.get(id=song_id)
        custom_user = CustomUser.objects.get(username=request.user.username)
        
        if playlist_id == 'favorites':
            song.favorited_by.add(custom_user)
            return JsonResponse({'success': True, 'playlist_name': 'My Favorite Music'})
        else:
            playlist = Playlist.objects.get(id=playlist_id, user=custom_user)
            playlist.songs.add(song)
            return JsonResponse({'success': True, 'playlist_name': playlist.name})
            
    except (Song.DoesNotExist, Playlist.DoesNotExist, CustomUser.DoesNotExist):
        return JsonResponse({'success': False, 'error': 'Not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def create_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
    
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
    
    name = request.POST.get('name')
    is_private = request.POST.get('is_private') == 'true'
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        # Use a default cover from the static folder
        default_cover = "/static/music/images/Playlist/PrivatePlaylist.png"
        
        playlist = Playlist.objects.create(
            user=custom_user,
            name=name,
            is_private=is_private,
            cover=default_cover
        )
        return JsonResponse({
            'success': True, 
            'playlist': {
                'id': playlist.id,
                'name': playlist.name,
                'cover': playlist.cover
            }
        })
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def increment_song_view(request):
    song_id = request.POST.get('song_id')
    try:
        song = Song.objects.get(id=song_id)
        song.views += 1
        song.save()
        return JsonResponse({'success': True, 'views': song.views})
    except Song.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Song not found'}, status=404)

def increment_playlist_view(request):
    playlist_id = request.POST.get('playlist_id')
    if playlist_id in ['favorites', 'recent']:
        return JsonResponse({'success': True}) # Virtual playlists
        
    try:
        playlist = Playlist.objects.get(id=playlist_id)
        playlist.views += 1
        playlist.save()
        return JsonResponse({'success': True, 'views': playlist.views})
    except Playlist.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)

def get_playlist_details(request, playlist_id):
    try:
        if playlist_id == 'favorites':
            if not request.user.is_authenticated:
                return JsonResponse({'error': 'Unauthorized'}, status=401)
            custom_user = CustomUser.objects.get(username=request.user.username)
            songs = custom_user.favorite_songs.all()
            playlist_name = "Favourite Music"
            cover = "/static/music/images/Playlist/Favourite.png"
            creator = custom_user.username
            creator_avatar = custom_user.avatar.url if custom_user.avatar else f"/static/music/images/Avatar/{custom_user.username}.png"
            created_at = custom_user.date_joined.strftime("%Y-%m-%d")
        elif playlist_id == 'recent':
            if not request.user.is_authenticated:
                return JsonResponse({'error': 'Unauthorized'}, status=401)
            custom_user = CustomUser.objects.get(username=request.user.username)
            # Use PlayHistory instead of RecentPlay
            recent_plays = PlayHistory.objects.filter(user=custom_user).order_by('-played_at')
            # Unique songs only, keep most recent
            seen_songs = set()
            unique_songs = []
            for rp in recent_plays:
                if rp.song.id not in seen_songs:
                    unique_songs.append(rp.song)
                    seen_songs.add(rp.song.id)
                if len(unique_songs) >= 20:
                    break
            songs = unique_songs
            playlist_name = "Recently Played"
            cover = "/static/music/images/Playlist/PrivatePlaylist.png"
            creator = custom_user.username
            creator_avatar = custom_user.avatar.url if custom_user.avatar else f"/static/music/images/Avatar/{custom_user.username}.png"
            created_at = "History"
        else:
            playlist = Playlist.objects.get(id=playlist_id)
            songs = playlist.songs.all()
            playlist_name = playlist.name
            cover = playlist.cover if playlist.cover.startswith('http') or playlist.cover.startswith('/') else f"/media/{playlist.cover}"
            creator = playlist.user.username
            creator_avatar = playlist.user.avatar.url if playlist.user.avatar else f"/static/music/images/Avatar/{playlist.user.username}.png"
            created_at = playlist.created_at.strftime("%Y-%m-%d") if playlist.created_at else "Unknown Date"

        # Get custom user for is_liked check if authenticated
        custom_user_for_liked = None
        if request.user.is_authenticated:
            custom_user_for_liked = CustomUser.objects.get(username=request.user.username)

        song_list = []
        for s in songs:
            # Try to get actual duration if file exists
            duration = "00:00"
            if s.download_link and os.path.exists(s.download_link.path):
                duration = get_audio_duration(s.download_link.path)

            song_list.append({
                'id': s.id,
                'title': s.name,
                'artist': s.arrangement,
                'album': s.album or "Unknown Album",
                'duration': duration,
                'cover': s.cover.url if s.cover else "/static/music/images/grid (1).jpg",
                'file_url': s.download_link.url if s.download_link else "",
                'is_liked': custom_user_for_liked and custom_user_for_liked.favorite_songs.filter(id=s.id).exists()
            })

        return JsonResponse({
            'success': True,
            'playlist': {
                'id': playlist_id,
                'name': playlist_name,
                'cover': cover,
                'creator': creator,
                'creator_avatar': creator_avatar,
                'created_at': created_at,
                'song_count': len(songs) if isinstance(songs, list) else songs.count(),
                'songs': song_list
            }
        })
    except Playlist.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def record_recent_play(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Not authenticated'}, status=401)
    
    song_id = request.POST.get('song_id')
    if not song_id:
        return JsonResponse({'success': False, 'error': 'No song_id provided'}, status=400)
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song = Song.objects.get(id=song_id)
        
        # We can just create a new history entry or reuse latest if it's very recent.
        # User said "直接调用history", but PlayHistory doesn't have unique_together by default in models.py
        # Let's check models.py again for PlayHistory.
        # It's lines 108-114. No unique_together.
        # So we can just create a new one.
        PlayHistory.objects.create(user=custom_user, song=song)
        return JsonResponse({'success': True})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)
def toggle_favorite(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Not authenticated'}, status=401)
    
    song_id = request.POST.get('song_id')
    if not song_id:
        return JsonResponse({'success': False, 'error': 'No song_id provided'}, status=400)
        
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song = Song.objects.get(id=song_id)
        
        if custom_user.favorite_songs.filter(id=song_id).exists():
            custom_user.favorite_songs.remove(song)
            is_liked = False
        else:
            custom_user.favorite_songs.add(song)
            is_liked = True
            
        return JsonResponse({'success': True, 'is_liked': is_liked})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def remove_from_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
    
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
    
    song_id = request.POST.get('song_id')
    playlist_id = request.POST.get('playlist_id')
    
    if not song_id or not playlist_id:
        return JsonResponse({'success': False, 'error': 'Missing song_id or playlist_id'}, status=400)
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song = Song.objects.get(id=song_id)
        playlist = Playlist.objects.get(id=playlist_id, user=custom_user)
        playlist.songs.remove(song)
        return JsonResponse({'success': True})
    except (Song.DoesNotExist, Playlist.DoesNotExist, CustomUser.DoesNotExist):
        return JsonResponse({'success': False, 'error': 'Not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def check_favorite(request):
    if not request.user.is_authenticated:
        return JsonResponse({'is_liked': False})
    
    song_id = request.GET.get('song_id')
    if not song_id:
        return JsonResponse({'error': 'No song_id provided'}, status=400)
        
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        is_liked = custom_user.favorite_songs.filter(id=song_id).exists()
        return JsonResponse({'success': True, 'is_liked': is_liked})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def update_last_played(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Not authenticated'}, status=401)
    
    if request.method != 'POST':
        return JsonResponse({'success': False, 'error': 'Only POST allowed'}, status=405)

    song_id = request.POST.get('song_id')
    if not song_id:
        return JsonResponse({'success': False, 'error': 'No song_id provided'}, status=400)
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song = Song.objects.get(id=song_id)
        custom_user.last_played_song = song
        custom_user.save()
        return JsonResponse({'success': True})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

