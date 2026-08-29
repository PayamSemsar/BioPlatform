<?php
/**
 * توابع کمکی امنیتی
 * - XSS Protection
 * - CSRF Validation
 * - Input Sanitization
 */

declare(strict_types=1);

class Security
{
    /**
     * Escape خروجی برای جلوگیری از XSS
     * @param string|null $str
     * @return string
     */
    public static function e(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * تمیز کردن ورودی‌ها
     * @param string $input
     * @return string
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * اعتبارسنجی ایمیل
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * اعتبارسنجی رمز عبور (حداقل ۸ کاراکتر)
     * @param string $password
     * @return bool
     */
    public static function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }

    /**
     * هش کردن رمز عبور با bcrypt
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * بررسی رمز عبور
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * اعتبارسنجی CSRF Token
     * @param string|null $token
     * @return bool
     */
    public static function validateCsrfToken(?string $token): bool
    {
        return Session::validateToken($token);
    }

    /**
     * تولید CSRF Token
     * @return string
     */
    public static function generateCsrfToken(): string
    {
        return Session::generateToken();
    }

    /**
     * دریافت IP کاربر
     * @return string
     */
    public static function getUserIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * دریافت User Agent
     * @return string
     */
    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * اعتبارسنجی slug (فقط حروف کوچک، اعداد و خط تیره)
     * @param string $slug
     * @return bool
     */
    public static function isValidSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }

    /**
     * تبدیل متن به slug
     * @param string $text
     * @return string
     */
    public static function toSlug(string $text): string
    {
        // حذف کاراکترهای غیر مجاز
        $text = preg_replace('/[^a-zA-Z0-9\x{0600}-\x{06FF}\s-]/u', '', $text);
        // جایگزینی فاصله با خط تیره
        $text = preg_replace('/[\s-]+/', '-', $text);
        return strtolower(trim($text, '-'));
    }

    /**
     * اعتبارسنجی فایل آپلود شده
     * @param array $file
     * @param array $allowedTypes
     * @param int $maxSize
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateUpload(
        array $file,
        array $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'],
        int $maxSize = 2097152 // 2MB
    ): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'خطا در آپلود فایل'];
        }

        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'حجم فایل بیش از حد مجاز است'];
        }

        // بررسی MIME type واقعی
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return ['valid' => false, 'error' => 'نوع فایل مجاز نیست'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * تولید نام فایل یونیک
     * @param string $originalName
     * @return string
     */
    public static function generateFileName(string $originalName): string
    {
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        return bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    }
}

// تابع کمکی کوتاه برای escape
if (!function_exists('e')) {
    function e(?string $str): string
    {
        return Security::e($str);
    }
}
