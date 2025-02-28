<?php

require_once __DIR__ . '/config/config.inc.php';
require_once __DIR__ . '/vendor/autoload.php';

// Разрешаем CORS
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

// Для OPTIONS запросов сразу возвращаем успешный ответ
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Получаем API-ключ из заголовков
$apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : null;

// Проверяем API-ключ
if (!$apiKey || !isValidApiKey($apiKey)) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен: недействительный API-ключ']);
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

// Получаем имя приложения на основе API-ключа
$appName = getAppNameByApiKey($apiKey);

// Логируем входящий запрос через RestLogger
\App\RestLogger::log([
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'request_body' => $data,
    'response_body' => $response,
    'response_status' => http_response_code(),
    'execution_time' => microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"],
    'app_name' => $appName // Передаем имя приложения в лог
]);

// Отправляем ответ
echo json_encode($response);

/**
 * Функция для проверки действительности API-ключа
 * 
 * @param string $apiKey API-ключ
 * @return bool Результат проверки
 */
function isValidApiKey($apiKey) {
    $db = \App\Database::getInstance();
    $result = $db->fetchOne("SELECT * FROM api_keys WHERE api_key = ?", [$apiKey]);
    return !empty($result);
}

/**
 * Функция для получения имени приложения по API-ключу
 * 
 * @param string $apiKey API-ключ
 * @return string|null Имя приложения или null
 */
function getAppNameByApiKey($apiKey) {
    $db = \App\Database::getInstance();
    $result = $db->fetchOne("SELECT app_name FROM api_keys WHERE api_key = ?", [$apiKey]);
    return $result ? $result['app_name'] : null;
}




?>