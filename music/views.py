import os
import base64
from django.conf import settings
from django.core.files.base import ContentFile
from django.shortcuts import render, redirect
from django.contrib.auth import authenticate, login as auth_login, logout
from django.contrib.auth.models import User as AuthUser
from django.contrib import messages
from .models import Song, User as CustomUser
from .forms import UserRegistrationForm, UserProfileUpdateForm

def index(request):
    random_song = Song.objects.order_by('?').first()
    return render(request, 'music/index.html', {'random_song': random_song})

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
        return 'images/grid (1).jpg' # default
        
    try:
        format, imgstr = base66_data.split(';base64,') 
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
        return 'images/grid (1).jpg'

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
                user_instance.avatar = 'images/grid (1).jpg' # default fallback
                
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
