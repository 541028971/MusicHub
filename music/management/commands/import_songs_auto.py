"""
MusicHub 自动导入歌曲 Management Command
用法:
    python manage.py import_songs_auto --dir "D:\..."
    python manage.py import_songs_auto --dir "D:\..." --skip-intro
    python manage.py import_songs_auto --dir "D:\..." --song-type "Game"
"""

import os
import time
import tempfile
import datetime

import requests
from mutagen.id3 import ID3

from django.core.management.base import BaseCommand
from django.core.files import File

from music.models import Song


class Command(BaseCommand):
    help = '从指定目录自动导入MP3歌曲，通过iTunes API获取元数据'

    def add_arguments(self, parser):
        parser.add_argument(
            '--dir',
            type=str,
            action='append',
            dest='dirs',
            required=True,
            help='包含MP3文件的目录（可多次使用来指定多个目录）'
        )
        parser.add_argument(
            '--skip-intro',
            action='store_true',
            default=False,
            help='跳过AI简介生成，使用默认"No Introduction"'
        )
        parser.add_argument(
            '--song-type',
            type=str,
            default=None,
            help='强制指定song_type，覆盖iTunes返回的值（如 Game、Anime、Soundtrack）'
        )

    def handle(self, *args, **options):
        dirs = options['dirs']
        skip_intro = options['skip_intro']
        force_song_type = options.get('song_type')

        # 收集所有MP3文件
        all_mp3s = []
        for directory in dirs:
            if not os.path.isdir(directory):
                self.stdout.write(self.style.ERROR(f'目录不存在: {directory}'))
                continue
            for filename in os.listdir(directory):
                if filename.lower().endswith('.mp3'):
                    all_mp3s.append((directory, filename))

        if not all_mp3s:
            self.stdout.write(self.style.ERROR('未找到任何MP3文件'))
            return

        self.stdout.write(f'共找到 {len(all_mp3s)} 个MP3文件\n')

        success_count = 0
        skip_count = 0
        error_count = 0

        for directory, filename in all_mp3s:
            mp3_path = os.path.join(directory, filename)
            lrc_path = os.path.splitext(mp3_path)[0] + '.lrc'

            self.stdout.write(f'处理: {filename}')

            # ── 1. 读取 ID3 标签 ──────────────────────────────────────
            try:
                id3 = ID3(mp3_path)
                name = str(id3['TIT2']) if 'TIT2' in id3 else None
                artist = str(id3['TPE1']) if 'TPE1' in id3 else None
                album_from_id3 = str(id3['TALB']) if 'TALB' in id3 else None
            except Exception as e:
                self.stdout.write(self.style.WARNING(f'  ID3读取失败: {e}'))
                id3 = None
                name = None
                artist = None
                album_from_id3 = None

            # 如果ID3没有歌名，从文件名解析（格式：歌手 - 歌名.mp3）
            if not name:
                base = os.path.splitext(filename)[0]
                if ' - ' in base:
                    artist_from_file, name = base.split(' - ', 1)
                    if not artist:
                        artist = artist_from_file.strip()
                    name = name.strip()
                else:
                    name = base.strip()

            if not artist:
                artist = 'Unknown Artist'

            # 多作者分隔符 / 替换为 |
            artist = artist.replace('/', '|')

            # ── 2. 检查是否已存在 ─────────────────────────────────────
            if Song.objects.filter(name=name).exists():
                self.stdout.write(self.style.WARNING(f'  跳过（已存在）: {name}\n'))
                skip_count += 1
                continue

            # ── 3. 查询 iTunes API ────────────────────────────────────
            itunes = self._search_itunes(name, artist)

            if itunes:
                album = itunes.get('collectionName', album_from_id3 or 'Unknown Album')
                song_type = force_song_type if force_song_type else itunes.get('primaryGenreName', 'Unknown')
                release_str = itunes.get('releaseDate', '')[:10]
                cover_url = itunes.get('artworkUrl100', '').replace('100x100', '600x600')
                try:
                    release_date = datetime.date.fromisoformat(release_str)
                except Exception:
                    release_date = datetime.date.today()
                self.stdout.write(f'  iTunes: 找到 [{song_type}] {release_date}')
            else:
                album = album_from_id3 or 'Unknown Album'
                song_type = force_song_type if force_song_type else 'Unknown'
                release_date = datetime.date.today()
                cover_url = None
                self.stdout.write(self.style.WARNING('  iTunes: 未找到，使用默认值'))

            # ── 4. 生成简介 ───────────────────────────────────────────
            if skip_intro:
                introduction = 'No Introduction'
            else:
                introduction = self._generate_intro(name, artist, album)
                self.stdout.write(f'  简介: 已生成')

            # ── 5. 第一次保存（获取ID）────────────────────────────────
            try:
                song = Song(
                    name=name,
                    album=album,
                    arrangement=artist,
                    song_type=song_type,
                    introduction=introduction,
                    release_date=release_date,
                )
                song.save()
                self.stdout.write(f'  数据库: 已创建 ID={song.id}')
            except Exception as e:
                self.stdout.write(self.style.ERROR(f'  数据库保存失败: {e}\n'))
                error_count += 1
                continue

            # ── 6. 附加封面 ───────────────────────────────────────────
            if cover_url:
                try:
                    resp = requests.get(cover_url, timeout=10)
                    resp.raise_for_status()
                    with tempfile.NamedTemporaryFile(suffix='.jpg', delete=False) as tmp:
                        tmp.write(resp.content)
                        tmp_path = tmp.name
                    with open(tmp_path, 'rb') as f:
                        song.cover.save(f'{name}.jpg', File(f), save=False)
                    os.unlink(tmp_path)
                    self.stdout.write(f'  封面: 已下载')
                except Exception as e:
                    self.stdout.write(self.style.WARNING(f'  封面下载失败: {e}'))

            # ── 7. 附加音频文件 ───────────────────────────────────────
            try:
                with open(mp3_path, 'rb') as f:
                    song.download_link.save(filename, File(f), save=False)
                self.stdout.write(f'  音频: 已附加')
            except Exception as e:
                self.stdout.write(self.style.WARNING(f'  音频附加失败: {e}'))

            # ── 8. 附加歌词文件（纯音乐跳过）────────────────────────────
            if os.path.exists(lrc_path):
                try:
                    with open(lrc_path, 'r', encoding='utf-8', errors='ignore') as f:
                        lrc_content = f.read()
                    if '纯音乐' in lrc_content:
                        self.stdout.write(f'  歌词: 纯音乐，跳过')
                    else:
                        with open(lrc_path, 'rb') as f:
                            lrc_filename = os.path.basename(lrc_path)
                            song.lyrics.save(lrc_filename, File(f), save=False)
                        self.stdout.write(f'  歌词: 已附加')
                except Exception as e:
                    self.stdout.write(self.style.WARNING(f'  歌词附加失败: {e}'))
            else:
                self.stdout.write(f'  歌词: 无对应LRC文件')

            # ── 9. 最终保存（触发signal自动整理文件路径）────────────────
            try:
                song.save()
                self.stdout.write(f'  完成: {name}\n')
                success_count += 1
            except Exception as e:
                self.stdout.write(self.style.ERROR(f'  最终保存失败: {e}\n'))
                error_count += 1

            # 避免iTunes API频率限制
            time.sleep(0.3)

        # ── 汇总 ─────────────────────────────────────────────────────
        self.stdout.write('=' * 50)
        self.stdout.write(self.style.SUCCESS(f'成功: {success_count}'))
        self.stdout.write(self.style.WARNING(f'跳过: {skip_count}'))
        if error_count:
            self.stdout.write(self.style.ERROR(f'失败: {error_count}'))

    def _search_itunes(self, name, artist):
        """查询iTunes API获取歌曲元数据"""
        try:
            r = requests.get(
                'https://itunes.apple.com/search',
                params={'term': f'{name} {artist}', 'media': 'music', 'limit': 1},
                timeout=10
            )
            results = r.json().get('results', [])
            return results[0] if results else None
        except Exception as e:
            self.stdout.write(self.style.WARNING(f'  iTunes请求失败: {e}'))
            return None

    def _generate_intro(self, name, artist, album):
        """调用Claude API生成歌曲简介"""
        try:
            import anthropic
            client = anthropic.Anthropic()
            message = client.messages.create(
                model='claude-haiku-4-5',
                max_tokens=150,
                messages=[{
                    'role': 'user',
                    'content': (
                        f"Write a brief 2-3 sentence introduction for the song '{name}' "
                        f"by {artist} from the album '{album}'. "
                        f"Be concise, factual and informative. No markdown."
                    )
                }]
            )
            return message.content[0].text.strip()
        except Exception as e:
            self.stdout.write(self.style.WARNING(f'  AI简介生成失败: {e}'))
            return 'No Introduction'
