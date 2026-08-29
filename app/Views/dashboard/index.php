<?php
$pageTitle = 'داشبورد';
ob_start();

$userId = Session::userId();
$username = Session::get('username');
$storeSlug = Session::get('store_slug');
$storeUrl = '/store/' . $storeSlug;
?>

<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-[#222] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-amber-400">داشبورد</h1>
            <div class="flex items-center gap-4">
                <a href="<?= e($storeUrl) ?>" target="_blank" class="text-gray-400 hover:text-amber-400 transition">
                    مشاهده فروشگاه
                </a>
                <span class="text-gray-400">|</span>
                <span class="text-gray-300"><?= e($username) ?></span>
                <a href="/logout" class="text-red-400 hover:text-red-300 transition">خروج</a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-[#222] rounded-xl p-6">
                <p class="text-gray-400 text-sm mb-2">مجموع فروش</p>
                <p class="text-3xl font-bold text-amber-400"><?= number_format($stats['total_sales']) ?> تومان</p>
            </div>
            <div class="bg-[#222] rounded-xl p-6">
                <p class="text-gray-400 text-sm mb-2">سفارش‌های پرداخت شده</p>
                <p class="text-3xl font-bold text-green-400"><?= $stats['paid_orders'] ?></p>
            </div>
            <div class="bg-[#222] rounded-xl p-6">
                <p class="text-gray-400 text-sm mb-2">محصولات</p>
                <p class="text-3xl font-bold text-blue-400"><?= $productsCount ?></p>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if ($error = Session::flash('error')): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?= nl2br(e($error)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success = Session::flash('success')): ?>
            <div class="bg-green-900/50 border border-green-500 text-green-200 px-4 py-3 rounded-lg mb-6">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="bg-[#222] rounded-xl p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">دسترسی سریع</h2>
            <div class="flex flex-wrap gap-4">
                <a href="/dashboard/products/add" class="bg-amber-400 hover:bg-amber-500 text-black font-semibold px-6 py-3 rounded-lg transition">
                    افزودن محصول
                </a>
                <a href="/dashboard/products" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                    مدیریت محصولات
                </a>
                <a href="/dashboard/orders" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold px-6 py-3 rounded-lg transition">
                    سفارش‌ها
                </a>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-[#222] rounded-xl p-6">
            <h2 class="text-lg font-semibold mb-4">آخرین سفارش‌ها</h2>
            <?php if (empty($recentOrders)): ?>
                <p class="text-gray-400">هنوز سفارشی ثبت نشده است.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right text-gray-400 text-sm border-b border-gray-700">
                                <th class="pb-3">محصول</th>
                                <th class="pb-3">مشتری</th>
                                <th class="pb-3">مبلغ</th>
                                <th class="pb-3">وضعیت</th>
                                <th class="pb-3">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="border-b border-gray-800">
                                    <td class="py-3"><?= e($order['product_title']) ?></td>
                                    <td class="py-3"><?= e($order['customer_name']) ?></td>
                                    <td class="py-3"><?= number_format($order['amount']) ?> تومان</td>
                                    <td class="py-3">
                                        <?php if ($order['status'] === 'paid'): ?>
                                            <span class="text-green-400">پرداخت شده</span>
                                        <?php elseif ($order['status'] === 'pending'): ?>
                                            <span class="text-yellow-400">در انتظار</span>
                                        <?php else: ?>
                                            <span class="text-red-400">ناموفق</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-gray-400"><?= date('Y/m/d', strtotime($order['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
