<?php
/**
 * Controller داشبورد (مدیریت فروشگاه)
 */

declare(strict_types=1);

class DashboardController
{
    private Product $productModel;
    private Order $orderModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->orderModel = new Order();
    }

    /**
     * نمایش داشبورد اصلی
     */
    public function index(): void
    {
        $this->requireAuth();

        $userId = Session::userId();
        $stats = $this->orderModel->getDashboardStats($userId);
        $productsCount = $this->productModel->countByUser($userId);
        $recentOrders = $this->orderModel->findByUser($userId, 10);

        require __DIR__ . '/../Views/dashboard/index.php';
    }

    /**
     * مدیریت محصولات - لیست
     */
    public function products(): void
    {
        $this->requireAuth();

        $userId = Session::userId();
        $products = $this->productModel->findByUser($userId);

        $csrfToken = Security::generateCsrfToken();
        require __DIR__ . '/../Views/dashboard/products.php';
    }

    /**
     * نمایش فرم افزودن محصول
     */
    public function addProduct(): void
    {
        $this->requireAuth();

        $csrfToken = Security::generateCsrfToken();
        $action = 'create';
        $product = null;

        require __DIR__ . '/../Views/dashboard/product-form.php';
    }

    /**
     * ایجاد محصول جدید
     */
    public function createProduct(): void
    {
        $this->requireAuth();

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'خطای امنیتی.');
            Router::redirect('/dashboard/products');
        }

        $userId = Session::userId();
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (int) ($_POST['price'] ?? 0);

        // اعتبارسنجی
        $errors = [];
        if (empty($title)) {
            $errors[] = 'عنوان محصول الزامی است.';
        }
        if ($price <= 0) {
            $errors[] = 'قیمت باید بیشتر از صفر باشد.';
        }

        // آپلود تصویر
        $imageUrl = null;
        if (!empty($_FILES['image']['name'])) {
            $validation = Security::validateUpload($_FILES['image']);
            if (!$validation['valid']) {
                $errors[] = $validation['error'];
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = Security::generateFileName($_FILES['image']['name']);
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    $errors[] = 'خطا در آپلود تصویر.';
                } else {
                    $imageUrl = '/uploads/' . $fileName;
                }
            }
        }

        if (!empty($errors)) {
            Session::flash('error', implode("\n", $errors));
            Router::redirect('/dashboard/products/add');
        }

        try {
            $this->productModel->create($userId, $title, $description, $price, $imageUrl);
            Session::flash('success', 'محصول با موفقیت افزوده شد.');
            Router::redirect('/dashboard/products');
        } catch (Exception $e) {
            Session::flash('error', 'خطا در افزودن محصول.');
            Router::redirect('/dashboard/products/add');
        }
    }

    /**
     * نمایش فرم ویرایش محصول
     */
    public function editProduct(int $id): void
    {
        $this->requireAuth();

        $userId = Session::userId();
        $product = $this->productModel->findById($id);

        if (!$product || $product['user_id'] !== $userId) {
            Session::flash('error', 'محصول یافت نشد.');
            Router::redirect('/dashboard/products');
        }

        $csrfToken = Security::generateCsrfToken();
        $action = 'update';
        
        require __DIR__ . '/../Views/dashboard/product-form.php';
    }

    /**
     * به‌روزرسانی محصول
     */
    public function updateProduct(int $id): void
    {
        $this->requireAuth();

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'خطای امنیتی.');
            Router::redirect('/dashboard/products');
        }

        $userId = Session::userId();
        $product = $this->productModel->findById($id);

        if (!$product || $product['user_id'] !== $userId) {
            Session::flash('error', 'محصول یافت نشد.');
            Router::redirect('/dashboard/products');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (int) ($_POST['price'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // اعتبارسنجی
        $errors = [];
        if (empty($title)) {
            $errors[] = 'عنوان محصول الزامی است.';
        }
        if ($price <= 0) {
            $errors[] = 'قیمت باید بیشتر از صفر باشد.';
        }

        // آپلود تصویر جدید
        $imageUrl = $product['image_url'];
        if (!empty($_FILES['image']['name'])) {
            $validation = Security::validateUpload($_FILES['image']);
            if (!$validation['valid']) {
                $errors[] = $validation['error'];
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = Security::generateFileName($_FILES['image']['name']);
                
                // حذف تصویر قدیمی
                if ($product['image_url']) {
                    @unlink(__DIR__ . '/../../public' . $product['image_url']);
                }
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    $errors[] = 'خطا در آپلود تصویر.';
                } else {
                    $imageUrl = '/uploads/' . $fileName;
                }
            }
        }

        if (!empty($errors)) {
            Session::flash('error', implode("\n", $errors));
            Router::redirect("/dashboard/products/edit/{$id}");
        }

        try {
            $this->productModel->update($id, $userId, [
                'title' => $title,
                'description' => $description,
                'price' => $price,
                'image_url' => $imageUrl,
                'is_active' => $isActive,
            ]);
            Session::flash('success', 'محصول به‌روزرسانی شد.');
            Router::redirect('/dashboard/products');
        } catch (Exception $e) {
            Session::flash('error', 'خطا در به‌روزرسانی محصول.');
            Router::redirect("/dashboard/products/edit/{$id}");
        }
    }

    /**
     * حذف محصول
     */
    public function deleteProduct(int $id): void
    {
        $this->requireAuth();

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'خطای امنیتی.');
            Router::redirect('/dashboard/products');
        }

        $userId = Session::userId();
        $product = $this->productModel->findById($id);

        if (!$product || $product['user_id'] !== $userId) {
            Session::flash('error', 'محصول یافت نشد.');
            Router::redirect('/dashboard/products');
        }

        // حذف تصویر
        if ($product['image_url']) {
            @unlink(__DIR__ . '/../../public' . $product['image_url']);
        }

        $this->productModel->delete($id, $userId);
        Session::flash('success', 'محصول حذف شد.');
        Router::redirect('/dashboard/products');
    }

    /**
     * مشاهده سفارش‌ها
     */
    public function orders(): void
    {
        $this->requireAuth();

        $userId = Session::userId();
        $orders = $this->orderModel->findByUser($userId);

        require __DIR__ . '/../Views/dashboard/orders.php';
    }

    /**
     * بررسی احراز هویت
     */
    private function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Router::redirect('/login');
        }
    }
}
