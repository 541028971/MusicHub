import os
import django

os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'MusicHub.settings')
django.setup()

from music.models import User, Song, Playlist, PlayHistory, Feedback
from datetime import datetime, timedelta

# 创建一些测试用户
users = []
for i in range(1, 15):
    user, created = User.objects.get_or_create(
        username=f'user_{i}',
        defaults={
            'password': 'test123',
            'email': f'user{i}@test.com',
            'identity': 'user',
            'status': 'active'
        }
    )
    users.append(user)

# 创建一些测试歌曲
songs = []
song_types = ['Pop', 'Rock', 'Jazz', 'Classical', 'Hip-Hop']
for i in range(1, 21):
    song, created = Song.objects.get_or_create(
        name=f'Song {i}',
        defaults={
            'album': f'Album {(i-1)//4 + 1}',
            'arrangement': 'arrangement',
            'song_type': song_types[i % 5],
            'release_date': datetime.now().date() - timedelta(days=i*2),
            'link': f'/audios/song{i}.mp3',
            'views': i * 100
        }
    )
    songs.append(song)

# 创建一些播放历史
for user in users[:10]:
    for song in songs[:5]:
        PlayHistory.objects.get_or_create(
            user=user,
            song=song,
            defaults={'played_at': datetime.now()}
        )

# 创建一些反馈
for i in range(1, 5):
    Feedback.objects.get_or_create(
        sender=users[i],
        receiver=users[0],
        defaults={'content': f'Feedback {i}', 'created_at': datetime.now()}
    )

print(f"创建了 {User.objects.count()} 个用户")
print(f"创建了 {Song.objects.count()} 首歌曲") 
print(f"创建了 {PlayHistory.objects.count()} 条播放历史")
print(f"创建了 {Feedback.objects.count()} 条反馈")
