<?php

namespace App;

/**
 * Класс для работы с базой данных
 * Реализует паттерн Singleton для единственного подключения
 */
class Database {

    private static $instance = null;
    private $connection;

    // Закрытый конструктор для паттерна Singleton
    private function __construct() {
        
        try {

            $this->connection = new \PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, 
                DB_PASSWORD
            );
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        } catch(\PDOException $e) {
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
        } catch(\PDOException $e) {
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

    // Обновление записи
    public function update($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        } catch(\PDOException $e) {
            die("Ошибка обновления записи: " . $e->getMessage());
        }
    }

    // Удаление записи
    public function delete($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($params);
            return $result;
        } catch(\PDOException $e) {
            die("Ошибка удаления записи: " . $e->getMessage());
        }
    }
    
    /**
     * Логирование SQL-операций
     * 
     * @param array $params Параметры операции
     * @return bool Результат логирования
     */
    public function logSql($params) {
        try {
            // Проверяем наличие обязательных параметров
            if (!isset($params['operation_type']) || !isset($params['table_name']) || !isset($params['sql_query'])) {
                return false;
            }
            
            // Подготавливаем данные для логирования
            $logData = [
                'operation_type' => $params['operation_type'],
                'table_name' => $params['table_name'],
                'sql_query' => $params['sql_query'],
                'params' => isset($params['params']) ? json_encode($params['params']) : null,
                'affected_rows' => isset($params['affected_rows']) ? $params['affected_rows'] : null,
                'insert_id' => isset($params['insert_id']) ? $params['insert_id'] : null,
                'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
                'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
                'execution_time' => isset($params['execution_time']) ? $params['execution_time'] : null
            ];
            
            // Формируем SQL-запрос для вставки лога
            $fields = array_keys($logData);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO sql_logs (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            // Выполняем запрос
            $this->query($sql, array_values($logData));
            
            return true;
            
        } catch (\Exception $e) {
            // В случае ошибки просто возвращаем false, чтобы не прерывать основную операцию
            return false;
        }
    }
    
    // Запрет клонирования для паттерна Singleton
    private function __clone() {}
} 