<?php
$pageTitle = $action === 'create' ? 'افزودن محصول' : 'ویرایش محصول';
ob_start();

$userId = Session::userId();
?>

<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-[#222] border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-amber-400"><?= e($pageTitle) ?></h1>
            <a href="/dashboard/products" class="text-gray-400 hover:text-amber-400 transition">بازگشت</a>
        </div>
    </header>

    <!-- Content -->
    <main class="max-w-2xl mx-auto px-4 py-8">
        <!-- Flash Messages -->
        <?php if ($error = Session::flash('error')): ?>
            <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-3 rounded-lg mb-6">
                <?= nl2br(e($error)) ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <div class="bg-[#222] rounded-xl p-8">
            <?php if ($action === 'create'): ?>
                <form method="POST" action="/dashboard/products/create" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <?php else: ?>
                <form method="POST" action="/dashboard/products/update/<?= $product['id'] ?>" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <?php endif; ?>

                <!-- Title -->
                <div>
                    <label class="block text-sm text-gray-400 mb-2">عنوان محصول *</label>
                    <input 
                        type="text" 
                        name="title" 
                        required
                        value="<?= e($product['title'] ?? '') ?>"
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                        placeholder="مثال: کفش ورزشی نایک"
                    >
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm text-gray-400 mb-2">توضیحات</label>
                    <textarea 
                        name="description" 
                        rows="4"
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition resize-none"
                        placeholder="توضیحات محصول را وارد کنید..."
                    ><?= e($product['description'] ?? '') ?></textarea>
                </div>

                <!-- Price -->
                <div>
                    <label class="block text-sm text-gray-400 mb-2">قیمت (تومان) *</label>
                    <input 
                        type="number" 
                        name="price" 
                        required
                        min="1"
                        value="<?= $product['price'] ?? '' ?>"
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                        placeholder="مثال: 500000"
                    >
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-sm text-gray-400 mb-2">تصویر محصول</label>
                    <?php if ($action === 'update' && $product['image_url']): ?>
                        <div class="mb-3">
                            <img src="<?= e($product['image_url']) ?>" alt="تصویر فعلی" class="w-32 h-32 object-cover rounded-lg">
                        </div>
                    <?php endif; ?>
                    <input 
                        type="file" 
                        name="image" 
                        accept="image/*"
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                    >
                    <p class="text-xs text-gray-500 mt-1">فرمت‌های مجاز: JPG, PNG, WebP - حداکثر حجم: 2MB</p>
                </div>

                <!-- Active Status (only for edit) -->
                <?php if ($action === 'update'): ?>
                    <div class="flex items-center gap-3">
                        <input 
                            type="checkbox" 
                            name="is_active" 
                            id="is_active"
                            value="1"
                            <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>
                            class="w-5 h-5 rounded bg-[#0d0d0d] border-gray-700 text-amber-400 focus:ring-amber-400"
                        >
                        <label for="is_active" class="text-gray-300">محصول فعال باشد</label>
                    </div>
                <?php endif; ?>

                <!-- Submit -->
                <button 
                    type="submit"
                    class="w-full bg-amber-400 hover:bg-amber-500 text-black font-semibold py-3 rounded-lg transition"
                >
                    <?= $action === 'create' ? 'افزودن محصول' : 'به‌روزرسانی محصول' ?>
                </button>
            </form>
        </div>
    </main>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
