# لینک در بایو - پلتفرم فروش آنلاین

یک پلتفرم SaaS سبک و امن برای ساخت صفحه «لینک در بایو» با درگاه پرداخت زیبال مخصوص آنلاین‌شاپ‌های ایرانی.

## ویژگی‌ها

- ✅ ثبت‌نام و ورود امن با هش bcrypt
- ✅ ساخت فروشگاه با آدرس یونیک (store_slug)
- ✅ مدیریت محصولات (افزودن، ویرایش، حذف، آپلود تصویر)
- ✅ صفحه عمومی فروشگاه با طراحی دارک مود
- ✅ یکپارچه‌سازی با درگاه پرداخت زیبال
- ✅ مدیریت سفارش‌ها و آمار فروش
- ✅ امنیت کامل (SQL Injection, XSS, CSRF Protection)

## استک تکنولوژی

- **بک‌اند:** PHP 8+ (بدون فریم‌ورک)
- **دیتابیس:** MySQL با PDO
- **فرانت‌اند:** Tailwind CSS (CDN)
- **پرداخت:** زیبال (Zibal)

---

## نصب و راه‌اندازی

### ۱. پیش‌نیازها

- PHP 8.0 یا بالاتر
- MySQL 8.0 یا بالاتر
- وب‌سرور (Apache/Nginx)
- افزونه‌های PHP: `pdo_mysql`, `curl`, `json`, `fileinfo`

### ۲. کلون پروژه

```bash
git clone <repository-url> linkinbio
cd linkinbio
```

### ۳. ایجاد دیتابیس

وارد MySQL شوید و دیتابیس را بسازید:

```sql
CREATE DATABASE linkinbio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

سپس schema را ایمپورت کنید:

```bash
mysql -u root -p linkinbio < sql/schema.sql
```

### ۴. پیکربندی

فایل‌های config را بررسی کنید:

**config/database.php:**
```php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'linkinbio',
    'username' => 'root',
    'password' => '',
    // ...
];
```

**config/zibal.php:**
```php
return [
    'merchant_id' => 'zibal', // برای تست از 'zibal' استفاده کنید
    'api_url' => 'https://gateway.zibal.ir/v1',
    'callback_url' => 'http://yourdomain.com/payment/callback',
    'sandbox' => true,
];
```

### ۵. تنظیم وب‌سرور

#### Apache

فایل `.htaccess` را در پوشه `public` ایجاد کنید:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# مسدود کردن اجرای PHP در uploads
<Directory "uploads">
    php_flag engine off
</Directory>
```

یا از virtual host استفاده کنید:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/linkinbio/public
    
    <Directory /path/to/linkinbio/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/linkinbio/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # مسدود کردن اجرای PHP در uploads
    location /uploads/ {
        location ~ \.php$ {
            deny all;
        }
    }
}
```

### ۶. ایجاد ادمین پیش‌فرض (اختیاری)

می‌توانید از طریق فرم ثبت‌نام اولین کاربر را بسازید یا مستقیماً در دیتابیس:

```sql
INSERT INTO users (username, email, password_hash, store_slug, created_at) 
VALUES (
    'ادمین',
    'admin@example.com',
    '$2y$12$...' -- هش رمز عبور با bcrypt
    'my-store',
    NOW()
);
```

برای تولید هش رمز عبور از PHP استفاده کنید:

```php
<?php
echo password_hash('YourPassword123', PASSWORD_BCRYPT, ['cost' => 12]);
```

---

## ساختار پروژه

```
/project-root
├── /app
│   ├── /Controllers      # Controllerها
│   ├── /Models           # Modelها
│   └── /Views            # Viewها
├── /public               # نقطه ورود اصلی
│   ├── index.php         # Front Controller
│   ├── /assets           # فایل‌های استاتیک
│   └── /uploads          # تصاویر آپلود شده
├── /config               # فایل‌های پیکربندی
├── /core                 # کلاس‌های پایه
└── /sql                  # Schema دیتابیس
```

---

## امنیت

### SQL Injection
تمام queryها با PDO Prepared Statements اجرا می‌شوند.

### XSS Protection
تمام خروجی‌ها با `htmlspecialchars()` escape می‌شوند. از تابع کمکی `e()` استفاده کنید:

```php
<?= e($userInput) ?>
```

### CSRF Protection
همه فرم‌ها شامل token CSRF هستند که در هر session تولید می‌شود.

### Session Security
- کوکی‌های `httponly`, `secure`, `samesite=strict`
- انقضای ۳۰ دقیقه‌ای
- `session_regenerate_id(true)` هنگام ورود

### رمز عبور
- حداقل ۸ کاراکتر
- هش bcrypt با cost 12

### آپلود فایل
- اعتبارسنجی MIME type
- تغییر نام فایل به نام تصادفی
- مسدود کردن اجرای PHP در پوشه uploads

---

## درگاه پرداخت زیبال

### حالت تست (Sandbox)

در `config/zibal.php`:

```php
'merchant_id' => 'zibal', // Merchant ID تست
'sandbox' => true,
```

### حالت Production

پس از دریافت Merchant ID از زیبال:

```php
'merchant_id' => 'YOUR_MERCHANT_ID',
'sandbox' => false,
```

### جریان پرداخت

1. کاربر روی «خرید» کلیک می‌کند
2. سفارش در دیتابیس با وضعیت `pending` ثبت می‌شود
3. درخواست به `/request` زیبال ارسال می‌شود
4. کاربر به درگاه ریدایرکت می‌شود
5. پس از پرداخت، callback فراخوانی می‌شود
6. پرداخت با `/verify` تأیید و وضعیت سفارش به‌روز می‌شود

---

## محیط توسعه

برای اجرای سریع در محیط توسعه:

```bash
cd public
php -S localhost:8000
```

سپس به آدرس `http://localhost:8000` بروید.

---

## متغیرهای محیطی (اختیاری)

می‌توانید از متغیرهای محیطی استفاده کنید:

```bash
export DB_HOST=localhost
export DB_DATABASE=linkinbio
export DB_USERNAME=root
export DB_PASSWORD=secret
export ZIBAL_MERCHANT_ID=zibal
export APP_URL=http://localhost
```

---

## لایسنس

این پروژه برای استفاده شخصی و تجاری آزاد است.

---

## پشتیبانی

برای گزارش باگ یا پیشنهاد ویژگی جدید، issue ایجاد کنید.
