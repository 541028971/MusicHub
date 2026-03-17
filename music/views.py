import os
import re
import random
import base64
import mimetypes
import zipfile
from io import BytesIO
from django.http import FileResponse
from django.conf import settings
from django.core.files.base import ContentFile
from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login as auth_login, logout
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User as AuthUser
from django.contrib import messages
from django.http import StreamingHttpResponse, Http404, HttpResponse, JsonResponse
from django.utils import timezone
from django.db import models
from .models import Song, User as CustomUser, Playlist, PlayHistory, get_audio_duration, Comment
from .forms import UserRegistrationForm, UserProfileUpdateForm

def index(request, playlist_id=None):
    from datetime import timedelta

    user_favorites = set()
    custom_user = None
    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            user_favorites = set(custom_user.favorite_songs.values_list('id', flat=True))
        except CustomUser.DoesNotExist:
            pass

    def process_songs(songs):
        results = []
        for s in songs:
            results.append({
                'obj': s,
                'is_liked': s.id in user_favorites,
                'comment_count': getattr(s, 'comment_count', 0),
            })
        return results

    # 1. Recommend for You — cached in session, only re-randomize on ?refresh_recommend=1
    refresh = request.GET.get('refresh_recommend') == '1'
    cached_ids = request.session.get('recommend_ids')
    if refresh or not cached_ids:
        all_meta = list(Song.objects.values_list('id', 'album'))
        random.shuffle(all_meta)
        album_count = {}
        selected_ids = []
        for song_id, album in all_meta:
            key = album or '__none__'
            if album_count.get(key, 0) < 2:
                selected_ids.append(song_id)
                album_count[key] = album_count.get(key, 0) + 1
            if len(selected_ids) >= 10:
                break
        request.session['recommend_ids'] = selected_ids
        # PRG: redirect to clean URL so F5 doesn't re-randomize
        if refresh:
            return redirect('music:index')
    else:
        selected_ids = cached_ids
    recommend_qs = Song.objects.filter(id__in=selected_ids).annotate(comment_count=models.Count('comments'))

    # 2. Top Views — 9 songs for 3×3 ranked grid
    top_views_qs = Song.objects.annotate(comment_count=models.Count('comments')).order_by('-views')[:9]
    top_views_list = process_songs(list(top_views_qs))
    for i, item in enumerate(top_views_list):
        item['rank'] = i + 1

    # 3. Recently Uploaded — newest first, max 3 per album for variety
    all_recent = list(Song.objects.values_list('id', 'album').order_by('-id'))
    recent_album_count = {}
    recent_ids = []
    for song_id, album in all_recent:
        key = album or '__none__'
        if recent_album_count.get(key, 0) < 3:
            recent_ids.append(song_id)
            recent_album_count[key] = recent_album_count.get(key, 0) + 1
        if len(recent_ids) >= 10:
            break
    recently_qs = Song.objects.filter(id__in=recent_ids).annotate(comment_count=models.Count('comments'))
    recently_id_order = {id_: i for i, id_ in enumerate(recent_ids)}
    recently_sorted = sorted(recently_qs, key=lambda s: recently_id_order.get(s.id, 999))

    # 4. Listen Again — songs last played 7+ days ago (only when ≥5 history entries)
    listen_again = []
    if custom_user:
        total_history = PlayHistory.objects.filter(user=custom_user).count()
        if total_history >= 5:
            seven_days_ago = timezone.now() - timedelta(days=7)
            history_rows = (
                PlayHistory.objects
                .filter(user=custom_user)
                .values('song')
                .annotate(last_played=models.Max('played_at'))
                .filter(last_played__lt=seven_days_ago)
                .order_by('last_played')[:10]
            )
            la_ids = [h['song'] for h in history_rows]
            if la_ids:
                la_qs = Song.objects.filter(id__in=la_ids).annotate(comment_count=models.Count('comments'))
                id_order = {id_: i for i, id_ in enumerate(la_ids)}
                la_sorted = sorted(la_qs, key=lambda s: id_order.get(s.id, 999))
                listen_again = process_songs(la_sorted)

    # 5. Browse by Type
    type_qs = (
        Song.objects
        .exclude(song_type__isnull=True).exclude(song_type='')
        .values('song_type')
        .annotate(cnt=models.Count('id'))
        .order_by('-cnt')
    )
    type_songs = {}
    for t in type_qs:
        stype = t['song_type']
        tqs = Song.objects.filter(song_type=stype).annotate(
            comment_count=models.Count('comments')).order_by('-views')[:8]
        type_songs[stype] = process_songs(list(tqs))

    id_order = {id_: i for i, id_ in enumerate(selected_ids)}
    recommend_sorted = sorted(recommend_qs, key=lambda s: id_order.get(s.id, 999))
    recommend_list = process_songs(recommend_sorted)

    context = {
        'recommend_songs': recommend_list,
        'top_views_songs': top_views_list,
        'recently_songs': process_songs(recently_sorted),
        'listen_again_songs': listen_again,
        'type_songs': type_songs,
    }
    return render(request, 'music/index.html', context)

def recommend_fragment_api(request):
    """AJAX: re-randomize session and return HTML fragment for the recommend shelf row."""
    user_favorites = set()
    if request.user.is_authenticated:
        try:
            cu = CustomUser.objects.get(username=request.user.username)
            user_favorites = set(cu.favorite_songs.values_list('id', flat=True))
        except CustomUser.DoesNotExist:
            pass

    all_meta = list(Song.objects.values_list('id', 'album'))
    random.shuffle(all_meta)
    album_count = {}
    selected_ids = []
    for song_id, album in all_meta:
        key = album or '__none__'
        if album_count.get(key, 0) < 2:
            selected_ids.append(song_id)
            album_count[key] = album_count.get(key, 0) + 1
        if len(selected_ids) >= 10:
            break
    request.session['recommend_ids'] = selected_ids

    recommend_qs = Song.objects.filter(id__in=selected_ids).annotate(comment_count=models.Count('comments'))
    id_order = {id_: i for i, id_ in enumerate(selected_ids)}
    recommend_sorted = sorted(recommend_qs, key=lambda s: id_order.get(s.id, 999))
    recommend_list = [{'obj': s, 'is_liked': s.id in user_favorites,
                       'comment_count': getattr(s, 'comment_count', 0)} for s in recommend_sorted]

    return render(request, 'music/includes/recommend_fragment.html', {
        'recommend_songs': recommend_list,
    })

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
            if request.headers.get('x-requested-with') == 'XMLHttpRequest':
                return JsonResponse({'success': True, 'redirect_url': '/'})
            return redirect('music:index')
        else:
            # If invalid, return JSON error for AJAX requests
            if request.headers.get('x-requested-with') == 'XMLHttpRequest':
                return JsonResponse({'success': False, 'error': 'Invalid username or password.'})
                
            # Fallback for standard form submissions
            messages.error(request, 'Invalid username or password.')
            return redirect('music:index')

    # If it's a GET request, just send them back home
    return redirect('music:index')

def logout_view(request):
    logout(request)
    return redirect('music:index')

@login_required
def profile_settings(request):
    """View to handle user profile updates (personal settings)."""
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
    except CustomUser.DoesNotExist:
        return redirect('music:index')

    if request.method == 'POST':
        form = UserProfileUpdateForm(request.POST, request.FILES, instance=custom_user)
        if form.is_valid():
            user_instance = form.save(commit=False)
            
            # Handle Password Change
            new_password = form.cleaned_data.get('new_password')
            if new_password:
                user_instance.password = new_password
                auth_user = request.user
                auth_user.set_password(new_password)
                auth_user.save()
                auth_login(request, auth_user)

            # Handle Avatar
            base64_avatar = form.cleaned_data.get('avatar_base64')
            if base64_avatar:
                avatar_path = process_base64_avatar(base64_avatar, user_instance.username)
                user_instance.avatar = avatar_path
            
            # Sync Auth User
            auth_user = request.user
            auth_user.username = user_instance.username
            auth_user.email = user_instance.email
            auth_user.save()
                
            user_instance.save()
            messages.success(request, 'Profile updated successfully!')
            return redirect('music:index')
    else:
        form = UserProfileUpdateForm(instance=custom_user)

    context = {
        'form': form,
        'user_profile': custom_user
    }
    return render(request, 'music/settings.html', context)

def process_base64_avatar(base64_data, username):
    """ Helper function to save base64 image data to the media avatars directory. """
    if not base64_data:
        return 'avatars/default.jpeg' # media relative
        
    try:
        format, imgstr = base64_data.split(';base64,') 
        ext = format.split('/')[-1]
        
        filename = f"{username}.png" 
        
        # Save to MEDIA_ROOT/avatars
        avatar_dir = os.path.join(settings.MEDIA_ROOT, 'avatars')
        os.makedirs(avatar_dir, exist_ok=True)
        
        file_path = os.path.join(avatar_dir, filename)
        
        with open(file_path, "wb") as fh:
            fh.write(base64.b64decode(imgstr))
            
        return f"avatars/{filename}"
    except Exception as e:
        print(f"Error saving avatar: {e}")
        return 'avatars/default.jpeg'

def register_view(request):
    if request.user.is_authenticated:
        return redirect('music:index')

    if request.method == 'POST':
        form = UserRegistrationForm(request.POST, request.FILES)
        if form.is_valid():
            # Save the Custom User Model
            user_instance = form.save(commit=False)
            user_instance.status = 'Active'
            user_instance.password = form.cleaned_data['password']
            
            # Handle Cropped Avatar Upload
            base64_avatar = form.cleaned_data.get('avatar_base64')
            if base64_avatar:
                avatar_path = process_base64_avatar(base64_avatar, user_instance.username)
                user_instance.avatar = avatar_path
            else:
                user_instance.avatar = 'avatars/default.jpeg'
                
            user_instance.save()
            
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
            
            messages.success(request, 'Registration successful!')
            return redirect('music:index')
    else:
        form = UserRegistrationForm()

    context = {
        'form': form,
        'is_edit': False
    }
    return render(request, 'music/register.html', context)


def music_library_view(request):
    """Enhanced Music Library view - supports tabs, sorting, and filtering."""
    q = request.GET.get('q', '')
    tab = request.GET.get('tab', 'all_songs')
    sort = request.GET.get('sort', 'name')
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
            # Define placeholders that should NOT count as a match reason
            placeholders = ['Unknown Artist', 'Unknown Album', '']
            
            # Logic: Match if (Name matches Q) 
            # OR (Album matches Q AND Album is NOT a placeholder)
            # OR (Artist matches Q AND Artist is NOT a placeholder)
            songs = songs.filter(
                Q(name__icontains=q) | 
                (Q(album__icontains=q) & ~Q(album__in=placeholders)) | 
                (Q(arrangement__icontains=q) & ~Q(arrangement__in=placeholders))
            )
        if genre:
            from django.db.models import Q
            # Match if it's the only genre, OR if it's in a comma-separated list
            # We use icontains which handles most cases, but we can refine if needed.
            songs = songs.filter(song_type__icontains=genre)
        
        # Mapping sort values to actual fields
        sort_map = {
            'name': 'name',
            'date': '-release_date',
            'popular': '-views',
            '-id': '-id'
        }
        sort_field = sort_map.get(sort, 'name')
        songs = songs.order_by(sort_field)

        # Pagination
        from django.core.paginator import Paginator
        paginator = Paginator(songs, 20) # Standard list view count
        page_number = request.GET.get('page')
        page_obj = paginator.get_page(page_number)
        context['page_obj'] = page_obj
        
    elif active_tab == 'all_albums':
        # Group by album name and pick a representative cover, also get the common artist
        albums = Song.objects.values('album').annotate(
            song_count=models.Count('id'),
            cover=models.Max('cover'),
            artist=models.Max('arrangement')
        ).exclude(
            models.Q(album__isnull=True) | 
            models.Q(album='') | 
            models.Q(album__iexact='Unknown Album') |
            models.Q(album__iexact='Unknown Artist/Album')
        ).order_by('album')
        if q:
            albums = albums.filter(album__icontains=q)
        context['albums'] = albums
        
    elif active_tab == 'all_artists':
        from django.db.models import Q, Sum, Count
        # Get all songs that have an arrangement
        raw_songs = Song.objects.exclude(
            Q(arrangement__isnull=True) | 
            Q(arrangement='') | 
            Q(arrangement__iexact='Unknown Artist')
        ).values('arrangement', 'views')
        
        artist_map = {}
        for s in raw_songs:
            # Split by pipe
            names = [n.strip() for n in str(s['arrangement']).split('|') if n.strip()]
            for name in names:
                # Apply filter if searching
                if q and q.lower() not in name.lower():
                    continue
                if name not in artist_map:
                    artist_map[name] = {
                        'arrangement': name,
                        'song_count': 0,
                        'views': 0
                    }
                artist_map[name]['song_count'] += 1
                artist_map[name]['views'] += (s['views'] or 0)
        
        # Sort by views descending
        artists = sorted(artist_map.values(), key=lambda x: x['views'], reverse=True)
        context['artists'] = artists

    # Get genres for filter dropdown - Enhanced to support comma-separated tags
    genres_raw = Song.objects.values_list('song_type', flat=True).distinct()
    genres_set = set()
    for g_str in genres_raw:
        if g_str:
            # Split by either English comma or Chinese comma, then clean up whitespace
            parts = [p.strip() for p in g_str.replace('，', ',').split(',')]
            for p in parts:
                if p:
                    genres_set.add(p)
    genres = sorted(list(genres_set))
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
    
def discovery_view(request):
    """Playlist Square view - shows all non-private playlists, excluding the user's own."""
    public_playlists = Playlist.objects.filter(is_private=False)
    
    if request.user.is_authenticated:
        # Exclude playlists owned by the current user
        public_playlists = public_playlists.exclude(user__username=request.user.username)
        
    public_playlists = public_playlists.order_by('-views')
    
    # Process playlists to include song count and favorited status
    favorited_playlist_ids = set()
    if request.user.is_authenticated:
        try:
            custom_user = Playlist.objects.get(id=public_playlists[0].id).user.__class__.objects.get(username=request.user.username)
            favorited_playlist_ids = set(custom_user.favorited_playlists.values_list('id', flat=True))
        except:
            pass

    playlists_data = []
    for p in public_playlists:
        playlists_data.append({
            'obj': p,
            'song_count': p.songs.count(),
            'is_favorited': p.id in favorited_playlist_ids
        })
        
    context = {
        'public_playlists': playlists_data,
    }
    return render(request, 'music/discovery.html', context)




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
    data = []
    
    # 1. Add Favorites as a virtual private playlist (always present)
    data.append({
        'id': 'favorites',
        'name': 'My Favorite Music',
        'count': 0, # Placeholder if guest
        'cover': '/media/playlists/Favourite.png',
        'is_private': False
    })

    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            # Update Favorites count for real user
            data[0]['count'] = custom_user.favorite_songs.count()
            playlists = Playlist.objects.filter(user=custom_user).order_by('id')
        except CustomUser.DoesNotExist:
            playlists = []
            
        for p in playlists:
            data.append({
                'id': p.id,
                'name': p.name,
                'count': p.songs.count(),
                'views': p.views,
                'cover': p.cover.url if p.cover else '/media/playlists/default_playlist.png',
                'is_private': p.is_private
            })
            
    return JsonResponse({'playlists': data})

def add_to_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
        
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
        
    song_id_str = request.POST.get('song_id')
    playlist_id = request.POST.get('playlist_id')
    
    if not song_id_str:
        return JsonResponse({'success': False, 'error': 'No song specified'}, status=400)

    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song_ids = [sid.strip() for sid in song_id_str.split(',') if sid.strip()]
        
        if playlist_id == 'favorites':
            playlist_name = 'My Favorite Music'
            playlist = None
        else:
            playlist = Playlist.objects.get(id=playlist_id, user=custom_user)
            playlist_name = playlist.name

        added_some = False
        already_exists_all = True
        
        for sid in song_ids:
            try:
                song = Song.objects.get(id=sid)
                if playlist_id == 'favorites':
                    if not song.favorited_by.filter(id=custom_user.id).exists():
                        song.favorited_by.add(custom_user)
                        added_some = True
                        already_exists_all = False
                    else:
                        pass # already in favorites
                else:
                    if not playlist.songs.filter(id=song.id).exists():
                        playlist.songs.add(song)
                        added_some = True
                        already_exists_all = False
                    else:
                        pass # already in playlist
            except Song.DoesNotExist:
                continue

        return JsonResponse({
            'success': True, 
            'playlist_name': playlist_name,
            'added_some': added_some,
            'already_exists': already_exists_all,
            'multi': len(song_ids) > 1
        })
            
    except (Playlist.DoesNotExist, CustomUser.DoesNotExist):
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)
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
        # Delegate default cover path to the Playlist model defaults seamlessly
        
        playlist = Playlist.objects.create(
            user=custom_user,
            name=name,
            is_private=is_private
        )
        return JsonResponse({
            'success': True, 
            'playlist': {
                'id': playlist.id,
                'name': playlist.name,
                'cover': playlist.cover.url if playlist.cover else '/media/playlists/default_playlist.png',
                'is_private': playlist.is_private
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

from django.shortcuts import render
from django.http import HttpResponse, HttpResponseForbidden

def playlist_detail(request, playlist_id):
    """
    Renders the playlist detail page with full HTML structure (inherits base).
    When fetched via SPA/AJAX, the SPA logic will extract just the content block.
    """
    try:
        custom_user = None
        if request.user.is_authenticated:
            custom_user = CustomUser.objects.get(username=request.user.username)

        if playlist_id == 'favorites':
            if custom_user:
                songs = custom_user.favorite_songs.all()
                creator = custom_user.username
                creator_avatar = custom_user.avatar.url if custom_user.avatar else "/media/avatars/default.jpeg"
            else:
                songs = []
                creator = "Guest"
                creator_avatar = "/media/avatars/default.jpeg"
            playlist_name = "Favourite Music"
            cover = "/media/playlists/Favourite.png"
            created_at = "Personal Collection"
            introduction = "Your private library"
            is_private = True
        elif playlist_id == 'recent':
            if custom_user:
                recent_plays = PlayHistory.objects.filter(user=custom_user).select_related('song').order_by('-played_at')[:100]
                unique_songs = []
                seen_songs = set()
                for rp in recent_plays:
                    if rp.song.id not in seen_songs:
                        unique_songs.append(rp.song)
                        seen_songs.add(rp.song.id)
                    if len(unique_songs) >= 20:
                        break
                songs = unique_songs
                creator = custom_user.username
                creator_avatar = custom_user.avatar.url if custom_user.avatar else "/media/avatars/default.jpeg"
            else:
                songs = []
                creator = "Guest"
                creator_avatar = "/media/avatars/default.jpeg"
            playlist_name = "Recently Played"
            cover = "/media/playlists/default_playlist.png"
            created_at = "History"
            introduction = "Your playback history"
            is_private = True
        else:
            playlist = Playlist.objects.get(id=playlist_id)
            songs = playlist.songs.all()
            playlist_name = playlist.name
            cover = playlist.cover.url if playlist.cover else "/media/playlists/default_playlist.png"
            creator = playlist.user.username
            creator_avatar = playlist.user.avatar.url if playlist.user.avatar else "/media/avatars/default.jpeg"
            created_at = playlist.created_at.strftime("%Y-%m-%d") if playlist.created_at else "Unknown Date"
            introduction = playlist.introduction
            is_private = playlist.is_private

        # Pre-fetch liked song IDs for efficient checking
        liked_song_ids = set()
        if custom_user:
            liked_song_ids = set(custom_user.favorite_songs.values_list('id', flat=True))

        # Prepare song data
        song_list = []
        for s in songs:
            song_list.append({
                'id': s.id,
                'title': s.name,
                'artist': s.arrangement,
                'album': s.album or "Unknown Album",
                'cover': s.cover.url,
                'file_url': s.download_link.url if s.download_link else "",
                'is_liked': s.id in liked_song_ids
            })

        context = {
            'is_edit_mode': (request.resolver_match.url_name == 'playlist_edit_frontend'),
            'playlist': {
                'id': playlist_id,
                'name': playlist_name,
                'cover': cover if cover else "/media/playlists/default_playlist.png",
                'creator': creator,
                'creator_avatar': creator_avatar,
                'created_at': created_at,
                'song_count': len(songs) if isinstance(songs, list) else songs.count(),
                'songs': song_list,
                'song_ids': [s['id'] for s in song_list],
                'introduction': introduction if playlist_id not in ['favorites', 'recent'] else "",
                'is_private': is_private if playlist_id not in ['favorites', 'recent'] else False,
                'favorited_count': playlist.favorited_by.count() if playlist_id not in ['favorites', 'recent'] else 0
            }
        }
        return render(request, 'music/playlist_detail.html', context)
    except Exception as e:
        return HttpResponse(f"Error: {str(e)}", status=500)

def album_detail(request, album_name):
    """
    Renders the album detail page.
    """
    try:
        custom_user = None
        if request.user.is_authenticated:
            try:
                custom_user = CustomUser.objects.get(username=request.user.username)
            except CustomUser.DoesNotExist:
                pass

        # In Lalaland/Aurora, albums are just strings in the Song model.
        songs = Song.objects.filter(album=album_name)
        if not songs.exists():
            raise Http404("Album not found")

        # Pick a representative cover (from the first song found)
        representative_song = songs.first()
        cover = representative_song.cover.url if representative_song else "/media/covers/default_cover.jpg"
        artist = representative_song.arrangement

        # Pre-fetch liked song IDs
        liked_song_ids = set()
        if custom_user:
            liked_song_ids = set(custom_user.favorite_songs.values_list('id', flat=True))

        # Prepare song data
        song_list = []
        for s in songs:
            song_list.append({
                'id': s.id,
                'title': s.name,
                'artist': s.arrangement,
                'album': s.album,
                'cover': s.cover.url,
                'file_url': s.download_link.url if s.download_link else "",
                'is_liked': s.id in liked_song_ids
            })

        context = {
            'album': {
                'name': album_name,
                'cover': cover,
                'artist': artist,
                'song_count': songs.count(),
                'songs': song_list,
                'song_ids': [s['id'] for s in song_list]
            }
        }
        return render(request, 'music/album_detail.html', context)
    except Exception as e:
        return HttpResponse(f"Error: {str(e)}", status=500)

def artist_detail(request, artist_name):
    """
    Renders the artist detail page.
    """
    try:
        custom_user = None
        if request.user.is_authenticated:
            try:
                custom_user = CustomUser.objects.get(username=request.user.username)
            except CustomUser.DoesNotExist:
                pass

        active_tab = request.GET.get('tab', 'songs')
        
        # Get all songs by this artist (support pipe split)
        from django.db.models import Q
        songs = Song.objects.filter(
            Q(arrangement=artist_name) | 
            Q(arrangement__icontains=f"|{artist_name}") | 
            Q(arrangement__icontains=f"{artist_name}|")
        ).order_by('-id')
        if not songs.exists():
            # Support case-insensitive/partial match if needed, but for now stick to exact
            pass
            
        # Get all unique albums by this artist
        all_albums = songs.values('album').order_by().annotate(
            song_count=models.Count('id')
        ).exclude(album__isnull=True).exclude(album='').exclude(album='Unknown Album')
        
        album_list = []
        for alb in all_albums:
            # Pick a cover for the album
            rep_song = songs.filter(album=alb['album']).first()
            album_list.append({
                'name': alb['album'],
                'song_count': alb['song_count'],
                'cover': rep_song.cover.url if rep_song.cover else "/media/covers/default_cover.jpg"
            })

        # Pre-fetch liked song IDs
        liked_song_ids = set()
        if custom_user:
            liked_song_ids = set(custom_user.favorite_songs.values_list('id', flat=True))

        # Prepare song data
        song_list = []
        for s in songs:
            song_list.append({
                'id': s.id,
                'title': s.name,
                'artist': s.arrangement,
                'album': s.album,
                'cover': s.cover.url if s.cover else "/media/covers/default_cover.jpg",
                'file_url': s.download_link.url if s.download_link else "",
                'is_liked': s.id in liked_song_ids
            })

        context = {
            'artist': {
                'name': artist_name,
                'song_count': songs.count(),
                'album_count': len(album_list),
                'songs': song_list,
                'albums': album_list,
                'song_ids': [s['id'] for s in song_list]
            },
            'active_tab': active_tab
        }
        return render(request, 'music/artist_detail.html', context)
    except Exception as e:
        return HttpResponse(f"Error: {str(e)}", status=500)


def get_playlist_details(request, playlist_id):
    try:
        if playlist_id == 'favorites':
            if not request.user.is_authenticated:
                return JsonResponse({'error': 'Unauthorized'}, status=401)
            custom_user = CustomUser.objects.get(username=request.user.username)
            songs = custom_user.favorite_songs.all()
            playlist_name = "Favourite Music"
            cover = "/media/playlists/Favourite.png"
            creator = custom_user.username
            creator_avatar = custom_user.avatar.url if custom_user.avatar else "/media/avatars/default.jpeg"
            created_at = custom_user.date_joined.strftime("%Y-%m-%d")
            is_private = False
            introduction = "Your private library"
        elif playlist_id == 'recent':
            if not request.user.is_authenticated:
                return JsonResponse({'error': 'Unauthorized'}, status=401)
            custom_user = CustomUser.objects.get(username=request.user.username)
            # Use PlayHistory instead of RecentPlay
            recent_plays = PlayHistory.objects.filter(user=custom_user).select_related('song').order_by('-played_at')[:100]
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
            cover = "/media/playlists/default_playlist.png"
            creator = custom_user.username
            creator_avatar = custom_user.avatar.url if custom_user.avatar else "/media/avatars/default.jpeg"
            created_at = "History"
            is_private = True
            introduction = "Your playback history"
        else:
            playlist = Playlist.objects.get(id=playlist_id)
            songs = playlist.songs.all()
            playlist_name = playlist.name
            cover = playlist.cover.url if playlist.cover else "/media/playlists/default_playlist.png"
            creator = playlist.user.username
            creator_avatar = playlist.user.avatar.url if playlist.user.avatar else "/media/avatars/default.jpeg"
            created_at = playlist.created_at.strftime("%Y-%m-%d") if playlist.created_at else "Unknown Date"
            is_private = playlist.is_private
            introduction = playlist.introduction

        # Get custom user for is_liked check if authenticated
        custom_user_for_liked = None
        if request.user.is_authenticated:
            custom_user_for_liked = CustomUser.objects.get(username=request.user.username)

        # Pre-fetch liked song IDs for efficient checking
        liked_song_ids = set()
        if custom_user_for_liked:
            liked_song_ids = set(custom_user_for_liked.favorite_songs.values_list('id', flat=True))

        song_list = []
        for s in songs:
            song_list.append({
                'id': s.id,
                'title': s.name,
                'artist': s.arrangement,
                'album': s.album or "Unknown Album",
                'cover': s.cover.url,
                'file_url': s.download_link.url if s.download_link else "",
                'is_liked': s.id in liked_song_ids
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
                'songs': song_list,
                'introduction': introduction if playlist_id not in ['favorites', 'recent'] else "",
                'is_private': is_private
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
            
        return JsonResponse({'success': True, 'is_liked': is_liked, 'likes_count': song.favorited_by.count()})
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



def get_song_details(request, song_id):
    """Fetch basic song details like lyrics for the frontend player."""
    try:
        song = Song.objects.get(id=song_id)
        return JsonResponse({
            'success': True,
            'lyrics': song.lyrics.url if song.lyrics else '1145141919810',
            'album': song.album,
            'song_type': song.song_type,
            'release_date': song.release_date.strftime('%Y-%m-%d') if song.release_date else '-',
            'introduction': song.introduction,
            'views': song.views,
            'likes_count': song.favorited_by.count(),
            'comments_count': song.comments.count(),
        })
    except Song.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Song not found'})

def update_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
    
    playlist_id = request.POST.get('playlist_id')
    name = request.POST.get('name')
    introduction = request.POST.get('introduction')
    is_private_raw = request.POST.get('is_private')
    cover_file = request.FILES.get('cover')
    
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        playlist = Playlist.objects.get(id=playlist_id, user=custom_user)
        
        if name: playlist.name = name
        if introduction is not None: playlist.introduction = introduction
        if is_private_raw is not None:
            new_is_private = (is_private_raw == 'true')
            # If changing from public to private, clear all favorites
            if new_is_private and not playlist.is_private:
                playlist.favorited_by.clear()
            playlist.is_private = new_is_private
        if cover_file:
            playlist.cover = cover_file
        
        playlist.save()
        
        return JsonResponse({
            'success': True, 
            'name': playlist.name, 
            'introduction': playlist.introduction,
            'cover_url': playlist.cover.url if hasattr(playlist.cover, 'url') else str(playlist.cover)
        })
    except Playlist.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def delete_playlist(request):
    if request.method != 'POST':
        return JsonResponse({'error': 'Only POST allowed'}, status=405)
    if not request.user.is_authenticated:
        return JsonResponse({'error': 'Unauthorized'}, status=401)
    
    playlist_id = request.POST.get('playlist_id')
    try:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
        except CustomUser.DoesNotExist:
             return JsonResponse({'success': False, 'error': 'User profile not found'}, status=404)
             
        playlist = Playlist.objects.get(id=playlist_id, user=custom_user)
        playlist.delete()
        return JsonResponse({'success': True})
    except Playlist.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def toggle_playlist_favorite(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Auth required'}, status=401)
    
    playlist_id = request.POST.get('playlist_id')
    if not playlist_id:
        return JsonResponse({'success': False, 'error': 'Missing playlist_id'}, status=400)
        
    try:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
        except CustomUser.DoesNotExist:
            return JsonResponse({'success': False, 'error': 'User profile not found'}, status=404)
            
        playlist = Playlist.objects.get(id=playlist_id)
        
        if playlist.favorited_by.filter(id=custom_user.id).exists():
            playlist.favorited_by.remove(custom_user)
            is_favorited = False
        else:
            playlist.favorited_by.add(custom_user)
            is_favorited = True
            
        return JsonResponse({'success': True, 'is_favorited': is_favorited})
    except Playlist.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Playlist not found'}, status=404)
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def get_favorited_playlists(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Auth required'}, status=401)
    
    try:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            playlists = custom_user.favorited_playlists.all()
        except CustomUser.DoesNotExist:
            return JsonResponse({'success': True, 'playlists': []})
        
        data = []
        for p in playlists:
            data.append({
                'id': p.id,
                'name': p.name,
                'cover': p.cover.url if p.cover else "/media/playlists/default_playlist.png",
                'creator': p.user.username
            })
        return JsonResponse({'success': True, 'playlists': data})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)}, status=500)

def download_playlist_zip(request, playlist_id):
    try:
        if playlist_id == 'favorites':
            if not request.user.is_authenticated:
                return HttpResponse("Unauthorized", status=401)
            try:
                custom_user = CustomUser.objects.get(username=request.user.username)
                songs = custom_user.favorite_songs.all()
            except CustomUser.DoesNotExist:
                return HttpResponse("User profile not found", status=404)
            zip_name = "My_Favorite_Music.zip"
        elif playlist_id == 'recent':
            if not request.user.is_authenticated:
                return HttpResponse("Unauthorized", status=401)
            try:
                custom_user = CustomUser.objects.get(username=request.user.username)
                history = PlayHistory.objects.filter(user=custom_user).select_related('song').order_by('-played_at')[:20]
                songs = [h.song for h in history]
            except CustomUser.DoesNotExist:
                return HttpResponse("User profile not found", status=404)
            zip_name = "Recently_Played.zip"
        else:
            try:
                playlist = Playlist.objects.get(id=playlist_id)
                songs = playlist.songs.all()
                # Sanitize name for filename
                safe_name = re.sub(r'[^\w\s-]', '', playlist.name).strip().replace(' ', '_')
                zip_name = f"{safe_name}.zip"
            except Playlist.DoesNotExist:
                return HttpResponse("Playlist not found", status=404)

        if not songs:
            return HttpResponse("No songs found", status=404)

        buffer = BytesIO()
        with zipfile.ZipFile(buffer, 'w') as zip_file:
            for song in songs:
                if song.download_link:
                    try:
                        file_path = song.download_link.path
                        if os.path.exists(file_path):
                            # Use song name + ext for internal zip path
                            ext = os.path.splitext(file_path)[1]
                            internal_name = f"{song.name}{ext}"
                            zip_file.write(file_path, internal_name)
                    except Exception as e:
                        print(f"Error packing song {song.id}: {e}")
                        continue

        buffer.seek(0)
        response = FileResponse(buffer, content_type='application/zip')
        response['Content-Disposition'] = f'attachment; filename="{zip_name}"'
        return response

    except Exception as e:
        return HttpResponse(f"Error: {str(e)}", status=500)

def song_comments_view(request, song_id):
    try:
        song = Song.objects.get(id=song_id)
        # Hot comments: Dynamic threshold based on average performance, optimized with good_count
        # We define "Hot" as at least 5 likes AND at least 1.5x the average likes for this song
        agg = Comment.objects.filter(song=song).aggregate(avg_l=models.Avg('good_count'))
        avg_likes = agg['avg_l'] or 0
        dynamic_threshold = max(5, avg_likes * 1.5)

        hot_comments = Comment.objects.filter(
            song=song, 
            good_count__gte=dynamic_threshold
        ).order_by('-good_count')[:15]
        
        # Latest comments: all comments (including replies) ordered by time
        latest_comments = Comment.objects.filter(song=song).order_by('-created_at')
        
        user_liked_ids = []
        if request.user.is_authenticated:
            try:
                current_custom_user = CustomUser.objects.get(username=request.user.username)
                user_liked_ids = list(current_custom_user.liked_comments.filter(song=song).values_list('id', flat=True))
            except CustomUser.DoesNotExist:
                current_custom_user = None
                pass

        context = {
            'song': song,
            'hot_comments': hot_comments,
            'latest_comments': latest_comments,
            'user_liked_ids': user_liked_ids,
            'current_custom_user': current_custom_user,
            'total_count': Comment.objects.filter(song=song).count()
        }
        return render(request, 'music/comments.html', context)
    except Song.DoesNotExist:
        return HttpResponse("Song not found", status=404)

def api_get_comments(request, song_id):
    try:
        song = Song.objects.get(id=song_id)
        # Re-use hot comment logic
        agg = Comment.objects.filter(song=song).aggregate(avg_l=models.Avg('good_count'))
        avg_likes = agg['avg_l'] or 0
        dynamic_threshold = max(5, avg_likes * 1.5)

        hot_comments_query = Comment.objects.filter(song=song, good_count__gte=dynamic_threshold).order_by('-good_count')[:15]
        latest_comments_query = Comment.objects.filter(song=song).order_by('-created_at')

        user_liked_ids = []
        if request.user.is_authenticated:
            try:
                current_custom_user = CustomUser.objects.get(username=request.user.username)
                user_liked_ids = list(current_custom_user.liked_comments.filter(song=song).values_list('id', flat=True))
            except CustomUser.DoesNotExist:
                pass

        def serialize_comment(c):
            return {
                'id': c.id,
                'user': c.user.username,
                'user_id': c.user.id,
                'avatar': c.user.avatar.url if c.user.avatar else '/media/avatars/default.jpeg',
                'content': c.content,
                'created_at': c.created_at.strftime('%Y-%m-%d'),
                'good_count': c.good_count,
                'is_liked': c.id in user_liked_ids,
                'parent_username': c.parent.user.username if c.parent else None,
                'parent_content': c.parent.content[:100] if c.parent else None,
                'can_delete': (request.user.is_authenticated and (c.user.username == request.user.username or request.user.is_staff))
            }

        return JsonResponse({
            'success': True,
            'hot_comments': [serialize_comment(c) for c in hot_comments_query],
            'latest_comments': [serialize_comment(c) for c in latest_comments_query],
            'total_count': Comment.objects.filter(song=song).count()
        })
    except Song.DoesNotExist:
        return JsonResponse({'success': False, 'error': 'Song not found'})

def api_post_comment(request):
    if request.method != 'POST':
        return JsonResponse({'success': False, 'error': 'POST only'})
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Not logged in'})
    
    song_id = request.POST.get('song_id')
    content = request.POST.get('content')
    parent_id = request.POST.get('parent_id') # If it's a reply
    
    if not content or not song_id:
        return JsonResponse({'success': False, 'error': 'Content and Song ID required'})
        
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        song = Song.objects.get(id=song_id)
        parent = None
        if parent_id:
            parent = Comment.objects.get(id=parent_id)
            
        comment = Comment.objects.create(
            user=custom_user,
            song=song,
            content=content,
            parent=parent
        )
        return JsonResponse({'success': True, 'comment_id': comment.id})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)})

def api_toggle_comment_like(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Login required'})
    
    comment_id = request.POST.get('comment_id')
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        comment = Comment.objects.get(id=comment_id)
        if custom_user in comment.liked_by.all():
            comment.liked_by.remove(custom_user)
            comment.good_count = max(0, comment.good_count - 1)
            liked = False
        else:
            comment.liked_by.add(custom_user)
            comment.good_count += 1
            liked = True
        
        comment.save()
        
        return JsonResponse({'success': True, 'is_liked': liked, 'likes_count': comment.good_count})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)})

def api_delete_comment(request):
    if not request.user.is_authenticated:
        return JsonResponse({'success': False, 'error': 'Login required'})
    
    comment_id = request.POST.get('comment_id')
    try:
        custom_user = CustomUser.objects.get(username=request.user.username)
        comment = Comment.objects.get(id=comment_id)
        if comment.user.id == custom_user.id or request.user.is_staff:
            comment.delete()
            return JsonResponse({'success': True})
        return JsonResponse({'success': False, 'error': 'Unauthorized'})
    except Exception as e:
        return JsonResponse({'success': False, 'error': str(e)})
