<?php
/**
 * Controller صفحه عمومی فروشگاه
 */

declare(strict_types=1);

class StoreController
{
    private User $userModel;
    private Product $productModel;
    private Order $orderModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    /**
     * نمایش صفحه عمومی فروشگاه
     */
    public function show(string $slug): void
    {
        // یافتن کاربر بر اساس slug
        $storeOwner = $this->userModel->findBySlug($slug);

        if (!$storeOwner) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        // دریافت محصولات فعال
        $products = $this->productModel->findActiveByUser($storeOwner['id']);

        require __DIR__ . '/../Views/store/public.php';
    }

    /**
     * شروع فرآیند خرید
     */
    public function checkout(int $productId): void
    {
        $product = $this->productModel->findById($productId);

        if (!$product || !$product['is_active']) {
            Session::flash('error', 'محصول یافت نشد یا غیرفعال است.');
            Router::redirect('/');
        }

        // دریافت اطلاعات مشتری از فرم
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');

        // اعتبارسنجی
        $errors = [];
        if (empty($customerName) || strlen($customerName) < 2) {
            $errors[] = 'نام و نام خانوادگی معتبر وارد کنید.';
        }
        if (empty($customerPhone) || !preg_match('/^09[0-9]{9}$/', $customerPhone)) {
            $errors[] = 'شماره موبایل معتبر وارد کنید (مثال: 09123456789).';
        }

        if (!empty($errors)) {
            Session::flash('error', implode("\n", $errors));
            Router::redirect("/store/{$this->userModel->findBySlug($_GET['slug'] ?? '')['store_slug']}#product-{$productId}");
            return;
        }

        // ایجاد سفارش اولیه
        $orderId = $this->orderModel->create(
            $product['user_id'],
            $productId,
            $customerName,
            $customerPhone,
            $product['price']
        );

        $order = $this->orderModel->findById($orderId);

        // درخواست به درگاه زیبال
        $zibalConfig = require __DIR__ . '/../../config/zibal.php';

        $data = [
            'merchantId' => $zibalConfig['merchant_id'],
            'amount' => $product['price'],
            'callbackUrl' => $zibalConfig['callback_url'] . '?order_id=' . $orderId,
            'mobile' => $customerPhone,
        ];

        // ارسال درخواست به API زیبال
        $ch = curl_init($zibalConfig['api_url'] . '/request');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode !== 200 || ($result['result'] ?? 0) !== 1) {
            Session::flash('error', 'خطا در ارتباط با درگاه پرداخت.');
            Router::redirect("/store/{$_GET['slug']}#product-{$productId}");
        }

        // ذخیره track_id
        $trackId = $result['trackId'];
        $this->orderModel->updateTrackId($orderId, $trackId);

        // ریدایرکت به درگاه
        header('Location: https://gateway.zibal.ir/start/' . $trackId);
        exit;
    }
}
