<?php
/**
 * Sport Shop – Route Definitions
 *
 * All routes are registered here and dispatched by the Router.
 * Variable: $router (Router instance from index.php)
 */

use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Middleware\CSRFMiddleware;

// ─── PUBLIC PAGES ────────────────────────────────────────────────────
$router->get('/',                        'ShopController@home');
$router->get('/sports/{slug}',           'ShopController@sport');
$router->get('/categories/{slug}',       'ShopController@category');
$router->get('/products/{slug}',         'ShopController@product');
$router->get('/search',                  'ShopController@search');
$router->get('/support/{slug}',          'SupportController@show');

// ─── AUTH PAGES ──────────────────────────────────────────────────────
$router->get('/login',                   'AuthController@showLogin');
$router->post('/login',                  'AuthController@login');
$router->get('/register',               'AuthController@showRegister');
$router->post('/register',              'AuthController@register');
$router->post('/logout',                'AuthController@logout');

// ─── CART PAGES ──────────────────────────────────────────────────────
$router->get('/cart',                    'CartController@show');
$router->get('/checkout',               'CheckoutController@show');
$router->post('/checkout',              'CheckoutController@process');
$router->get('/order-success/{code}',   'CheckoutController@success');
$router->post('/order-success/{code}/cancel', 'CheckoutController@cancelGuestOrder');

// ─── CUSTOMER ACCOUNT ────────────────────────────────────────────────
$router->group('account', function ($router) {
    $router->get('/',                    'AccountController@profile');
    $router->post('/update',             'AccountController@update');
    $router->get('/orders',              'AccountController@orders');
    $router->get('/orders/{code}',       'AccountController@orderDetail');
    $router->post('/orders/{code}/cancel', 'AccountController@cancelOrder');
}, [AuthMiddleware::class]);

// ─── PUBLIC API ──────────────────────────────────────────────────────
$router->group('api', function ($router) {
    // Sports & Categories
    $router->get('/sports',              'Api\ShopApiController@sports');
    $router->get('/categories',          'Api\ShopApiController@categories');

    // Products
    $router->get('/products',            'Api\ShopApiController@products');
    $router->get('/products/{id}',       'Api\ShopApiController@product');

    // Cart
    $router->get('/cart',                'Api\CartApiController@show');
    $router->post('/cart/items',         'Api\CartApiController@addItem');
    $router->patch('/cart/items/{id}',   'Api\CartApiController@updateItem');
    $router->delete('/cart/items/{id}',  'Api\CartApiController@removeItem');

    // Auth
    $router->post('/auth/register',      'Api\AuthApiController@register');
    $router->post('/auth/login',         'Api\AuthApiController@login');
    $router->post('/auth/logout',        'Api\AuthApiController@logout');
    $router->get('/auth/me',             'Api\AuthApiController@me');

    // Checkout
    $router->post('/checkout',           'Api\CheckoutApiController@process');

    // Orders (owner or guest with token)
    $router->get('/orders/{code}',       'Api\OrderApiController@show');
});

// ─── ADMIN API ───────────────────────────────────────────────────────
$router->group('api/admin', function ($router) {
    // Dashboard
    $router->get('/dashboard',           'Api\Admin\DashboardApiController@index');

    // Products
    $router->get('/products',            'Api\Admin\ProductApiController@index');
    $router->get('/products/{id}',       'Api\Admin\ProductApiController@show');
    $router->post('/products',           'Api\Admin\ProductApiController@store', [CSRFMiddleware::class]);
    $router->patch('/products/{id}',     'Api\Admin\ProductApiController@update', [CSRFMiddleware::class]);
    $router->post('/products/{id}/images', 'Api\Admin\ProductImageApiController@store', [CSRFMiddleware::class]);
    $router->post('/products/{id}/images/{imageId}/primary', 'Api\Admin\ProductImageApiController@setPrimary', [CSRFMiddleware::class]);

    // Sports
    $router->get('/sports',              'Api\Admin\SportApiController@index');
    $router->post('/sports',             'Api\Admin\SportApiController@store');
    $router->patch('/sports/{id}',       'Api\Admin\SportApiController@update');

    // Categories
    $router->get('/categories',          'Api\Admin\CategoryApiController@index');
    $router->post('/categories',         'Api\Admin\CategoryApiController@store');
    $router->patch('/categories/{id}',   'Api\Admin\CategoryApiController@update');

    // Brands
    $router->get('/brands',              'Api\Admin\BrandApiController@index');
    $router->post('/brands',             'Api\Admin\BrandApiController@store');
    $router->patch('/brands/{id}',       'Api\Admin\BrandApiController@update');

    // Orders
    $router->get('/orders',              'Api\Admin\OrderApiController@index');
    $router->get('/orders/{id}',         'Api\Admin\OrderApiController@show');
    $router->patch('/orders/{id}/status','Api\Admin\OrderApiController@updateStatus', [CSRFMiddleware::class]);

    // Users
    $router->get('/users',               'Api\Admin\UserApiController@index');
    $router->patch('/users/{id}',        'Api\Admin\UserApiController@update');
}, [AuthMiddleware::class, AdminMiddleware::class]);

// ─── ADMIN PAGES ─────────────────────────────────────────────────────
$router->group('admin', function ($router) {
    $router->get('/',                    'AdminController@dashboard');
    $router->get('/products',            'AdminController@products');
    $router->get('/products/create',     'AdminController@productCreate');
    $router->get('/products/{id}/edit',  'AdminController@productEdit');
    $router->get('/sports',              'AdminController@sports');
    $router->get('/categories',          'AdminController@categories');
    $router->get('/brands',              'AdminController@brands');
    $router->get('/orders',              'AdminController@orders');
    $router->get('/orders/{id}',         'AdminController@orderDetail');
    $router->get('/users',               'AdminController@users');
}, [AuthMiddleware::class, AdminMiddleware::class]);

// ─── ERROR PAGES ─────────────────────────────────────────────────────
$router->setNotFound(function ($request) {
    if ($request->isAjax() || $request->isJson()) {
        \App\Core\Response::error('Không tìm thấy trang yêu cầu.', 'NOT_FOUND', 404);
    } else {
        http_response_code(404);
        \App\Core\View::page('errors.404', ['title' => '404 - Không tìm thấy']);
    }
});

