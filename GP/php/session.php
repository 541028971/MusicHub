<?php
session_start();

class Session
{
    public static function setSessions($sessionName, $sessionValue)
    {
        $_SESSION[$sessionName] = [];
        $_SESSION[$sessionName]['value'] = $sessionValue;
    }

    public static function getSessions($sessionName)
    {
        if (isset($_SESSION[$sessionName])) {
            return $_SESSION[$sessionName]['value'];
        }
        return false;
    }
}