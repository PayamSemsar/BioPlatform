<?php
/**
 * Model سفارش
 */

declare(strict_types=1);

class Order
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ایجاد سفارش جدید
     */
    public function create(
        int $userId,
        int $productId,
        string $customerName,
        string $customerPhone,
        int $amount,
        ?string $trackId = null
    ): int {
        return (int) $this->db->insert('orders', [
            'user_id' => $userId,
            'product_id' => $productId,
            'customer_name' => Security::sanitize($customerName),
            'customer_phone' => Security::sanitize($customerPhone),
            'amount' => $amount,
            'track_id' => $trackId,
            'status' => 'pending',
        ]);
    }

    /**
     * یافتن سفارش بر اساس ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM orders WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * یافتن سفارش بر اساس track_id
     */
    public function findByTrackId(string $trackId): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM orders WHERE track_id = :track_id',
            ['track_id' => $trackId]
        );
    }

    /**
     * دریافت سفارش‌های یک کاربر (صاحب فروشگاه)
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            'SELECT o.*, p.title as product_title 
             FROM orders o 
             JOIN products p ON o.product_id = p.id 
             WHERE o.user_id = :user_id 
             ORDER BY o.created_at DESC 
             LIMIT :limit',
            ['user_id' => $userId, 'limit' => $limit]
        );
    }

    /**
     * به‌روزرسانی وضعیت سفارش
     */
    public function updateStatus(int $id, string $status, ?string $trackId = null): int
    {
        $data = ['status' => $status];
        if ($trackId !== null) {
            $data['track_id'] = $trackId;
        }

        return $this->db->update('orders', $data, 'id = :id', ['id' => $id]);
    }

    /**
     * به‌روزرسانی track_id
     */
    public function updateTrackId(int $id, string $trackId): int
    {
        return $this->db->update(
            'orders',
            ['track_id' => $trackId],
            'id = :id',
            ['id' => $id]
        );
    }

    /**
     * شمارش سفارش‌های یک کاربر
     */
    public function countByUser(int $userId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
        return (int) ($result['count'] ?? 0);
    }

    /**
     * دریافت مجموع فروش یک کاربر
     */
    public function getTotalSales(int $userId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) as total 
             FROM orders 
             WHERE user_id = :user_id AND status = :status',
            ['user_id' => $userId, 'status' => 'paid']
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * دریافت تعداد سفارش‌های پرداخت شده
     */
    public function countPaidOrders(int $userId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id AND status = :status',
            ['user_id' => $userId, 'status' => 'paid']
        );
        return (int) ($result['count'] ?? 0);
    }

    /**
     * دریافت آمار کلی برای داشبورد
     */
    public function getDashboardStats(int $userId): array
    {
        return [
            'total_orders' => $this->countByUser($userId),
            'paid_orders' => $this->countPaidOrders($userId),
            'total_sales' => $this->getTotalSales($userId),
        ];
    }
}
