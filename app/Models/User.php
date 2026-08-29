<?php
/**
 * Model کاربر (صاحب فروشگاه)
 */

declare(strict_types=1);

class User
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ایجاد کاربر جدید
     */
    public function create(string $username, string $email, string $password, string $storeSlug): int
    {
        $passwordHash = Security::hashPassword($password);

        return (int) $this->db->insert('users', [
            'username' => Security::sanitize($username),
            'email' => Security::sanitize($email),
            'password_hash' => $passwordHash,
            'store_slug' => Security::sanitize($storeSlug),
        ]);
    }

    /**
     * یافتن کاربر بر اساس ایمیل
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );
    }

    /**
     * یافتن کاربر بر اساس ID
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, username, email, store_slug, created_at FROM users WHERE id = :id',
            ['id' => $id]
        );
    }

    /**
     * یافتن کاربر بر اساس store_slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            'SELECT id, username, store_slug FROM users WHERE store_slug = :slug',
            ['slug' => $slug]
        );
    }

    /**
     * بررسی تکراری بودن ایمیل
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return ($result['COUNT(*)'] ?? 0) > 0;
    }

    /**
     * بررسی تکراری بودن store_slug
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE store_slug = :slug';
        $params = ['slug' => $slug];

        if ($excludeId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return ($result['COUNT(*)'] ?? 0) > 0;
    }

    /**
     * به‌روزرسانی اطلاعات کاربر
     */
    public function update(int $id, array $data): int
    {
        $allowedFields = ['username', 'email', 'store_slug'];
        $filteredData = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $filteredData[$field] = Security::sanitize($data[$field]);
            }
        }

        if (isset($data['password'])) {
            $filteredData['password_hash'] = Security::hashPassword($data['password']);
        }

        return $this->db->update('users', $filteredData, 'id = :id', ['id' => $id]);
    }

    /**
     * حذف کاربر
     */
    public function delete(int $id): int
    {
        return $this->db->delete('users', 'id = :id', ['id' => $id]);
    }

    /**
     * اعتبارسنجی ورود
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // بازگشت بدون password_hash
            unset($user['password_hash']);
            return $user;
        }

        return null;
    }
}
