<?php

require_once __DIR__ . '/config/config.inc.php';
require_once __DIR__ . '/vendor/autoload.php';

// Разрешаем CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Для OPTIONS запросов сразу возвращаем успешный ответ
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем тело запроса
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Проверяем наличие action в запросе
if (!isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Не указано действие']);
    exit();
}

$action = $data['action'];
$response = [];

try {
    // Подключаем файл обработчика в зависимости от действия
    $handler_file = __DIR__ . '/includes/' . $action . '.php';
    
    if (!file_exists($handler_file)) {
        throw new Exception('Обработчик не найден');
    }
    
    // Получаем имя функции обработчика (берем часть после / если есть)
    $handler_function = strpos($action, '/') !== false ? substr($action, strrpos($action, '/') + 1) : $action;
    
    require_once $handler_file;
    
    // Вызываем функцию обработчика
    if (!function_exists($handler_function)) {
        throw new Exception('Функция обработчика не найдена');
    }
    
    $response = $handler_function($data['data']);
    
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Отправляем ответ
echo json_encode($response);




?>