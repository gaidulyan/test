<?php

/**
 * Функция вставки данных в базу
 * 
 * @param array $params Параметры запроса
 * @return array Результат операции
 */
function insert($params) {
    
    try {
        // Получаем экземпляр базы данных
        $db = \App\Database::getInstance();
        
        // Проверяем наличие обязательных параметров
        if (!isset($params['table'])) {
            return [
                'success' => false,
                'message' => 'Не указана таблица'
            ];
        }
        
        if (!isset($params['params']) || !is_array($params['params']) || empty($params['params'])) {
            return [
                'success' => false,
                'message' => 'Не указаны данные для вставки'
            ];
        }
        
        // Получаем имя таблицы
        $table = $params['table'];
        
        // Получаем данные для вставки
        $data = $params['params'];
        
        // Засекаем время выполнения
        $startTime = microtime(true);
        
        // Формируем список полей и значений
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        // Формируем SQL-запрос
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        // Выполняем запрос
        $stmt = $db->query($sql, array_values($data));
        
        // Получаем ID вставленной записи
        $lastInsertId = $db->getConnection()->lastInsertId();
        
        // Вычисляем время выполнения
        $executionTime = microtime(true) - $startTime;
        
        // Логируем успешную операцию через метод класса Database
        $db->logSql([
            'operation_type' => 'INSERT',
            'table_name' => $table,
            'sql_query' => $sql,
            'params' => $data,
            'affected_rows' => 1,
            'insert_id' => $lastInsertId,
            'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
            'execution_time' => $executionTime
        ]);
        
        return [
            'success' => true,
            'sql' => $sql,
            'params' => $data,
            'message' => 'Данные успешно добавлены',
            'insert_id' => $lastInsertId
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при добавлении данных: ' . $e->getMessage()
        ];
    }
} 