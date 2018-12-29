<?php

namespace RunService;

class DB {

    private $driver;
    private $baseName;
    private $host;
    private $user;
    private $pass;
    /**
     * @var \PDO
     */
    private $db;

    /**
     * @param string $driver
     * @param string $baseName
     * @param string $host
     * @param string $user
     * @param string $pass
     */
    public function __construct(string $driver, string $baseName, string $host, string $user, string $pass) {
        $this->driver = $driver;
        $this->baseName = $baseName;
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * Выполняет SQL-запрос.
     * @param string $sql
     * @param array $params
     */
    public function execute(string $sql, array $params = null) {
        /**
         * TODO: Проверить как будут отрабатывать запросы INSERT, UPDATE, CREATE
         */
        $this->db = new \PDO($this->driver . ":dbname=" . $this->baseName . ";host=" . $this->host, $this->user, $this->pass);
        $result = false;
        if ($params === null) {
            $params = [];
        }
        $PDOStatement = $this->db->prepare($sql);
        if ($PDOStatement && $PDOStatement->execute($params)) {
            $result = $PDOStatement->fetchAll();
        }
        return $result;
    }

    /**
     * Инициализация транзакции
     */
    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    /**
     * Фиксирует транзакцию
     */
    public function commit() {
        $this->db->commit();
    }

    /**
     * Откат транзакции
     */
    public function rollBack() {
        $this->db->rollBack();
    }
}