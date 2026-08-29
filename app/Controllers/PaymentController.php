<?php
/**
 * Controller پرداخت (callback و verify)
 */

declare(strict_types=1);

class PaymentController
{
    private Order $orderModel;
    private Product $productModel;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->productModel = new Product();
    }

    /**
     * callback درگاه زیبال
     */
    public function callback(): void
    {
        $orderId = (int) ($_GET['order_id'] ?? 0);
        $trackId = $_GET['trackId'] ?? '';

        if (!$orderId || !$trackId) {
            Session::flash('error', 'اطلاعات پرداخت ناقص است.');
            Router::redirect('/');
        }

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            Session::flash('error', 'سفارش یافت نشد.');
            Router::redirect('/');
        }

        // بررسی track_id
        if ($order['track_id'] !== $trackId) {
            Session::flash('error', 'اطلاعات پرداخت نامعتبر است.');
            Router::redirect('/');
        }

        // بررسی وضعیت سفارش
        if ($order['status'] === 'paid') {
            Session::flash('success', 'پرداخت قبلاً با موفقیت انجام شده است.');
            $this->showSuccess($order);
            return;
        }

        if ($order['status'] === 'failed') {
            Session::flash('error', 'این پرداخت قبلاً ناموفق بوده است.');
            $this->showFailure($order);
            return;
        }

        // تأیید پرداخت با زیبال
        $zibalConfig = require __DIR__ . '/../../config/zibal.php';

        $data = [
            'merchantId' => $zibalConfig['merchant_id'],
            'trackId' => $trackId,
        ];

        $ch = curl_init($zibalConfig['api_url'] . '/verify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        // بررسی نتیجه
        if ($httpCode === 200 && ($result['result'] ?? 0) === 1 && ($result['status'] ?? 0) === 1) {
            // پرداخت موفق
            $this->orderModel->updateStatus($orderId, 'paid');
            Session::flash('success', 'پرداخت با موفقیت انجام شد.');
            $this->showSuccess($order);
        } else {
            // پرداخت ناموفق
            $this->orderModel->updateStatus($orderId, 'failed');
            Session::flash('error', 'پرداخت ناموفق بود.');
            $this->showFailure($order);
        }
    }

    /**
     * نمایش صفحه موفقیت
     */
    private function showSuccess(array $order): void
    {
        $product = $this->productModel->findById($order['product_id']);
        $message = Session::flash('success');
        require __DIR__ . '/../Views/payment/success.php';
    }

    /**
     * نمایش صفحه شکست
     */
    private function showFailure(array $order): void
    {
        $product = $this->productModel->findById($order['product_id']);
        $message = Session::flash('error');
        require __DIR__ . '/../Views/payment/failure.php';
    }
}
