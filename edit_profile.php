<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Строгая проверка авторизации
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];

// Получаем текущие данные пользователя
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception("Пользователь не найден");
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация CSRF-токена
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Недействительный CSRF-токен");
    }

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Валидация данных
    if (empty($full_name)) {
        $errors[] = "ФИО обязательно для заполнения";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email";
    }
    
    // Если меняется пароль
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 8) {
            $errors[] = "Пароль должен содержать минимум 8 символов";
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "Пароли не совпадают";
        }
    }

    if (empty($errors)) {
        try {
            // Обновление данных
            $data = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'id' => $user_id
            ];
            
            $sql = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone";
            
            // Если меняется пароль
            if (!empty($_POST['new_password'])) {
                $data['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql .= ", password = :password";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($data);
            
            $_SESSION['success'] = "Профиль успешно обновлен";
            header('Location: profile.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении профиля: " . $e->getMessage();
        }
    }
}

// Генерация CSRF-токена
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Редактирование профиля</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?= implode('<br>', $errors) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?= htmlspecialchars($user['full_name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Телефон</label>
                            <input type="tel" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label">Новый пароль (оставьте пустым, если не меняется)</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Подтвердите пароль</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            <a href="profile.php" class="btn btn-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>