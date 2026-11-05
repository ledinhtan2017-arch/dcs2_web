<?php
/**
 * AuthController – Login, Register, Logout pages.
 */

namespace App\Controllers;

use App\Core\{View, Auth, CSRF, Response, Validator};
use App\Models\{User, Cart};

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
            return;
        }
        View::page('account.login', ['title' => 'Đăng nhập - Sport Shop']);
    }

    public function login(): void
    {
        $request = new \App\Core\Request();
        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        $validation = Validator::validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required|min:6'],
            ['email' => 'Email', 'password' => 'Mật khẩu']
        );

        if (!$validation['valid']) {
            Response::flash('error', implode(' ', $validation['errors']));
            Response::redirect('/login');
            return;
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::flash('error', 'Email hoặc mật khẩu không chính xác.');
            Response::redirect('/login');
            return;
        }

        if ($user['status'] === 'locked') {
            Response::flash('error', 'Tài khoản đã bị khóa. Vui lòng liên hệ admin.');
            Response::redirect('/login');
            return;
        }

        Auth::login($user);

        // Merge guest cart
        Cart::mergeGuestCart((int) $user['id']);

        $intended = $_SESSION['_intended_url'] ?? '/';
        unset($_SESSION['_intended_url']);
        Response::redirect($intended);
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
            return;
        }
        View::page('account.register', ['title' => 'Đăng ký - Sport Shop']);
    }

    public function register(): void
    {
        $request = new \App\Core\Request();
        $data = [
            'name'                  => trim($request->post('name', '')),
            'email'                 => trim($request->post('email', '')),
            'phone'                 => trim($request->post('phone', '')),
            'password'              => $request->post('password', ''),
            'password_confirmation' => $request->post('password_confirmation', ''),
        ];

        $validation = Validator::validate($data, [
            'name'     => 'required|string|min:2|max:100',
            'email'    => 'required|email|max:255',
            'phone'    => 'string|max:20',
            'password' => 'required|min:6|max:72|confirmed',
        ], [
            'name' => 'Họ tên', 'email' => 'Email', 'phone' => 'Số điện thoại', 'password' => 'Mật khẩu',
        ]);

        if (!$validation['valid']) {
            Response::flash('error', implode(' ', $validation['errors']));
            Response::redirect('/register');
            return;
        }

        // Check duplicate email
        if (User::findByEmail($data['email'])) {
            Response::flash('error', 'Email đã được sử dụng.');
            Response::redirect('/register');
            return;
        }

        $userId = User::create($data);
        $user = User::findById($userId);
        Auth::login($user);
        Cart::mergeGuestCart($userId);

        Response::flash('success', 'Đăng ký thành công! Chào mừng bạn đến Sport Shop.');
        Response::redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/');
    }
}

