<?php
namespace App\Controllers\Api;
use App\Core\{Response, Auth, Validator};
use App\Models\{User, Cart};

class AuthApiController
{
    public function register(): void
    {
        $request = new \App\Core\Request();
        $data = $request->json() ?: $request->all();

        $v = Validator::validate($data, [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:72',
        ], ['name' => 'Họ tên', 'email' => 'Email', 'password' => 'Mật khẩu']);

        if (!$v['valid']) Response::error(implode(' ', $v['errors']), 'VALIDATION_ERROR', 422);
        if (User::findByEmail($data['email'])) Response::error('Email đã được sử dụng.', 'DUPLICATE', 409);

        $userId = User::create($data);
        $user = User::findById($userId);
        Auth::login($user);
        Cart::mergeGuestCart($userId);

        Response::success(['user' => Auth::user()], null, 201);
    }

    public function login(): void
    {
        $request = new \App\Core\Request();
        $data = $request->json() ?: $request->all();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) Response::error('Vui lòng nhập email và mật khẩu.', 'VALIDATION_ERROR', 422);

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error('Email hoặc mật khẩu không chính xác.', 'AUTH_ERROR', 401);
        }
        if ($user['status'] === 'locked') {
            Response::error('Tài khoản đã bị khóa.', 'LOCKED', 403);
        }

        Auth::login($user);
        Cart::mergeGuestCart((int)$user['id']);
        Response::success(['user' => Auth::user()]);
    }

    public function logout(): void
    {
        Auth::logout();
        Response::success(null);
    }

    public function me(): void
    {
        if (!Auth::check()) Response::error('Chưa đăng nhập.', 'UNAUTHORIZED', 401);
        Response::success(Auth::user());
    }
}

