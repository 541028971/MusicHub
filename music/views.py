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
from django.http import StreamingHttpResponse, Http404, HttpResponse
from .models import Song, User as CustomUser
from .forms import UserRegistrationForm, UserProfileUpdateForm

def index(request):
    # For now we'll simulate 'Top Trending' and 'Recently Added' by ordering
    top_trending = list(Song.objects.all()[:12]) # In real app, order by likes or plays
    recently_added = list(Song.objects.order_by('-id')[:12])
    
    context = {
        'top_trending': top_trending,
        'recently_added': recently_added,
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
    """Static/initial Music Library view - supports simple query and pagination."""
    q = request.GET.get('q', '')
    songs = Song.objects.all().order_by('-id')
    if q:
        songs = songs.filter(name__icontains=q)

    # Simple pagination
    from django.core.paginator import Paginator
    paginator = Paginator(songs, 12)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)

    context = {
        'page_obj': page_obj,
        'q': q,
    }
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
