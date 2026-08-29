<?php
/**
 * Model محصول
 */

declare(strict_types=1);

class Product
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ایجاد محصول جدید
     */
    public function create(int $userId, string $title, string $description, int $price, ?string $imageUrl = null): int
    {
        return (int) $this->db->insert('products', [
            'user_id' => $userId,
            'title' => Security::sanitize($title),
            'description' => Security::sanitize($description),
            'price' => $price,
            'image_url' => $imageUrl,
            'is_active' => 1,
        ]);
    }

    /**
     * یافتن محصول بر اساس ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM products WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * دریافت همه محصولات یک کاربر
     */
    public function findByUser(int $userId, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM products WHERE user_id = :user_id';
        $params = ['user_id' => $userId];

        if ($activeOnly) {
            $sql .= ' AND is_active = 1';
        }

        $sql .= ' ORDER BY created_at DESC';

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * دریافت محصولات فعال برای نمایش عمومی
     */
    public function findActiveByUser(int $userId): array
    {
        return $this->findByUser($userId, true);
    }

    /**
     * به‌روزرسانی محصول
     */
    public function update(int $id, int $userId, array $data): int
    {
        $allowedFields = ['title', 'description', 'price', 'image_url', 'is_active'];
        $filteredData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'price') {
                    $filteredData[$field] = (int) $data[$field];
                } elseif ($field === 'is_active') {
                    $filteredData[$field] = (int) $data[$field];
                } else {
                    $filteredData[$field] = Security::sanitize($data[$field]);
                }
            }
        }

        return $this->db->update(
            'products',
            $filteredData,
            'id = :id AND user_id = :user_id',
            ['id' => $id, 'user_id' => $userId]
        );
    }

    /**
     * حذف محصول
     */
    public function delete(int $id, int $userId): int
    {
        return $this->db->delete(
            'products',
            'id = :id AND user_id = :user_id',
            ['id' => $id, 'user_id' => $userId]
        );
    }

    /**
     * تغییر وضعیت محصول (فعال/غیرفعال)
     */
    public function toggleStatus(int $id, int $userId): int
    {
        $product = $this->findById($id);
        if (!$product || $product['user_id'] !== $userId) {
            return 0;
        }

        $newStatus = $product['is_active'] ? 0 : 1;
        return $this->update($id, $userId, ['is_active' => $newStatus]);
    }

    /**
     * شمارش محصولات یک کاربر
     */
    public function countByUser(int $userId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM products WHERE user_id = :user_id',
            ['user_id' => $userId]
        );
        return (int) ($result['count'] ?? 0);
    }
}
