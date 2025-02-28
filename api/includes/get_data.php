<?php

require_once __DIR__ . '/../config/config.inc.php';

$db = Database::getInstance();


function get_data($table, $params = []) {
    
    global $db;

    if (empty($table)) {
        throw new Exception('Не указана таблица для получения данных');
    }

    $sql = "SELECT * FROM $table";

    if (!empty($params)) {
        $sql .= " WHERE " . implode(" AND ", $params);
    }

    $data = $db->fetchAll($sql);

    return $data;
}





