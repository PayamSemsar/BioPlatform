<?php
/**
 * تنظیمات درگاه پرداخت زیبال
 * برای تست از sandbox استفاده می‌کنیم
 */

return [
    'merchant_id' => getenv('ZIBAL_MERCHANT_ID') ?: 'zibal', // حالت تست: zibal
    'api_url' => 'https://gateway.zibal.ir/v1',
    'callback_url' => getenv('APP_URL') ?: 'http://localhost' . '/payment/callback',
    'sandbox' => true, // در محیط production به false تغییر دهد
];
