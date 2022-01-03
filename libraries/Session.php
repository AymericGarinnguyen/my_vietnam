<?php

namespace Libraries;

class Session
{
    /**
     * Session start
     * 
     * @return void
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Init a session
     * 
     * @param string $sessionName
     * @param array $params
     * @return void
     */
    public static function init(string $sessionName, array $params): void
    {
        $_SESSION[$sessionName] = $params;
    }

    /**
     * Check if user is connected
     * 
     * @return boolean
     */
    public static function isConnected(): bool
    {
        return isset($_SESSION['login']) ? true : false;
    }

    /**
     * Check if user is admin
     * 
     * @return boolean
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['login']) &&  $_SESSION['login']['is_admin'] === "1" ? true : false;
    }

    /**
     * Destroy a specific session
     * 
     * @param string $value
     * @return void
     */
    public static function destroyByValue(string $value): void
    {
        $_SESSION[$value] = [];
        unset($_SESSION[$value]);
    }

    /**
     * Destroy all sessions
     * 
     * @return void
     */
    public static function destroyAll(): void
    {
        foreach ($_SESSION as $table => $value) {
            $_SESSION[$table] = [];
            unset($_SESSION[$table]);
        }
        session_destroy();
    }

    /**
     * Set an error in $_SESSION['error]
     * 
     * @param string $error
     * @return void
     */
    public static function setError(string $name, array $error): void
    {
        $_SESSION['error-' . $name] = $error;
    }

    /**
     * Return an error message if $_SESSION['error'] is not empty
     * 
     * @return string||null
     */
    public static function getError(string $name): ?string
    {
        return isset($_SESSION['error-' . $name]) ? $_SESSION['error-404']['error'] : null;
    }

    /**
     * Check if isset session and value, return value or empty string
     * 
     * @param string $table
     * @param string $field
     * @param string $else
     * @return string
     */
    public static function issetValue(string $table, string $field, $else): string
    {
        return isset($_SESSION[$table][$field]) ? $_SESSION[$table][$field] : $else;
    }
}
