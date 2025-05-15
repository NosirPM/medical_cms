<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Медицинский центр 'Здоровье' - профессиональная медицинская помощь">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?>Медицинский центр "Здоровье"</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Основная навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-hospital me-2"></i>
                <span>МедЦентр "Здоровье"</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">О клинике</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php">Услуги</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">Врачи</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Контакты</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php 
                        // Безопасная проверка роли пользователя
                        $userRole = $_SESSION['role'] ?? 'patient'; // Значение по умолчанию
                        ?>
                        
                        <?php if ($userRole === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-warning" href="admin_dashboard.php">
                                    <i class="fas fa-cog me-1"></i> Админ-панель
                                </a>
                            </li>
                        <?php elseif ($userRole === 'doctor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="doctor_schedule.php">
                                    <i class="fas fa-calendar-alt me-1"></i> Расписание
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name'] ?? 'Профиль') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Профиль</a></li>
                                <li><a class="dropdown-item" href="appointments.php"><i class="fas fa-calendar-check me-2"></i>Мои записи</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Вход
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus me-1"></i> Регистрация
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Хлебные крошки -->
    <nav aria-label="breadcrumb" class="bg-light py-2">
        <div class="container">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home me-1"></i> Главная</a></li>
                <?php
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $pathParts = explode('/', trim($uri, '/'));
                $currentPath = '';
                
                foreach ($pathParts as $i => $part) {
                    if (empty($part)) continue;
                    
                    $currentPath .= '/' . $part;
                    $pageName = str_replace(['.php', '-', '_'], ['', ' ', ' '], $part);
                    $pageName = ucfirst($pageName);
                    
                    if ($i === count($pathParts) - 1) {
                        echo '<li class="breadcrumb-item active" aria-current="page">' . $pageName . '</li>';
                    } else {
                        echo '<li class="breadcrumb-item"><a href="' . $currentPath . '">' . $pageName . '</a></li>';
                    }
                }
                ?>
            </ol>
        </div>
    </nav>