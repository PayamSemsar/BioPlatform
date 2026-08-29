<?php
$pageTitle = 'محصولات';
ob_start();

$userId = Session::userId();
?>

<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-[#222] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-amber-400">مدیریت محصولات</h1>
            <div class="flex items-center gap-4">
                <a href="/dashboard" class="text-gray-400 hover:text-amber-400 transition">بازگشت به داشبورد</a>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
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

        <!-- Add Button -->
        <div class="mb-6">
            <a href="/dashboard/products/add" class="bg-amber-400 hover:bg-amber-500 text-black font-semibold px-6 py-3 rounded-lg inline-flex items-center gap-2 transition">
                <span>+</span>
                <span>افزودن محصول جدید</span>
            </a>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="bg-[#222] rounded-xl p-8 text-center">
                <p class="text-gray-400 mb-4">هنوز محصولی اضافه نکرده‌اید.</p>
                <a href="/dashboard/products/add" class="text-amber-400 hover:text-amber-300">اولین محصول خود را اضافه کنید</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="bg-[#222] rounded-xl overflow-hidden">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-800 relative">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['title']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-600">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <?php if ($product['is_active']): ?>
                                    <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">فعال</span>
                                <?php else: ?>
                                    <span class="bg-gray-500 text-white text-xs px-2 py-1 rounded">غیرفعال</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-2"><?= e($product['title']) ?></h3>
                            <p class="text-gray-400 text-sm mb-3 line-clamp-2"><?= e($product['description']) ?></p>
                            <p class="text-amber-400 font-bold mb-4"><?= number_format($product['price']) ?> تومان</p>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="/dashboard/products/edit/<?= $product['id'] ?>" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white text-center py-2 rounded-lg transition text-sm">
                                    ویرایش
                                </a>
                                <form method="POST" action="/dashboard/products/delete/<?= $product['id'] ?>" class="flex-1" onsubmit="return confirm('آیا مطمئن هستید؟')">
                                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                    <button type="submit" class="w-full bg-red-900/50 hover:bg-red-800 text-red-300 py-2 rounded-lg transition text-sm">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
