<?php

namespace App\Model;

use App\Database\Database;
use Exception;
use PDO;

class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /** Подготовка и проверка запроса на выборку данных
     * @param string $sql
     * @param array|null $params
     * @return \PDOStatement
     * @throws Exception
     */
    private static function prepareSelectQuery(string $sql, array $params = null): \PDOStatement
    {
        // Для других запросов — исключение
        if (stripos(mb_strtoupper($sql), 'SELECT') === FALSE) {
            throw new Exception("Only SELECT queries are supported");
        }

        return Database::getInstance()::query($sql, $params);
    }

    /** Выполняем запрос к БД
     * @param string $sql
     * @param array|null $params
     * @return array
     * @throws Exception
     */
    public static function select(string $sql, array $params = null) : array
    {
        $stmt = self::prepareSelectQuery($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
        return ($result !== false) ? $result : [];
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @return Model|null
     * @throws Exception
     */
    public static function selectOne(string $sql, array $params = null) : ?Model
    {
        $stmt = self::prepareSelectQuery($sql, $params);
        $result = $stmt->fetchObject( static::class);
        return ($result !== false) ? $result : null;
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @return int
     * @throws Exception
     */
    public static function insert(string $sql, array $params = null): int
    {
        if (stripos(mb_strtoupper($sql), 'INSERT') === FALSE) {
            throw new Exception("Only INSERT queries are supported");
        }

        Database::query($sql, $params);

        return Database::getInstance()->getConnection()->lastInsertId();
    }

    /**
     * @param string $sql
     * @param array|null $params
     * @return int
     * @throws Exception
     */
    public static function update(string $sql, array $params = null): int
    {
        if (stripos(mb_strtoupper($sql), 'UPDATE') === FALSE) {
            throw new Exception("Only UPDATE queries are supported");
        }

        Database::query($sql, $params);

        return Database::getInstance()->getConnection()->lastInsertId();
    }

}