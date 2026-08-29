<?php
$pageTitle = 'پرداخت ناموفق';
ob_start();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-[#0d0d0d] text-gray-100 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
        <!-- Error Icon -->
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-red-400 mb-4">پرداخت ناموفق بود</h1>
        
        <?php if (isset($message)): ?>
            <p class="text-gray-400 mb-6"><?= e($message) ?></p>
        <?php endif; ?>

        <!-- Order Info -->
        <div class="bg-[#222] rounded-xl p-6 mb-6">
            <div class="space-y-3 text-right">
                <div class="flex justify-between">
                    <span class="text-gray-400">محصول:</span>
                    <span class="font-semibold"><?= e($product['title']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">مبلغ:</span>
                    <span class="text-amber-400 font-bold"><?= number_format($order['amount']) ?> تومان</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">شماره سفارش:</span>
                    <span>#<?= $order['id'] ?></span>
                </div>
            </div>
        </div>

        <p class="text-gray-400 text-sm mb-6">
            می‌توانید دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.
        </p>

        <div class="flex gap-4 justify-center">
            <a 
                href="/store/<?= e($storeOwner['store_slug'] ?? '') ?>"
                class="inline-block bg-amber-400 hover:bg-amber-500 text-black font-semibold px-6 py-3 rounded-lg transition"
            >
                بازگشت به فروشگاه
            </a>
            <button 
                onclick="window.location.reload()"
                class="inline-block bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition"
            >
                تلاش مجدد
            </button>
        </div>
    </div>
</body>
</html>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
