from django.db import models
from django.contrib.auth.models import User as AuthUser
from django.db.models.signals import post_delete
from django.dispatch import receiver

# 1. User 模型
class User(models.Model):
    # Django 会自动创建 id 作为主键 (取代 uid)
    username = models.CharField(max_length=100)
    password = models.CharField(max_length=100)
    avatar = models.CharField(max_length=100, default='images/Avatar/default.jpeg')
    birth = models.DateField(null=True, blank=True)
    identity = models.CharField(max_length=100)
    status = models.CharField(max_length=100)
    email = models.CharField(max_length=100)
    phone_number = models.CharField(max_length=11, null=True, blank=True)
    city = models.CharField(max_length=100, null=True, blank=True)
    membership = models.IntegerField(default=0)

    def __str__(self):
        return self.username


# 2. Song 模型
class Song(models.Model):
    # Django 会自动创建 id 作为主键 (取代 s_id)
    name = models.CharField(max_length=100) # sname -> name
    album = models.CharField(max_length=100)
    lyrics = models.TextField(default='Pure Music', null=True, blank=True)
    cover = models.ImageField(upload_to='covers/', default='covers/default.jpg')
    arrangement = models.CharField(max_length=100)
    song_type = models.CharField(max_length=100) # stype -> song_type
    introduction = models.TextField(default='No Introduction') # sintroduction -> introduction
    release_date = models.DateField() # release_time -> release_date
    link = models.FileField(upload_to='songs/')
    views = models.IntegerField(default=0)
    download_link = models.FileField(upload_to='songs/downloads/', null=True, blank=True) # download -> download_link
    
    # 取代 favourite 表
    favorited_by = models.ManyToManyField(User, related_name='favorite_songs', blank=True)

    def __str__(self):
        return self.name


# 3. Playlist 模型
class Playlist(models.Model):
    # Django 会自动创建 id 作为主键 (取代 pid)
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='playlists') # uid -> user_id (数据库中)
    name = models.CharField(max_length=100) # pname -> name
    cover = models.CharField(max_length=100) # pcover -> cover
    is_private = models.BooleanField(default=False) # private -> is_private
    views = models.IntegerField(default=0)
    
    # 取代 playlist_songs 表
    songs = models.ManyToManyField(Song, related_name='included_in_playlists', blank=True)

    def __str__(self):
        return self.name


# 4. Comment 模型
class Comment(models.Model):
    # Django 会自动创建 id 作为主键 (取代 cid)
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='comments') # uid -> user_id
    song = models.ForeignKey(Song, on_delete=models.CASCADE, related_name='comments') # s_id -> song_id
    content = models.TextField()
    good_count = models.IntegerField(default=0) # good -> good_count
    bad_count = models.IntegerField(default=0)  # bad -> bad_count
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at

    def __str__(self):
        return f"{self.user.username} - {self.song.name}"


# 5. PlayHistory 模型 (取代 history 表)
class PlayHistory(models.Model):
    user = models.ForeignKey(User, on_delete=models.CASCADE, related_name='play_history') # uid -> user_id
    song = models.ForeignKey(Song, on_delete=models.CASCADE, related_name='play_history') # s_id -> song_id
    played_at = models.DateTimeField(auto_now_add=True) # time -> played_at

    def __str__(self):
        return f"{self.user.username} played {self.song.name}"


# 6. Announcement 模型
class Announcement(models.Model):
    # Django 会自动创建 id 作为主键 (取代 aid)
    sender = models.ForeignKey(User, on_delete=models.CASCADE, related_name='sent_announcements') # srcuid
    receiver = models.ForeignKey(User, on_delete=models.CASCADE, related_name='received_announcements') # desuid
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at
    content = models.TextField()

    def __str__(self):
        return f"Announcement from {self.sender.username} to {self.receiver.username}"


# 7. Feedback 模型
class Feedback(models.Model):
    # Django 会自动创建 id 作为主键 (取代 fid)
    sender = models.ForeignKey(User, on_delete=models.CASCADE, related_name='sent_feedbacks') # srcuid
    receiver = models.ForeignKey(User, on_delete=models.CASCADE, related_name='received_feedbacks') # desuid
    created_at = models.DateTimeField(auto_now_add=True) # time -> created_at
    content = models.TextField()

    def __str__(self):
        return f"Feedback from {self.sender.username}"


# 8. Invitation 模型
class Invitation(models.Model):
    # Django 会自动创建 id 作为主键 (取代 iid)
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
