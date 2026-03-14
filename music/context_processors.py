from .models import User as CustomUser

def player_context(request):
    """
    Injects the last played song into the template context for player recovery.
    """
    context = {'last_played_song': None}
    
    if request.user.is_authenticated:
        try:
            custom_user = CustomUser.objects.get(username=request.user.username)
            if custom_user.last_played_song:
                context['last_played_song'] = custom_user.last_played_song
        except CustomUser.DoesNotExist:
            pass
            
    return context
