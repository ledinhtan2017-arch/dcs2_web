<?php
/**
 * Sport Shop – HTTP Request Wrapper
 *
 * Provides a clean interface to $_GET, $_POST, $_FILES, $_SERVER and JSON body.
 */

namespace App\Core;

class Request
{
    private array $getParams;
    private array $postParams;
    private array $files;
    private array $server;
    private array $headers;
    private ?array $jsonBody = null;
    private bool  $jsonParsed = false;
    private array $routeParams = [];

    public function __construct()
    {
        $this->getParams  = $_GET;
        $this->postParams = $_POST;
        $this->files      = $_FILES;
        $this->server     = $_SERVER;
        $this->headers    = $this->parseHeaders();
    }

    // -------------------------------------------------------------------
    // Route parameters (set by Router)
    // -------------------------------------------------------------------

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    // -------------------------------------------------------------------
    // Input accessors
    // -------------------------------------------------------------------

    /**
     * Get a query string ($_GET) parameter.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getParams[$key] ?? $default;
    }

    /**
     * Get a POST parameter.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->postParams[$key] ?? $default;
    }

    /**
     * Get a value from any source: route params → POST → GET → JSON body.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key]
            ?? $this->postParams[$key]
            ?? $this->getParams[$key]
            ?? $this->json()[$key]
            ?? $default;
    }

    /**
     * Get an uploaded file by field name.
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get all input merged (GET + POST + JSON).
     */
    public function all(): array
    {
        return array_merge($this->getParams, $this->postParams, $this->json() ?? [], $this->routeParams);
    }

    // -------------------------------------------------------------------
    // JSON body
    // -------------------------------------------------------------------

    /**
     * Parse and return the JSON request body.
     */
    public function json(): array
    {
        if (!$this->jsonParsed) {
            $this->jsonParsed = true;
            $raw = file_get_contents('php://input');
            if ($raw !== false && $raw !== '') {
                $decoded = json_decode($raw, true);
                $this->jsonBody = is_array($decoded) ? $decoded : [];
            } else {
                $this->jsonBody = [];
            }
        }
        return $this->jsonBody;
    }

    // -------------------------------------------------------------------
    // Request metadata
    // -------------------------------------------------------------------

    /**
     * HTTP method (uppercase).
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Request path without query string, trimmed.
     */
    public function path(): string
    {
        $uri  = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        // Strip the base path (script dir) so routes are relative to public/
        $scriptDir = dirname($this->server['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && $scriptDir !== '\\') {
            $scriptDir = rtrim($scriptDir, '/');
            if (str_starts_with($path, $scriptDir)) {
                $path = substr($path, strlen($scriptDir));
            }
        }

        $path = '/' . trim($path, '/');

        return $path;
    }

    /**
     * Full URL.
     */
    public function url(): string
    {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $this->server['HTTP_HOST'] ?? 'localhost';
        $uri    = $this->server['REQUEST_URI'] ?? '/';
        return $scheme . '://' . $host . $uri;
    }

    /**
     * Is this an AJAX (XMLHttpRequest) request?
     */
    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * Does the request expect JSON (Accept header or Content-Type)?
     */
    public function isJson(): bool
    {
        $accept      = $this->server['HTTP_ACCEPT'] ?? '';
        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json');
    }

    /**
     * Get a specific HTTP header value.
     */
    public function header(string $name, mixed $default = null): mixed
    {
        $key = strtolower($name);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Client IP address.
     */
    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['HTTP_CLIENT_IP']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    // -------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        // Content-Type and Content-Length are not prefixed with HTTP_
        if (isset($this->server['CONTENT_TYPE'])) {
            $headers['content-type'] = $this->server['CONTENT_TYPE'];
        }
        if (isset($this->server['CONTENT_LENGTH'])) {
            $headers['content-length'] = $this->server['CONTENT_LENGTH'];
        }
        return $headers;
    }
}
