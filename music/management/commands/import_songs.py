from django.core.management.base import BaseCommand
from django.conf import settings
import os
import datetime

from music.models import Song


class Command(BaseCommand):
    help = 'Import mp3 files from GP/php/audios/Download into Song model as library entries'

    def handle(self, *args, **options):
        directory = os.path.join(settings.BASE_DIR, 'GP', 'php', 'audios', 'Download')
        if not os.path.isdir(directory):
            self.stdout.write(self.style.ERROR(f'Directory not found: {directory}'))
            return
        count = 0
        for filename in os.listdir(directory):
            if not filename.lower().endswith('.mp3'):
                continue
            name = os.path.splitext(filename)[0]
            src_path = os.path.join(directory, filename)
            # determine static relative path
            dest_dir = os.path.join(settings.BASE_DIR, 'music', 'static', 'music', 'audio')
            os.makedirs(dest_dir, exist_ok=True)
            dest_path = os.path.join(dest_dir, filename)
            try:
                # copy file if not already present
                if not os.path.exists(dest_path):
                    import shutil
                    shutil.copy2(src_path, dest_path)
            except Exception as e:
                self.stdout.write(self.style.WARNING(f'Failed to copy {filename}: {e}'))

            download_path = f'music/audio/{filename}'  # relative path for {% static %}

            # use get_or_create to avoid duplicates
            song, created = Song.objects.get_or_create(name=name, defaults={
                'album': 'Unknown',
                'arrangement': 'Unknown',
                'song_type': 'Unknown',
                'release_date': datetime.date.today(),
                'link': '',
                'download_link': download_path,
            })
            if created:
                self.stdout.write(self.style.SUCCESS(f'Created song: {name}'))
                count += 1
            else:
                self.stdout.write(f'Song already exists: {name}')
        self.stdout.write(self.style.NOTICE(f'Total new songs: {count}'))
