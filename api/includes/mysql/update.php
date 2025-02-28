<?php

/**
 * Функция обновления данных в базе
 * 
 * @param array $params Параметры запроса
 * @return array Результат операции
 */
function update($params) {
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
                'message' => 'Не указаны данные для обновления'
            ];
        }
        
        if (!isset($params['where']) || empty($params['where'])) {
            return [
                'success' => false,
                'message' => 'Не указано условие WHERE для обновления'
            ];
        }
        
        // Получаем имя таблицы
        $table = $params['table'];
        
        // Получаем данные для обновления
        $data = $params['params'];
        
        // Получаем условие WHERE
        $where = $params['where'];
        
        // Засекаем время выполнения
        $startTime = microtime(true);
        
        // Формируем список SET для обновления
        $setList = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $setList[] = "{$field} = ?";
            $values[] = $value;
        }
        
        // Формируем SQL-запрос
        $sql = "UPDATE {$table} SET " . implode(', ', $setList) . " WHERE {$where}";
        
        // Добавляем параметры для WHERE, если они есть
        if (isset($params['where_params']) && is_array($params['where_params'])) {
            $values = array_merge($values, $params['where_params']);
        }
        
        // Выполняем запрос
        $stmt = $db->query($sql, $values);
        
        // Получаем количество затронутых строк
        $affectedRows = $stmt->rowCount();
        
        // Вычисляем время выполнения
        $executionTime = microtime(true) - $startTime;
        
        // Логируем успешную операцию через метод класса Database
        $db->logSql([
            'operation_type' => 'UPDATE',
            'table_name' => $table,
            'sql_query' => $sql,
            'params' => $values,
            'affected_rows' => $affectedRows,
            'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
            'execution_time' => $executionTime
        ]);
        
        return [
            'success' => true,
            'sql' => $sql,
            'params' => $values,
            'message' => 'Данные успешно обновлены',
            'affected_rows' => $affectedRows
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при обновлении данных: ' . $e->getMessage()
        ];
    }
} 