import os
import django

os.environ.setdefault("DJANGO_SETTINGS_MODULE", "MusicHub.settings")
django.setup()

from django.contrib.auth.models import User as DjangoUser
from music.models import User as AppUser
import datetime

def create_ginga_superuser():
    username = "Ginga"
    email = "ginga2003@163.com"
    password = "Ginga"

    # Create the standard Django superuser to access the admin panel
    if not DjangoUser.objects.filter(username=username).exists():
        print(f"Creating Django superuser: {username}")
        DjangoUser.objects.create_superuser(username, email, password)
    else:
        print(f"Django superuser '{username}' already exists.")

    # Create the App user for the business logic based on test.sql
    if not AppUser.objects.filter(username=username).exists():
        print(f"Creating App User: {username}")
        AppUser.objects.create(
            username=username,
            password=password, # Note: In a real app, hash this password, but keeping it raw for now to match test.sql
            avatar='images/Avatar/Ginga.png',
            birth=datetime.date(2003, 8, 30),
            identity='Creator',
            status='Enabled',
            email=email,
            phone_number='19527559812',
            city='Calamity',
            membership=114514
        )
    else:
        print(f"App User '{username}' already exists.")

if __name__ == "__main__":
    create_ginga_superuser()
