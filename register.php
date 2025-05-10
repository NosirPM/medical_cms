<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Проверка существования функции
if (!function_exists('sanitizeInput')) {
    die('Функция sanitizeInput не определена. Проверьте bootstrap.php');
}

// Остальной код...
if (isLoggedIn()) {
    header('Location: profile.php');
    exit;
}

$errors = [];
$input = [];
$suggestedUsernames = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'username' => sanitizeInput(trim($_POST['username'] ?? '')),
        'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
        'password' => $_POST['password'] ?? '',
        'full_name' => sanitizeInput(trim($_POST['full_name'] ?? ''))
    ];

    // Валидация
    if (empty($input['username']) || !preg_match('/^[a-zA-Z0-9_]+$/', $input['username'])) {
        $errors['username'] = "Используйте только буквы, цифры и подчёркивание";
    }

    if (empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Введите корректный email";
    }

    if (empty($input['password'])) {
        $errors['password'] = "Введите пароль";
    } elseif (strlen($input['password']) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов";
    }

    if (empty($input['full_name'])) {
        $errors['full_name'] = "Введите ваше имя";
    }

    // Проверка уникальности только для логина и email
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Проверка логина
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$input['username']]);
            
            if ($stmt->fetch()) {
                // Предлагаем варианты логина
                $baseUsername = $input['username'];
                for ($i = 1; $i <= 5; $i++) {
                    $suggestedUsernames[] = $baseUsername . $i;
                }
                $errors['username'] = "Логин занят. Попробуйте: " . implode(", ", $suggestedUsernames);
            } 
            // Проверка email
            else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$input['email']]);
                if ($stmt->fetch()) {
                    $errors['email'] = "Email уже используется";
                }
            }

            // Если ошибок нет - регистрируем
            if (empty($errors)) {
                $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users 
                    (username, password, email, role, full_name, created_at) 
                    VALUES (?, ?, ?, 'patient', ?, NOW())");
                
                $stmt->execute([
                    $input['username'],
                    $hashed_password,
                    $input['email'],
                    $input['full_name']
                ]);
                
                // Автоматический вход
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['role'] = 'patient';
                $_SESSION['user_name'] = $input['full_name'];
                $_SESSION['user_email'] = $input['email'];
                
                $pdo->commit();
                header("Location: profile.php");
                exit;
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Registration error: ' . $e->getMessage());
            $errors['general'] = "Ошибка регистрации. Попробуйте позже.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0">Регистрация пациента</h2>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Ваше имя*</label>
                            <input type="text" id="full_name" name="full_name" 
                                   class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($input['full_name'] ?? '') ?>" required>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['full_name'] ?? '') ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин*</label>
                            <input type="text" id="username" name="username" 
                                   class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($input['username'] ?? '') ?>" 
                                   pattern="[a-zA-Z0-9_]+" required>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['username'] ?? '') ?>
                            </div>
                            <small class="text-muted">Буквы, цифры и _</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email*</label>
                            <input type="email" id="email" name="email" 
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   value="<?= htmlspecialchars($input['email'] ?? '') ?>" required>
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['email'] ?? '') ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль*</label>
                            <input type="password" id="password" name="password" 
                                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                   required minlength="6">
                            <div class="invalid-feedback">
                                <?= htmlspecialchars($errors['password'] ?? '') ?>
                            </div>
                            <small class="text-muted">Минимум 6 символов</small>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i> Зарегистрироваться
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>