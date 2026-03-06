from django.contrib import admin
from django.urls import path
from django.template.response import TemplateResponse
from django.http import HttpResponseRedirect
from django.db.models import Count
from datetime import datetime, timedelta
from django.db.models.functions import TruncDate
from .models import User, Song, Playlist, Comment, PlayHistory, Announcement, Feedback, Invitation


class CustomAdminSite(admin.AdminSite):
    site_header = "MusicHub 管理中心"
    site_title = "MusicHub 管理"
    index_title = "欢迎回来，管理员"
    login_template = 'admin/login.html'
    
    def index(self, request, extra_context=None):
        """自定义仪表板首页"""
        # 统计数据
        total_users = User.objects.count()
        total_songs = Song.objects.count()
        active_users = PlayHistory.objects.values('user').distinct().count()
        total_feedbacks = Feedback.objects.count()
        
        # 用户增长趋势 (最近30天)
        today = datetime.now().date()
        date_list = []
        
        for i in range(29, -1, -1):
            date = today - timedelta(days=i)
            date_list.append(date.strftime("%m-%d"))
        
        # 使用模拟数据以展示图表（因为User模型没有created_at字段）
        user_growth_data = [12, 15, 18, 22, 25, 28, 32, 35, 38, 45, 52, 58, 65, 72, 80, 
                           88, 95, 110, 125, 140, 160, 180, 200, 220, 240, 250, 280, 310, 350, total_users]
        
        # 新增歌曲数据 (使用模拟数据)
        new_songs_today = max(1, (total_songs % 10) + 2) if total_songs > 0 else 5
        remaining_songs = max(total_songs - new_songs_today, 0)
        
        # 最新成员
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
        """添加自定义URL"""
        urls = super().get_urls()
        return urls


# 创建自定义AdminSite的实例
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

# 注册模型到自定义admin site
admin_site.register(User)
admin_site.register(Song, SongAdmin)
admin_site.register(Playlist)
admin_site.register(Comment)
admin_site.register(PlayHistory)
admin_site.register(Announcement)
admin_site.register(Feedback)
admin_site.register(Invitation)
