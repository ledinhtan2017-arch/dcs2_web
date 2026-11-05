<?php
/**
 * Sport Shop – CSRF Middleware
 *
 * Verifies CSRF token on state-changing requests.
 */

namespace App\Middleware;

use App\Core\CSRF;
use App\Core\Response;

class CSRFMiddleware
{
    public function handle(\App\Core\Request $request): bool
    {
        $method = $request->method();

        // Only check for state-changing methods
        if (!in_array($method, ['POST', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $token = $request->input('_token')
              ?? $request->header('x-csrf-token')
              ?? '';

        if (CSRF::verifyToken($token)) {
            return true;
        }

        if ($request->isAjax() || $request->isJson()) {
            Response::error('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', 'CSRF_ERROR', 403);
        }

        http_response_code(403);
        echo '<h1>403 - Phiên đã hết hạn</h1><p>Vui lòng tải lại trang và thử lại.</p>';
        exit;
    }

    public function __invoke(\App\Core\Request $request): bool
    {
        return $this->handle($request);
    }
}
