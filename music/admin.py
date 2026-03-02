from django.contrib import admin
from .models import User, Song, Playlist, Comment, PlayHistory, Announcement, Feedback, Invitation

# Register your models here.
admin.site.register(User)
admin.site.register(Song)
admin.site.register(Playlist)
admin.site.register(Comment)
admin.site.register(PlayHistory)
admin.site.register(Announcement)
admin.site.register(Feedback)
admin.site.register(Invitation)
