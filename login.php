<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Если уже авторизован - на профиль
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$error = '';
$email = '';

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Введите email и пароль";
    } elseif (loginUser($email, $password)) {
        header("Location: profile.php");
        exit;
    } else {
        $error = "Неверный email или пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход для пациентов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-box {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box mx-auto">
            <h2 class="text-center mb-4">Вход для пациентов</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= $email ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Войти</button>
                
                <div class="mt-3 text-center">
                    <a href="register.php">Регистрация</a> | 
                    <a href="forgot_password.php">Забыли пароль?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>