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
]
