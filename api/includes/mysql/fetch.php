<?php


/**
 * Функция получения данных из базы
 * 
 * @param array $params Параметры запроса
 * @return array Результат операции
 */
function fetch($arr) {
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
        
        // Получаем имя таблицы
        $table = $arr['table'];
        $params = $arr['params'];

        // Формируем базовый SQL-запрос
        $sql = "SELECT ";
        
        // Определяем, какие поля выбирать
        if (isset($params['fields']) && !empty($params['fields'])) {
            $sql .= $params['fields'];
        } else {
            $sql .= "*";
        }
        
        $sql .= " FROM " . $table;
        
        // Добавляем условия WHERE, если они указаны
        $whereParams = [];
        if (isset($params['where']) && !empty($params['where'])) {
            $sql .= " WHERE " . $params['where'];
            
            // Добавляем параметры для WHERE, если они есть
            if (isset($params['where_params']) && is_array($params['where_params'])) {
                $whereParams = $params['where_params'];
            }
        }
        
        // Добавляем группировку, если она указана
        if (isset($params['group_by']) && !empty($params['group_by'])) {
            $sql .= " GROUP BY " . $params['group_by'];
        }
        
        // Добавляем условия HAVING, если они указаны
        if (isset($params['having']) && !empty($params['having'])) {
            $sql .= " HAVING " . $params['having'];
        }
        
        // Добавляем сортировку, если она указана
        if (isset($params['order_by']) && !empty($params['order_by'])) {
            $sql .= " ORDER BY " . $params['order_by'];
        }
        
        // Добавляем лимит, если он указан
        if (isset($params['limit']) && !empty($params['limit'])) {
            $sql .= " LIMIT " . $params['limit'];
        }
        
        // Засекаем время выполнения
        $startTime = microtime(true);
        
        // Определяем тип выборки
        $fetchType = isset($params['fetch_type']) ? $params['fetch_type'] : 'all';
        
        // Выполняем запрос в зависимости от типа выборки
        if ($fetchType === 'one') {
            $result = $db->fetchOne($sql, $whereParams);
            $rowCount = $result ? 1 : 0;
        } else {
            $result = $db->fetchAll($sql, $whereParams);
            $rowCount = count($result);
        }
        
        // Вычисляем время выполнения
        $executionTime = microtime(true) - $startTime;
        
        // Логируем успешную операцию через метод класса Database
        $db->logSql([
            'operation_type' => 'SELECT',
            'table_name' => $table,
            'sql_query' => $sql,
            'params' => $params,
            'affected_rows' => $rowCount,
            'user_id' => isset($params['user_id']) ? $params['user_id'] : null,
            'execution_time' => $executionTime
        ]);
        
        return [
            'success' => true,
            'sql' => $sql,
            'params' => $params,
            'data' => $result,
            'count' => $rowCount
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при получении данных: ' . $e->getMessage()
        ];
    }
}





