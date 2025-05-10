
 <?php
require __DIR__ . '/includes/bootstrap.php';

$request = $_SERVER['REQUEST_URI'];
$basePath = '/medical_cms'; // Базовый путь приложения

// Нормализация пути
$path = parse_url($request, PHP_URL_PATH);
$path = rtrim(str_replace($basePath, '', $path), '/');
$path = empty($path) ? '/' : $path;

// Логирование для отладки
error_log("Request: {$request} | Path: {$path}");

// Защищенные маршруты (требуют авторизации)
$protectedRoutes = [
    '/admin',
    '/profile',
    '/appointments'
];

// Определение маршрутов
$routes = [
    '/' => 'index.php',
    '/login' => 'login.php',
    '/register' => 'register.php',
    '/logout' => 'logout.php',
    '/admin' => 'admin/dashboard.php',
    '/profile' => 'profile.php',
    '/appointments' => 'appointments.php',
    '/doctors' => 'doctors.php',
    '/services' => 'services.php',
    '/contact' => 'contact.php',
    '/404' => '404.php'
];

// Проверка авторизации для защищенных маршрутов
if (in_array($path, $protectedRoutes) && !isLoggedIn()) {
    header('Location: /login');
    exit;
}

// Обработка маршрута
if (array_key_exists($path, $routes)) {
    $file = __DIR__ . '/' . $routes[$path];
    
    if (file_exists($file)) {
        require $file;
    } else {
        error_log("File not found: {$file}");
        serveNotFound();
    }
} else {
    serveNotFound();
}

/**
 * Отображает страницу 404
 */
function serveNotFound() {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}