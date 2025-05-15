<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Редирект авторизованных пользователей
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'doctor':
            header('Location: doctor_schedule.php');
            break;
        default:
            header('Location: profile.php');
    }
    exit;
}

// Подключение шапки
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="hero-section text-center py-5 mb-5 bg-light rounded">
        <h1 class="display-4 fw-bold text-primary">Медицинский центр "Здоровье"</h1>
        <p class="lead fs-4">Профессиональная медицинская помощь с заботой о каждом пациенте</p>
        <div class="hero-buttons mt-4">
            <div class="row justify-content-center g-3">
                <div class="col-lg-4 col-md-6">
                    <a href="login.php" class="btn btn-primary btn-lg w-100 py-3">
                        <i class="fas fa-sign-in-alt me-2"></i> Вход в систему
                    </a>
                </div>
                <div class="col-lg-4 col-md-6">
                    <a href="register.php" class="btn btn-success btn-lg w-100 py-3">
                        <i class="fas fa-user-plus me-2"></i> Регистрация
                    </a>
                </div>
            </div>
            <div class="mt-3">
                <a href="appointment.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-calendar-check me-2"></i> Записаться на прием
                </a>
            </div>
        </div>
    </div>

    <div class="features-section mb-5">
        <h2 class="text-center mb-4">Почему выбирают нас</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-primary bg-opacity-10 rounded-circle p-3 mb-3 mx-auto">
                            <i class="fas fa-user-md fa-2x text-primary"></i>
                        </div>
                        <h4 class="card-title">Квалифицированные врачи</h4>
                        <p class="card-text">Более 50 специалистов с опытом работы от 10 лет</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-success bg-opacity-10 rounded-circle p-3 mb-3 mx-auto">
                            <i class="fas fa-clock fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Удобное время</h4>
                        <p class="card-text">Работаем с 8:00 до 20:00 без выходных и праздников</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="icon-wrapper bg-info bg-opacity-10 rounded-circle p-3 mb-3 mx-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-info"></i>
                        </div>
                        <h4 class="card-title">Доступное расположение</h4>
                        <p class="card-text">Центр города с удобной парковкой и транспортной развязкой</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cta-section bg-primary text-white rounded p-5 mb-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="mb-3">Запишитесь на прием прямо сейчас!</h3>
                <p class="mb-0">Наши специалисты готовы помочь вам в любое удобное время</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="appointment.php" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-arrow-right me-2"></i> Записаться
                </a>
            </div>
        </div>
    </div>
</div>

<?php 
require_once __DIR__ . '/includes/footer.php';
?>