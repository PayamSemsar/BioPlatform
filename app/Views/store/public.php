<?php
$pageTitle = e($storeOwner['username']) . ' - فروشگاه';
ob_start();
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Vazirmatn', sans-serif; }
    </style>
</head>
<body class="bg-[#0d0d0d] text-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-[#222] border-b border-gray-800 py-8">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <h1 class="text-3xl font-bold text-amber-400 mb-2"><?= e($storeOwner['username']) ?></h1>
            <p class="text-gray-400">فروشگاه آنلاین</p>
        </div>
    </header>

    <!-- Products Grid -->
    <main class="max-w-6xl mx-auto px-4 py-8">
        <?php if (empty($products)): ?>
            <div class="text-center py-16">
                <p class="text-gray-400 text-lg">هنوز محصولی برای نمایش وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): ?>
                    <div id="product-<?= $product['id'] ?>" class="bg-[#222] rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-800 relative overflow-hidden">
                            <?php if ($product['image_url']): ?>
                                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['title']) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-600">
                                    <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="p-5">
                            <h3 class="font-semibold text-xl mb-2"><?= e($product['title']) ?></h3>
                            <p class="text-gray-400 text-sm mb-4 line-clamp-2"><?= e($product['description']) ?></p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-amber-400 font-bold text-lg"><?= number_format($product['price']) ?> تومان</span>
                                
                                <!-- Buy Button -->
                                <button 
                                    onclick="document.getElementById('modal-<?= $product['id'] ?>').classList.remove('hidden')"
                                    class="bg-amber-400 hover:bg-amber-500 text-black font-semibold px-6 py-2 rounded-lg transition"
                                >
                                    خرید
                                </button>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div id="modal-<?= $product['id'] ?>" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
                            <div class="bg-[#222] rounded-xl p-6 w-full max-w-md relative">
                                <button 
                                    onclick="document.getElementById('modal-<?= $product['id'] ?>').classList.add('hidden')"
                                    class="absolute top-4 left-4 text-gray-400 hover:text-white"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>

                                <h3 class="text-xl font-bold mb-4"><?= e($product['title']) ?></h3>
                                <p class="text-amber-400 font-bold mb-6"><?= number_format($product['price']) ?> تومان</p>

                                <form method="POST" action="/store/checkout" class="space-y-4">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    
                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">نام و نام خانوادگی</label>
                                        <input 
                                            type="text" 
                                            name="customer_name" 
                                            required
                                            class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                                            placeholder="علی محمدی"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm text-gray-400 mb-2">شماره موبایل</label>
                                        <input 
                                            type="tel" 
                                            name="customer_phone" 
                                            required
                                            pattern="09[0-9]{9}"
                                            class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                                            placeholder="09123456789"
                                            dir="ltr"
                                        >
                                    </div>

                                    <button 
                                        type="submit"
                                        class="w-full bg-amber-400 hover:bg-amber-500 text-black font-semibold py-3 rounded-lg transition"
                                    >
                                        پرداخت آنلاین
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-[#222] border-t border-gray-800 py-6 mt-12">
        <div class="max-w-6xl mx-auto px-4 text-center text-gray-400 text-sm">
            <p>قدرت گرفته از پلتفرم لینک در بایو</p>
        </div>
    </footer>
</body>
</html>
