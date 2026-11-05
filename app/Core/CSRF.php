<?php
/**
 * Sport Shop – CSRF Protection
 *
 * Generate, retrieve and verify CSRF tokens stored in the session.
 */

namespace App\Core;

class CSRF
{
    /** Session key for the CSRF token. */
    private const SESSION_KEY = '_csrf_token';

    /**
     * Generate a new CSRF token and store it in the session.
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY] = $token;
        return $token;
    }

    /**
     * Get the current CSRF token (generates one if none exists).
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            return self::generateToken();
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Verify a submitted token against the session token.
     */
    public static function verifyToken(string $token): bool
    {
        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }
        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Return an HTML hidden input field containing the CSRF token.
     */
    public static function getFormField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Return a meta tag (useful for AJAX requests).
     */
    public static function getMetaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
