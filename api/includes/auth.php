<?php

/**
 * Функция проверки авторизации пользователя
 * 
 * @param array $arr Массив с данными пользователя
 * @return array Результат авторизации
 */
function auth($params) {

    $login = $params['login'];
    $password = $params['password'];

    try {
        // Получаем экземпляр базы данных
        $db = \App\Database::getInstance();
        
        // Получаем пользователя по имени
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE login = ?", 
            [$login]
        );
        
        if (!$user) {
            // Пользователь не найден
            return [
                'success' => false,
                'message' => 'Пользователь не найден'
            ];
        }
        
        // Проверяем пароль с использованием password_verify для хешированных паролей
        if (password_verify($password, $user['password'])) {
            // Успешная авторизация
            // Удаляем пароль из массива перед возвратом
            unset($user['password']);
            
            return [
                'success' => true,
                'user' => $user
            ];
        } else {
            // Неверный пароль
            return [
                'success' => false,
                'message' => 'Неверный пароль'
            ];
        }
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Ошибка при авторизации: ' . $e->getMessage()
        ];
    }
} 