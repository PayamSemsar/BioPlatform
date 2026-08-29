<?php
$pageTitle = 'سفارش‌ها';
ob_start();

$userId = Session::userId();
?>

<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-[#222] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-amber-400">سفارش‌ها</h1>
            <a href="/dashboard" class="text-gray-400 hover:text-amber-400 transition">بازگشت به داشبورد</a>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if (empty($orders)): ?>
            <div class="bg-[#222] rounded-xl p-8 text-center">
                <p class="text-gray-400">هنوز سفارشی ثبت نشده است.</p>
            </div>
        <?php else: ?>
            <div class="bg-[#222] rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right text-gray-400 text-sm border-b border-gray-700">
                                <th class="p-4">شماره سفارش</th>
                                <th class="p-4">محصول</th>
                                <th class="p-4">مشتری</th>
                                <th class="p-4">تلفن</th>
                                <th class="p-4">مبلغ</th>
                                <th class="p-4">وضعیت</th>
                                <th class="p-4">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr class="border-b border-gray-800 hover:bg-[#2a2a2a] transition">
                                    <td class="p-4 text-gray-300">#<?= $order['id'] ?></td>
                                    <td class="p-4"><?= e($order['product_title']) ?></td>
                                    <td class="p-4"><?= e($order['customer_name']) ?></td>
                                    <td class="p-4" dir="ltr"><?= e($order['customer_phone']) ?></td>
                                    <td class="p-4 text-amber-400"><?= number_format($order['amount']) ?> تومان</td>
                                    <td class="p-4">
                                        <?php if ($order['status'] === 'paid'): ?>
                                            <span class="bg-green-900/50 text-green-400 px-3 py-1 rounded-full text-sm">پرداخت شده</span>
                                        <?php elseif ($order['status'] === 'pending'): ?>
                                            <span class="bg-yellow-900/50 text-yellow-400 px-3 py-1 rounded-full text-sm">در انتظار</span>
                                        <?php else: ?>
                                            <span class="bg-red-900/50 text-red-400 px-3 py-1 rounded-full text-sm">ناموفق</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-400"><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
