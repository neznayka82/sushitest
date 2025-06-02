<?php

namespace App\Database;

use Exception;
use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        try {
            $this->connection = new PDO(
                "{$config['driver']}:host={$config['host']};dbname={$config['database']}",
                $config['username'],
                $config['password']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return false|\PDOStatement
     * @throws Exception
     */
    public static function query(string $sql, array $params = []): bool|\PDOStatement
    {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare($sql);
        if (!$stmt) {
            $error = $pdo->errorInfo();
            throw new Exception("Error:" . implode(";\n", $error));
        }
        if (!$stmt->execute($params)) {
            $error = $pdo->errorInfo();
            throw new Exception("Error:" . implode(";\n", $error));
        }
        return $stmt;
    }
}