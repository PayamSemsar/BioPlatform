<?php
/**
 * کلاس مدیریت اتصال به دیتابیس با PDO
 * Singleton pattern برای اتصال یکتا
 */

declare(strict_types=1);

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    /**
     * Constructor خصوصی برای Singleton
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        $this->connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );
    }

    /**
     * دریافت نمونه یکتا از کلاس
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * جلوگیری از Clone
     */
    private function __clone() {}

    /**
     * جلوگیری از unserialize
     */
    public function __wakeup(): void
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * دریافت اتصال PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * اجرای query با prepared statement
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * دریافت یک رکورد
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * دریافت همه رکوردها
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * درج رکورد و دریافت ID
     * @param string $table
     * @param array $data
     * @return string
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }

    /**
     * به‌روزرسانی رکورد
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @return int
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
        }
        $set = implode(', ', $setParts);

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";

        $params = array_merge($data, $whereParams);
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * حذف رکورد
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * شروع تراکنش
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * کامیت تراکنش
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * رول‌بک تراکنش
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}
