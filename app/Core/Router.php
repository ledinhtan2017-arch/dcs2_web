<?php
/**
 * Sport Shop – HTTP Router
 *
 * Supports GET, POST, PATCH, DELETE with route parameters,
 * middleware stacks, route groups, CSRF auto-checking, and 404 handling.
 */

namespace App\Core;

class Router
{
    private Request $request;

    /** @var array<string, array> Registered routes keyed by HTTP method */
    private array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /** Current group state for nested groups */
    private string $groupPrefix = '';
    private array  $groupMiddleware = [];

    /** Custom 404 handler */
    private mixed $notFoundHandler = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // -------------------------------------------------------------------
    // Route registration
    // -------------------------------------------------------------------

    public function get(string $uri, callable|array|string $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, callable|array|string $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function patch(string $uri, callable|array|string $action, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    public function delete(string $uri, callable|array|string $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    /**
     * Group routes under a common prefix and/or shared middleware.
     */
    public function group(string $prefix, callable $callback, array $middleware = []): self
    {
        $previousPrefix     = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;

        $this->groupPrefix     = $previousPrefix . '/' . trim($prefix, '/');
        $this->groupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix     = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;

        return $this;
    }

    /**
     * Set a custom 404 handler.
     */
    public function setNotFound(callable $handler): self
    {
        $this->notFoundHandler = $handler;
        return $this;
    }

    // -------------------------------------------------------------------
    // Dispatch
    // -------------------------------------------------------------------

    /**
     * Match and dispatch the current request.
     */
    public function dispatch(): void
    {
        $method = $this->resolveMethod();
        $path   = $this->request->path();

        // Try to match a registered route
        if (!isset($this->routes[$method])) {
            $this->handleNotFound();
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $params = $this->matchRoute($route['pattern'], $path);

            if ($params !== false) {
                // Merge route params into the request
                $this->request->setRouteParams($params);

                // Run middleware stack
                foreach ($route['middleware'] as $mw) {
                    $result = $this->callMiddleware($mw);
                    if ($result === false) {
                        return; // Middleware halted the request
                    }
                }

                // Auto CSRF check for state-changing non-API routes
                if (in_array($method, ['POST', 'PATCH', 'DELETE'], true) && !$this->isApiRoute($route['uri'])) {
                    if (!CSRF::verifyToken($this->request->input('_token') ?? $this->request->header('X-CSRF-TOKEN') ?? '')) {
                        if ($this->request->isAjax() || $this->request->isJson()) {
                            Response::error('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 'CSRF_ERROR', 403);
                        } else {
                            http_response_code(403);
                            echo '<h1>403 - Forbidden</h1><p>Phiên làm việc đã hết hạn. Vui lòng tải lại trang.</p>';
                        }
                        return;
                    }
                }

                // Call the controller/action
                $this->callAction($route['action'], $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    // -------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------

    private function addRoute(string $method, string $uri, callable|array|string $action, array $middleware): self
    {
        $uri = $this->groupPrefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');

        // Merge group middleware (group first, then route-specific)
        $middleware = array_merge($this->groupMiddleware, $middleware);

        // Build regex pattern from URI
        $pattern = $this->buildPattern($uri);

        $this->routes[$method][] = [
            'uri'        => $uri,
            'pattern'    => $pattern,
            'action'     => $action,
            'middleware'  => $middleware,
        ];

        return $this;
    }

    /**
     * Convert a URI like /products/{slug} into a regex pattern.
     */
    private function buildPattern(string $uri): string
    {
        // Escape forward slashes
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z_]+)\}/',
            fn($m) => '(?P<' . $m[1] . '>[^/]+)',
            $uri
        );

        return '#^' . $pattern . '$#u';
    }

    /**
     * Match a pattern against a path. Returns params array or false.
     *
     * @return array<string, string>|false
     */
    private function matchRoute(string $pattern, string $path): array|false
    {
        if (preg_match($pattern, $path, $matches)) {
            // Keep only named captures
            return array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
        }
        return false;
    }

    /**
     * Resolve the real HTTP method (supports _method override for forms).
     */
    private function resolveMethod(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Allow method override via _method hidden field (for HTML forms)
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? $this->request->input('_method') ?? null;
            if ($override !== null && in_array(strtoupper($override), ['PATCH', 'DELETE'], true)) {
                $method = strtoupper($override);
            }
        }

        return $method;
    }

    /**
     * Call a middleware (supports callable, [Class, method], and class string).
     */
    private function callMiddleware(mixed $mw): mixed
    {
        if (is_callable($mw)) {
            return call_user_func($mw, $this->request);
        }

        if (is_string($mw) && class_exists($mw)) {
            $instance = new $mw();
            if (method_exists($instance, 'handle')) {
                return $instance->handle($this->request);
            }
            if (is_callable($instance)) {
                return $instance($this->request);
            }
        }

        if (is_array($mw) && count($mw) === 2) {
            [$class, $method] = $mw;
            if (is_string($class)) {
                $class = new $class();
            }
            return $class->$method($this->request);
        }

        return true;
    }

    /**
     * Call the matched route action.
     */
    private function callAction(callable|array|string $action, array $params): void
    {
        // String "Controller@method"
        if (is_string($action) && str_contains($action, '@')) {
            [$className, $method] = explode('@', $action, 2);

            // Auto-resolve namespace – always prefix unless already fully qualified
            if (!str_starts_with($className, 'App\\')) {
                $className = 'App\\Controllers\\' . $className;
            }

            $controller = new $className();
            call_user_func_array([$controller, $method], $params);
            return;
        }

        // Array [Controller::class, 'method']
        if (is_array($action) && count($action) === 2) {
            [$className, $method] = $action;
            if (is_string($className)) {
                $className = new $className();
            }
            call_user_func_array([$className, $method], $params);
            return;
        }

        // Plain callable / closure
        if (is_callable($action)) {
            call_user_func_array($action, $params);
            return;
        }
    }

    /**
     * Check whether a route URI starts with /api.
     */
    private function isApiRoute(string $uri): bool
    {
        return str_starts_with($uri, '/api');
    }

    /**
     * Handle 404 - Not Found.
     */
    private function handleNotFound(): void
    {
        http_response_code(404);

        if ($this->notFoundHandler) {
            call_user_func($this->notFoundHandler, $this->request);
            return;
        }

        if ($this->request->isAjax() || $this->request->isJson()) {
            Response::error('Không tìm thấy trang yêu cầu.', 'NOT_FOUND', 404);
        } else {
            // Try to load a 404 view if it exists
            $view404 = VIEW_PATH . '/errors/404.php';
            if (file_exists($view404)) {
                include $view404;
            } else {
                echo '<h1>404 - Không tìm thấy</h1>';
                echo '<p>Trang bạn đang tìm kiếm không tồn tại.</p>';
            }
        }
    }
}
