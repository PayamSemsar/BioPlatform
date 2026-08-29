<?php
/**
 * Controller احراز هویت (ورود و ثبت‌نام)
 */

declare(strict_types=1);

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * نمایش فرم ورود
     */
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            Router::redirect('/dashboard');
        }

        $csrfToken = Security::generateCsrfToken();
        require __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * پردازش ورود
     */
    public function login(): void
    {
        // بررسی CSRF
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'خطای امنیتی. لطفاً دوباره تلاش کنید.');
            Router::redirect('/login');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // اعتبارسنجی
        $errors = [];
        if (empty($email) || !Security::isValidEmail($email)) {
            $errors[] = 'ایمیل معتبر وارد کنید.';
        }
        if (empty($password)) {
            $errors[] = 'رمز عبور را وارد کنید.';
        }

        if (!empty($errors)) {
            Session::flash('error', implode("\n", $errors));
            Router::redirect('/login');
        }

        // احراز هویت
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            Session::flash('error', 'ایمیل یا رمز عبور نادرست است.');
            Router::redirect('/login');
        }

        // ذخیره session
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('store_slug', $user['store_slug']);

        Session::flash('success', 'خوش آمدید!');
        Router::redirect('/dashboard');
    }

    /**
     * نمایش فرم ثبت‌نام
     */
    public function showRegister(): void
    {
        if (Session::isLoggedIn()) {
            Router::redirect('/dashboard');
        }

        $csrfToken = Security::generateCsrfToken();
        require __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * پردازش ثبت‌نام
     */
    public function register(): void
    {
        // بررسی CSRF
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'خطای امنیتی. لطفاً دوباره تلاش کنید.');
            Router::redirect('/register');
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $storeSlug = trim($_POST['store_slug'] ?? '');

        // اعتبارسنجی
        $errors = [];

        if (empty($username) || strlen($username) < 3) {
            $errors[] = 'نام کاربری باید حداقل ۳ کاراکتر باشد.';
        }

        if (empty($email) || !Security::isValidEmail($email)) {
            $errors[] = 'ایمیل معتبر وارد کنید.';
        } elseif ($this->userModel->emailExists($email)) {
            $errors[] = 'این ایمیل قبلاً ثبت شده است.';
        }

        if (empty($password) || !Security::isValidPassword($password)) {
            $errors[] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند.';
        }

        if (empty($storeSlug) || !Security::isValidSlug($storeSlug)) {
            $errors[] = 'آدرس فروشگاه باید شامل حروف کوچک انگلیسی، اعداد و خط تیره باشد.';
        } elseif ($this->userModel->slugExists($storeSlug)) {
            $errors[] = 'این آدرس فروشگاه قبلاً گرفته شده است.';
        }

        if (!empty($errors)) {
            Session::flash('error', implode("\n", $errors));
            Router::redirect('/register');
        }

        // ایجاد کاربر
        try {
            $userId = $this->userModel->create($username, $email, $password, $storeSlug);

            // لاگین خودکار
            Session::regenerate();
            Session::set('user_id', $userId);
            Session::set('username', $username);
            Session::set('store_slug', $storeSlug);

            Session::flash('success', 'ثبت‌نام موفقیت‌آمیز بود!');
            Router::redirect('/dashboard');
        } catch (Exception $e) {
            Session::flash('error', 'خطا در ثبت‌نام. لطفاً دوباره تلاش کنید.');
            Router::redirect('/register');
        }
    }

    /**
     * خروج
     */
    public function logout(): void
    {
        Session::destroy();
        Router::redirect('/login');
    }
}
