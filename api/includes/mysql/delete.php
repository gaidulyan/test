<?php

/**
 * Функция удаления данных из базы
 * 
 * @param array $params Параметры запроса
 * @return array Результат операции
 */
function delete($arr) {
    try {
        // Получаем экземпляр базы данных
        $db = \App\Database::getInstance();
        
        // Проверяем наличие обязательных параметров
        if (!isset($arr['table'])) {
            return [
                'success' => false,
                'message' => 'Не указана таблица'
            ];
        }

        $params = $arr['params'];
        
        if (!isset($params['where']) || empty($params['where'])) {
            return [
                'success' => false,
                'message' => 'Не указано условие WHERE для удаления'
            ];
        }
        
        // Получаем имя таблицы
        $table = $arr['table'];
        
        // Получаем условие WHERE
        $where = $params['where'];
        
        // Засекаем время выполнения
        $startTime = microtime(true);
        
        // Формируем SQL-запрос
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        // Добавляем параметры для WHERE, если они есть
        $whereParams = isset($params['where_params']) ? $params['where_params'] : [];
        
        // Выполняем запрос
        $stmt = $db->query($sql, $whereParams);
        
        // Получаем количество затронутых строк
        $affectedRows = $stmt->rowCount();
        
        // Вычисляем время выполнения
        $executionTime = microtime(true) - $startTime;
        
        // Логируем успешную операцию через метод класса Database
        $db->logSql([
            'operation_type' => 'DELETE',
            'table_name' => $table,
            'sql_query' => $sql,
            'params' => $whereParams,
            'affected_rows' => $affectedRows,
            'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
            'execution_time' => $executionTime
        ]);
        
        return [
            'success' => true,
            'message' => 'Данные успешно удалены',
            'affected_rows' => $affectedRows
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при удалении данных: ' . $e->getMessage()
        ];
    }
} 