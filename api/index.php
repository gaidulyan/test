<?php

require_once __DIR__ . '/config/config.inc.php';


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
    
    
    require_once $handler_file;
    
    // Вызываем функцию обработчика
    $handler_function = 'handle_' . $action;
    if (!function_exists($handler_function)) {
        throw new Exception('Функция обработчика не найдена');
    }
    
    $response = $handler_function($data);
    
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Отправляем ответ
echo json_encode($response);




?>