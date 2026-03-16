from django.test import TestCase, Client
from django.urls import reverse
import datetime

from .models import User, Song, Playlist, Comment, get_audio_duration


# ──────────────────────────────────────────────
# Model Tests
# ──────────────────────────────────────────────

class UserModelTest(TestCase):

    def setUp(self):
        self.user = User.objects.create(
            username='testuser',
            password='password123',
            status='Active',
            email='test@example.com',
        )

    def test_str_returns_username(self):
        """User __str__ should return the username."""
        self.assertEqual(str(self.user), 'testuser')


class SongModelTest(TestCase):

    def setUp(self):
        self.song = Song.objects.create(
            name='Test Song',
            song_type='Pop',
            release_date=datetime.date(2024, 1, 1),
        )

    def test_str_returns_song_name(self):
        """Song __str__ should return the song name."""
        self.assertEqual(str(self.song), 'Test Song')

    def test_default_views_is_zero(self):
        """Song views should default to 0."""
        self.assertEqual(self.song.views, 0)

    def test_default_album_is_unknown(self):
        """Song album should default to 'Unknown Album'."""
        self.assertEqual(self.song.album, 'Unknown Album')


class PlaylistModelTest(TestCase):

    def setUp(self):
        self.user = User.objects.create(
            username='playlistuser',
            password='password123',
            status='Active',
            email='playlist@example.com',
        )
        self.playlist = Playlist.objects.create(
            user=self.user,
            name='My Playlist',
        )

    def test_str_returns_playlist_name(self):
        """Playlist __str__ should return the playlist name."""
        self.assertEqual(str(self.playlist), 'My Playlist')

    def test_default_is_not_private(self):
        """Playlist should be public by default."""
        self.assertFalse(self.playlist.is_private)

    def test_default_views_is_zero(self):
        """Playlist views should default to 0."""
        self.assertEqual(self.playlist.views, 0)


class CommentModelTest(TestCase):

    def setUp(self):
        self.user = User.objects.create(
            username='commenter',
            password='password123',
            status='Active',
            email='comment@example.com',
        )
        self.song = Song.objects.create(
            name='Comment Song',
            song_type='Jazz',
            release_date=datetime.date(2024, 1, 1),
        )
        self.comment = Comment.objects.create(
            user=self.user,
            song=self.song,
            content='Great song!',
        )

    def test_str_format(self):
        """Comment __str__ should include username and song name."""
        result = str(self.comment)
        self.assertIn('commenter', result)
        self.assertIn('Comment Song', result)

    def test_default_good_count_is_zero(self):
        """Comment good_count should default to 0."""
        self.assertEqual(self.comment.good_count, 0)


class GetAudioDurationTest(TestCase):

    def test_invalid_path_returns_default(self):
        """get_audio_duration with invalid path should return '00:00'."""
        result = get_audio_duration('/nonexistent/path/file.mp3')
        self.assertEqual(result, '00:00')

    def test_empty_string_returns_default(self):
        """get_audio_duration with empty string should return '00:00'."""
        result = get_audio_duration('')
        self.assertEqual(result, '00:00')


# ──────────────────────────────────────────────
# View Tests
# ──────────────────────────────────────────────

class IndexViewTest(TestCase):

    def setUp(self):
        self.client = Client()

    def test_index_returns_200(self):
        """Home page should return HTTP 200."""
        response = self.client.get(reverse('music:index'))
        self.assertEqual(response.status_code, 200)


class RegisterViewTest(TestCase):

    def setUp(self):
        self.client = Client()

    def test_register_page_returns_200(self):
        """Register page (GET) should return HTTP 200."""
        response = self.client.get(reverse('music:register'))
        self.assertEqual(response.status_code, 200)


class LibraryViewTest(TestCase):

    def setUp(self):
        self.client = Client()

    def test_library_returns_200(self):
        """Music library page should return HTTP 200."""
        response = self.client.get(reverse('music:library'))
        self.assertEqual(response.status_code, 200)
