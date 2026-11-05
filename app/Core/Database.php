<?php
/**
 * Sport Shop – Singleton PDO Database Connection
 */

namespace App\Core;

class Database
{
    private static ?Database $instance = null;
    private static array $config = [];
    private ?\PDO $pdo = null;

    private function __construct()
    {
        // Prevent direct instantiation
    }

    private function __clone()
    {
        // Prevent cloning
    }

    /**
     * Set database configuration (called once from index.php).
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Get the singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection (lazy-initialized).
     */
    public static function getConnection(): \PDO
    {
        return self::getInstance()->connect();
    }

    /**
     * Create or return existing PDO connection.
     */
    private function connect(): \PDO
    {
        if ($this->pdo === null) {
            $cfg = self::$config;

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host']     ?? 'localhost',
                $cfg['port']     ?? 3306,
                $cfg['database'] ?? 'sport_shop',
                $cfg['charset']  ?? 'utf8mb4'
            );

            $this->pdo = new \PDO(
                $dsn,
                $cfg['username'] ?? 'root',
                $cfg['password'] ?? '',
                [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND  => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
                ]
            );
        }

        return $this->pdo;
    }
}
