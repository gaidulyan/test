<?php

namespace App;

class RestLogger {
    
    /**
     * Логирование REST-запросов
     * 
     * @param array $params Параметры запроса
     * @return bool Результат логирования
     */
    public static function log($params) {
        try {
            // Получаем экземпляр базы данных
            $db = Database::getInstance();
            
            // Проверяем наличие обязательных параметров
            if (!isset($params['request_method']) || !isset($params['request_uri']) || !isset($params['response_status'])) {
                return false;
            }
            
            // Подготавливаем данные для логирования
            $logData = [
                'request_method' => $params['request_method'],
                'request_uri' => $params['request_uri'],
                'request_body' => isset($params['request_body']) ? json_encode($params['request_body']) : null,
                'response_body' => isset($params['response_body']) ? json_encode($params['response_body']) : null,
                'response_status' => $params['response_status'],
                'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
                'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
                'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
                'execution_time' => isset($params['execution_time']) ? $params['execution_time'] : null,
                'app_name' => isset($params['app_name']) ? $params['app_name'] : null
            ];
            
            // Формируем SQL-запрос для вставки лога
            $fields = array_keys($logData);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO rest_logs (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            
            // Выполняем запрос
            $db->query($sql, array_values($logData));
            
            return true;
            
        } catch (\Exception $e) {
            // В случае ошибки просто возвращаем false, чтобы не прерывать основную операцию
            return false;
        }
    }
} 