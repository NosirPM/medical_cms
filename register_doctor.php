<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Только администратор может регистрировать врачей
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);

    // Валидация
    if (empty($username)) $errors[] = "Введите логин";
    if (empty($specialization)) $errors[] = "Укажите специализацию";
    if (strlen($password) < 6) $errors[] = "Пароль должен быть не менее 6 символов";

    // Проверка уникальности
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) $errors[] = "Пользователь с таким логином/email уже существует";

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, full_name, phone, specialization) VALUES (?, ?, ?, 'doctor', ?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $email, $full_name, $phone, $specialization]);
        
        $_SESSION['success'] = "Врач успешно зарегистрирован";
        header('Location: admin_dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <h2>Регистрация нового врача</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Логин*</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email*</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Пароль* (мин. 6 символов)</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">ФИО*</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Специализация*</label>
                    <input type="text" name="specialization" class="form-control" required>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Зарегистрировать врача</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>