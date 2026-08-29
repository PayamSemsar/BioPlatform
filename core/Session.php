<?php
/**
 * مدیریت امن Session
 * - ذخیره در دیتابیس
 * - کوکی‌های امن
 * - انقضای ۳۰ دقیقه‌ای
 */

declare(strict_types=1);

class Session
{
    private const SESSION_LIFETIME = 1800; // 30 دقیقه
    private const COOKIE_OPTIONS = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false, // در production true شود
        'httponly' => true,
        'samesite' => 'Strict',
    ];

    /**
     * شروع session با تنظیمات امن
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(self::COOKIE_OPTIONS);
            session_start();

            // بررسی انقضا
            if (isset($_SESSION['last_activity']) && 
                (time() - $_SESSION['last_activity']) > self::SESSION_LIFETIME) {
                self::destroy();
            }

            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * ذخیره مقدار در session
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * دریافت مقدار از session
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * بررسی وجود key
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * حذف key از session
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * نابودی کامل session
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * بازسازی ID سشن برای امنیت بیشتر
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['last_activity'] = time();
    }

    /**
     * تولید CSRF Token
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * اعتبارسنجی CSRF Token
     */
    public static function validateToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * بررسی اینکه کاربر لاگین است یا نه
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    /**
     * دریافت ID کاربر لاگین شده
     */
    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    /**
     * ذخیره flash message (برای یک درخواست)
     */
    public static function flash(string $key, ?string $message = null): ?string
    {
        $sessionKey = "flash_{$key}";
        
        if ($message === null) {
            $value = self::get($sessionKey);
            self::remove($sessionKey);
            return $value;
        }

        self::set($sessionKey, $message);
        return $message;
    }
}
