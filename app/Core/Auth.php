<?php
/**
 * Sport Shop – Authentication Helper
 *
 * Manages session-based authentication (login, logout, role checks).
 */

namespace App\Core;

class Auth
{
    /** Session key used to store the authenticated user. */
    private const SESSION_KEY = '_auth_user';

    /**
     * Log a user in. Accepts the user row from the database.
     */
    public static function login(array $user): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user data in session (excluding password)
        unset($user['password']);
        $_SESSION[self::SESSION_KEY] = $user;
    }

    /**
     * Log the current user out.
     */
    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);

        // Destroy the entire session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();

            // Delete the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            // Restart a fresh session so flash messages etc. still work
            session_start();
            session_regenerate_id(true);
        }
    }

    /**
     * Check if a user is authenticated.
     */
    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]) && !empty($_SESSION[self::SESSION_KEY]['id']);
    }

    /**
     * Get the current authenticated user array, or null.
     */
    public static function user(): ?array
    {
        return self::check() ? $_SESSION[self::SESSION_KEY] : null;
    }

    /**
     * Get the current user's ID, or null.
     */
    public static function id(): ?int
    {
        return self::check() ? (int)$_SESSION[self::SESSION_KEY]['id'] : null;
    }

    /**
     * Check if the current user has the admin role.
     */
    public static function isAdmin(): bool
    {
        return self::check() && ($_SESSION[self::SESSION_KEY]['role'] ?? '') === 'admin';
    }

    /**
     * Get a specific attribute of the current user.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::check()) {
            return $default;
        }
        return $_SESSION[self::SESSION_KEY][$key] ?? $default;
    }

    /**
     * Update a stored attribute of the authenticated user without re-login.
     */
    public static function set(string $key, mixed $value): void
    {
        if (self::check()) {
            $_SESSION[self::SESSION_KEY][$key] = $value;
        }
    }
}
