<?php
$pageTitle = 'ورود';
ob_start();
?>

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-amber-400">لینک در بایو</h1>
            <p class="text-gray-400 mt-2">ساخت صفحه فروش حرفه‌ای</p>
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

        <!-- Form -->
        <div class="bg-[#222] rounded-xl p-8 shadow-xl">
            <h2 class="text-xl font-semibold mb-6">ورود به حساب</h2>

            <form method="POST" action="/login" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

                <div>
                    <label class="block text-sm text-gray-400 mb-2">ایمیل</label>
                    <input 
                        type="email" 
                        name="email" 
                        required
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                        placeholder="example@email.com"
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">رمز عبور</label>
                    <input 
                        type="password" 
                        name="password" 
                        required
                        class="w-full bg-[#0d0d0d] border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-amber-400 transition"
                        placeholder="••••••••"
                    >
                </div>

                <button 
                    type="submit"
                    class="w-full bg-amber-400 hover:bg-amber-500 text-black font-semibold py-3 rounded-lg transition"
                >
                    ورود
                </button>
            </form>

            <p class="text-center text-gray-400 mt-6">
                حساب ندارید؟
                <a href="/register" class="text-amber-400 hover:text-amber-300">ثبت‌نام کنید</a>
            </p>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); require __DIR__ . '/layouts/main.php'; ?>
