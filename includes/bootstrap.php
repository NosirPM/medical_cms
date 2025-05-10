<?php
// includes/bootstrap.php

// Настройки для разработки
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Старт сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Подключение к БД
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
}

/**
 * Проверка авторизации (упрощенная)
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Вход в систему (упрощенный)
 */
function loginUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ? AND role = 'patient' LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
    } catch (PDOException $e) {
        error_log('Login error: ' . $e->getMessage());
    }
    
    return false;
}

/**
 * Безопасный выход
 */
function logoutUser() {
    $_SESSION = [];
    session_destroy();
}

/**
 * Простая очистка данных
 */
function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
