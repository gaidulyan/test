<?php

// Конфигурация базы данных
define('DB_HOST', '10.0.0.59');
define('DB_USER', 'ai');

define('DB_PASSWORD', 'tdiITgAopPuN1Ck5'); // Пароль для подключения к БД
define('DB_NAME', 'test'); // Название базы данных



// Класс для работы с базой данных
class Database {

    private static $instance = null;
    private $connection;

    // Закрытый конструктор для паттерна Singleton
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, 
                DB_PASSWORD
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Ошибка подключения к БД: " . $e->getMessage());
        }
    }

    // Получение единственного экземпляра класса
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Получение подключения
    public function getConnection() {
        return $this->connection;
    }

    // Выполнение запроса
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            die("Ошибка выполнения запроса: " . $e->getMessage());
        }
    }

    // Получение одной строки
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    // Получение всех строк
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    // Запрет клонирования для паттерна Singleton
    private function __clone() {}

    // Обновление записи
    public function update($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        } catch(PDOException $e) {
            die("Ошибка обновления записи: " . $e->getMessage());
        }
    }

    // Удаление записи
    public function delete($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        } catch(PDOException $e) {
            die("Ошибка удаления записи: " . $e->getMessage());
        }
    }
    
}
