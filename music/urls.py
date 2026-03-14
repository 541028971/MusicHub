from django.urls import path
from . import views

app_name = 'music'

urlpatterns = [
    path('', views.index, name='index'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),
    path('register/', views.register_or_edit_view, name='register'),
    # User Area - static library and song detail (initial static pages)
    path('library/', views.music_library_view, name='library'),
    path('song/<int:pk>/', views.song_detail_view, name='song_detail'),
    # API endpoints
    path('api/playlists/', views.get_user_playlists, name='get_playlists'),
    path('api/add-to-playlist/', views.add_to_playlist, name='add_to_playlist'),
    path('api/create-playlist/', views.create_playlist, name='create_playlist'),
    path('api/increment-song-view/', views.increment_song_view, name='increment_song_view'),
    path('api/increment-playlist-view/', views.increment_playlist_view, name='increment_playlist_view'),
    path('api/playlist-details/<str:playlist_id>/', views.get_playlist_details, name='get_playlist_details'),
    path('api/record-recent-play/', views.record_recent_play, name='record_recent_play'),
    path('api/toggle-favorite/', views.toggle_favorite, name='toggle_favorite'),
    path('api/check-favorite/', views.check_favorite, name='check_favorite'),
    path('api/remove-from-playlist/', views.remove_from_playlist, name='remove_from_playlist'),
    path('api/update-last-played/', views.update_last_played, name='update_last_played'),
    path('search/', views.music_library_view, name='search'),
]

