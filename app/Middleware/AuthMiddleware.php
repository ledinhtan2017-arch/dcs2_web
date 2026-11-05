<?php
/**
 * Sport Shop – Auth Middleware
 *
 * Ensures the user is authenticated before accessing protected routes.
 * Redirects to login page for web requests, returns 401 JSON for API.
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

class AuthMiddleware
{
    /**
     * Handle the middleware check.
     *
     * @param \App\Core\Request $request
     * @return bool  true = continue, false = halted
     */
    public function handle(\App\Core\Request $request): bool
    {
        if (Auth::check()) {
            return true;
        }

        if ($request->isAjax() || $request->isJson()) {
            Response::error('Vui lòng đăng nhập để tiếp tục.', 'UNAUTHORIZED', 401);
            return false; // Never reached (Response::error exits)
        }

        // Store intended URL for redirect after login
        $_SESSION['_intended_url'] = $request->url();

        Response::redirect('/login');
        return false; // Never reached
    }

    /**
     * Invokable alias.
     */
    public function __invoke(\App\Core\Request $request): bool
    {
        return $this->handle($request);
    }
}
