from django.db import models
from django.contrib.auth.models import User as AuthUser
from django.db.models.signals import post_delete
from django.dispatch import receiver
import os
import re
from mutagen.mp3 import MP3

def get_audio_duration(file_path):
    try:
        audio = MP3(file_path)
        length = int(audio.info.length)
        minutes = length // 60
        seconds = length % 60
        return f"{minutes:02d}:{seconds:02d}"
    except Exception as e:
        return "00:00"

def song_cover_path(instance, filename):
    ext = filename.split('.')[-1]
    # Keep alphanumeric characters including Unicode (Chinese, Japanese, etc.)
    # Replace anything else with an underscore
    safe_name = re.sub(r'[^\w\s-]', '', instance.name).strip().replace(' ', '_')
    # Add ID for uniqueness if available
    prefix = f"{instance.id}_" if instance.id else ""
    return os.path.join('covers', f"{prefix}{safe_name}.{ext}")

def song_audio_path(instance, filename):
    ext = filename.split('.')[-1]
    safe_name = re.sub(r'[^\w\s-]', '', instance.name).strip().replace(' ', '_')
    prefix = f"{instance.id}_" if instance.id else ""
    return os.path.join('songs', f"{prefix}{safe_name}.{ext}")

# 1. User Model
class User(models.Model):
    # Django will automatically create id as primary key (replacing uid)
    username = models.CharField(max_length=100)
    password = models.CharField(max_length=100)
    avatar = models.ImageField(upload_to='avatars/', default='avatars/default.jpeg')
    date_joined = models.DateTimeField(auto_now_add=True, null=True)
    birth = models.DateField(null=True, blank=True)
    identity = models.CharField(max_length=100)
    status = models.CharField(max_length=100)
    email = models.CharField(max_length=100)
    phone_number = models.CharField(max_length=11, null=True, blank=True)
    city = models.CharField(max_length=100, null=True, blank=True)
    membership = models.IntegerField(default=0)
    last_played_song = models.ForeignKey('Song', on_delete=models.SET_NULL, null=True, blank=True)

    def __str__(self):
        return self.username


# 2. Song Model
class Song(models.Model):
    # Django will automatically create id as primary key (replacing s_id)
    name = models.CharField(max_length=100) # sname -> name
    album = models.CharField(max_length=100)
    lyrics = models.TextField(default='Pure Music', null=True, blank=True)
    cover = models.ImageField(upload_to=song_cover_path, default='covers/default.jpg')
    arrangement = models.CharField(max_length=100)
    song_type = models.CharField(max_length=100) # stype -> song_type
    introduction = models.TextField(default='No Introduction') # sintroduction -> introduction
    release_date = models.DateField() # release_time -> release_date
    views = models.IntegerField(default=0)
    download_link = models.FileField(upload_to=song_audio_path, null=True, blank=True) # download -> download_link
    
    # Replaces favourite table
    favorited_by = models.ManyToManyField(User, related_name='favorite_songs', blank=True)

    def save(self, *args, **kwargs):
        # Handle file cleanup if the name changed or replacement occurred
        if self.pk:
            try:
                old_song = Song.objects.get(pk=self.pk)
                if old_song.name != self.name:
                    # Logic here could rename physical files, but Django ImageField usually needs manual move
                    # For now, let's keep it simple: new uploads will follow the new name.
                    pass
            except Song.DoesNotExist:
                pass
        super().save(*args, **kwargs)

    def __str__(self):
        return self.name


# 3. Playlist Model
class Playlist(models.Model):
    # Django will automatically create id as primary key (replacing pid)
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='playlists') # uid -> user_id (in database)
    name = models.CharField(max_length=100) # pname -> name
    cover = models.CharField(max_length=100) # pcover -> cover
    is_private = models.BooleanField(default=False) # private -> is_private
    views = models.IntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True, null=True)
    
    # Replaces playlist_songs table
    songs = models.ManyToManyField(Song, related_name='included_in_playlists', blank=True)

    def __str__(self):
        return self.name


# 4. Comment Model
class Comment(models.Model):
    # Django will automatically create id as primary key (replacing cid)
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='comments') # uid -> user_id
    song = models.ForeignKey(Song, on_delete=models.CASCADE, related_name='comments') # s_id -> song_id
    content = models.TextField()
    good_count = models.IntegerField(default=0) # good -> good_count
    bad_count = models.IntegerField(default=0)  # bad -> bad_count
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at

    def __str__(self):
        return f"{self.user.username} - {self.song.name}"


# 5. PlayHistory Model (replacing history table)
class PlayHistory(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='play_history') # uid -> user_id
    song = models.ForeignKey(Song, on_delete=models.CASCADE, related_name='play_history') # s_id -> song_id
    played_at = models.DateTimeField(auto_now_add=True) # time -> played_at

    def __str__(self):
        return f"{self.user.username} played {self.song.name}"


# 6. Announcement Model
class Announcement(models.Model):
    # Django will automatically create id as primary key (replacing aid)
    sender = models.ForeignKey(User, on_delete=models.CASCADE, related_name='sent_announcements') # srcuid
    receiver = models.ForeignKey(User, on_delete=models.CASCADE, related_name='received_announcements') # desuid
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at
    content = models.TextField()

    def __str__(self):
        return f"Announcement from {self.sender.username} to {self.receiver.username}"


# 7. Feedback Model
class Feedback(models.Model):
    # Django will automatically create id as primary key (replacing fid)
    sender = models.ForeignKey(User, on_delete=models.CASCADE, related_name='sent_feedbacks') # srcuid
    receiver = models.ForeignKey(User, on_delete=models.CASCADE, related_name='received_feedbacks') # desuid
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at
    content = models.TextField()

    def __str__(self):
        return f"Feedback from {self.sender.username}"


# 8. Invitation Model
class Invitation(models.Model):
    # Django will automatically create id as primary key (replacing iid)
    code = models.IntegerField()

    def __str__(self):
        return str(self.code)

# Signals to keep CustomUser and AuthUser synchronized when deleted from Admin
@receiver(post_delete, sender=User)
def delete_auth_user(sender, instance, **kwargs):
    if instance.username:
        AuthUser.objects.filter(username=instance.username).delete()

@receiver(post_delete, sender=AuthUser)
def delete_custom_user(sender, instance, **kwargs):
    if instance.username:
        User.objects.filter(username=instance.username).delete()
