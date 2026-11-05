<?php
/**
 * Sport Shop – Admin Middleware
 *
 * Ensures the authenticated user has admin role.
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

class AdminMiddleware
{
    public function handle(\App\Core\Request $request): bool
    {
        if (Auth::isAdmin()) {
            return true;
        }

        if ($request->isAjax() || $request->isJson()) {
            Response::error('Bạn không có quyền truy cập.', 'FORBIDDEN', 403);
            return false;
        }

        http_response_code(403);
        echo '<h1>403 - Không có quyền</h1><p>Bạn không có quyền truy cập trang này.</p>';
        exit;
    }

    public function __invoke(\App\Core\Request $request): bool
    {
        return $this->handle($request);
    }
}
