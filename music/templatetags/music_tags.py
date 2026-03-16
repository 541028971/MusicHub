from django import template

register = template.Library()

@register.filter
def split_artists(value):
    """
    Splits a string of artists by the pipe symbol '|' and returns a list.
    Handles extra whitespace and empty values.
    """
    if not value:
        return []
    # Force to string and split by pipe
    artists = [a.strip() for a in str(value).split('|') if a.strip()]
    return artists
