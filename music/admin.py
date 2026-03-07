from django.contrib import admin
from django.urls import path
from django.template.response import TemplateResponse
from django.http import HttpResponseRedirect
from django.db.models import Count
from datetime import datetime, timedelta
from django.db.models.functions import TruncDate
from .models import User, Song, Playlist, Comment, PlayHistory, Announcement, Feedback, Invitation


class CustomAdminSite(admin.AdminSite):
    site_header = "MusicHub Admin Center"
    site_title = "MusicHub Administration"
    index_title = "Welcome back, Administrator"
    login_template = 'admin/login.html'
    
    def index(self, request, extra_context=None):
        """Custom dashboard index"""
        # App Statistics
        total_users = User.objects.count()
        total_songs = Song.objects.count()
        active_users = PlayHistory.objects.values('user').distinct().count()
        total_feedbacks = Feedback.objects.count()
        
        # User growth trend (Last 30 days)
        today = datetime.now().date()
        date_list = []
        
        for i in range(29, -1, -1):
            date = today - timedelta(days=i)
            date_list.append(date.strftime("%m-%d"))
        
        # Use mock data to display chart (since User model has no created_at field)
        user_growth_data = [12, 15, 18, 22, 25, 28, 32, 35, 38, 45, 52, 58, 65, 72, 80, 
                           88, 95, 110, 125, 140, 160, 180, 200, 220, 240, 250, 280, 310, 350, total_users]
        
        # New songs today data (Use mock data)
        new_songs_today = max(1, (total_songs % 10) + 2) if total_songs > 0 else 5
        remaining_songs = max(total_songs - new_songs_today, 0)
        
        # Newest members
        newest_members = User.objects.all().order_by('-id')[:3]
        
        extra_context = extra_context or {}
        extra_context.update({
            'total_users': total_users,
            'total_songs': total_songs,
            'active_users': active_users,
            'total_feedbacks': total_feedbacks,
            'date_list': date_list,
            'user_growth': user_growth_data,
            'new_songs_today': new_songs_today,
            'remaining_songs': remaining_songs,
            'newest_members': newest_members,
        })
        
        request.current_app = self.name
        return TemplateResponse(request, 'admin/dashboard.html', extra_context)
    
    def get_urls(self):
        """Add custom URLs"""
        urls = super().get_urls()
        return urls


# Create instance of custom AdminSite
admin_site = CustomAdminSite(name='admin')

from django.utils.html import format_html

class SongAdmin(admin.ModelAdmin):
    list_display = ('name', 'album', 'cover_preview', 'song_type', 'release_date')
    search_fields = ('name', 'album')
    list_filter = ('song_type', 'release_date')
    
    def cover_preview(self, obj):
        if obj.cover:
            try:
                return format_html('<img src="{}" width="50" height="50" style="object-fit:cover; border-radius:4px;" />', obj.cover.url)
            except ValueError:
                return "No File"
        return "No Image"
    cover_preview.short_description = 'Cover'

class UserAdmin(admin.ModelAdmin):
    list_display = ('username', 'email', 'avatar_preview', 'identity', 'status_tag')
    search_fields = ('username', 'email')
    list_filter = ('identity', 'status')
    actions = ['ban_users', 'unban_users']
    
    def avatar_preview(self, obj):
        if obj.avatar:
            try:
                return format_html('<img src="{}" width="40" height="40" style="object-fit:cover; border-radius:50%;" />', obj.avatar.url)
            except ValueError:
                return "No File"
        return "No Image"
    avatar_preview.short_description = 'Avatar'

    def status_tag(self, obj):
        color = 'green' if obj.status.lower() in ['active', 'normal'] else 'red'
        return format_html('<span style="color: {}; font-weight:bold;">{}</span>', color, obj.status)
    status_tag.short_description = 'Status'

    @admin.action(description='Ban selected users')
    def ban_users(self, request, queryset):
        updated = queryset.update(status='Banned')
        self.message_user(request, f'Successfully banned {updated} users.')

    @admin.action(description='Unban selected users')
    def unban_users(self, request, queryset):
        updated = queryset.update(status='Active')
        self.message_user(request, f'Successfully unbanned {updated} users.')

# Register models to custom admin site
admin_site.register(User, UserAdmin)
admin_site.register(Song, SongAdmin)
admin_site.register(Playlist)
admin_site.register(Comment)
admin_site.register(PlayHistory)
admin_site.register(Announcement)
admin_site.register(Feedback)
admin_site.register(Invitation)
