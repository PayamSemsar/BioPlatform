<?php
/**
 * Front Controller - نقطه ورود اصلی برنامه
 * تمام درخواست‌ها از این فایل عبور می‌کنند
 */

declare(strict_types=1);

// تنظیمات خطاگیری در توسعه (در production غیرفعال شود)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // نمایش خطاها را خاموش می‌کنیم اما لاگ می‌شوند
ini_set('log_errors', '1');

// تعریف ثابت‌ها
define('ROOT_PATH', dirname(__DIR__));
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// autoload کلاس‌های core
spl_autoload_register(function ($class) {
    $corePath = ROOT_PATH . '/core/' . $class . '.php';
    if (file_exists($corePath)) {
        require_once $corePath;
    }
});

// autoload کلاس‌های app
spl_autoload_register(function ($class) {
    $appPaths = [
        ROOT_PATH . '/app/Controllers/' . $class . '.php',
        ROOT_PATH . '/app/Models/' . $class . '.php',
    ];
    
    foreach ($appPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// شروع session با تنظیمات امن
Session::start();

// ایجاد Router و ثبت routeها
$router = new Router();
$router->setBasePath('');

// ==================== Routeها ====================

// Auth Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard Routes (نیاز به احراز هویت)
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/products', [DashboardController::class, 'products']);
$router->get('/dashboard/products/add', [DashboardController::class, 'addProduct']);
$router->post('/dashboard/products/create', [DashboardController::class, 'createProduct']);
$router->get('/dashboard/products/edit/{id}', [DashboardController::class, 'editProduct']);
$router->post('/dashboard/products/update/{id}', [DashboardController::class, 'updateProduct']);
$router->post('/dashboard/products/delete/{id}', [DashboardController::class, 'deleteProduct']);
$router->get('/dashboard/orders', [DashboardController::class, 'orders']);

// Store Public Routes
$router->get('/store/{slug}', [StoreController::class, 'show']);
$router->post('/store/checkout', [StoreController::class, 'checkout']);

// Payment Routes
$router->get('/payment/callback', [PaymentController::class, 'callback']);

// Home Redirect
$router->get('/', function () {
    if (Session::isLoggedIn()) {
        Router::redirect('/dashboard');
    } else {
        Router::redirect('/login');
    }
});

// پردازش درخواست
$router->dispatch();
