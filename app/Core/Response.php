<?php
/**
 * Sport Shop – HTTP Response Helper
 *
 * Static methods for sending JSON, HTML, redirects and standardised
 * API success / error responses.
 */

namespace App\Core;

class Response
{
    /**
     * Send a JSON response.
     */
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to another URL.
     */
    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Send raw HTML content.
     */
    public static function html(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
        exit;
    }

    /**
     * Send a standardised API error response.
     *
     * Format: {"success": false, "error": {"code": "...", "message": "..."}}
     */
    public static function error(string $message, string $code = 'ERROR', int $status = 400): void
    {
        self::json([
            'success' => false,
            'error'   => [
                'code'    => $code,
                'message' => $message,
            ],
        ], $status);
    }

    /**
     * Send a standardised API success response.
     *
     * Format: {"success": true, "data": ..., "meta": ...}
     */
    public static function success(mixed $data = null, ?array $meta = null, int $status = 200): void
    {
        $payload = [
            'success' => true,
            'data'    => $data,
        ];

        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        self::json($payload, $status);
    }

    /**
     * Send a 204 No Content response (e.g., after DELETE).
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Set a flash message in the session.
     */
    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type]   = $message;
    }

    /**
     * Redirect back to the previous page (Referer header).
     */
    public static function back(int $status = 302): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer, $status);
    }
}
