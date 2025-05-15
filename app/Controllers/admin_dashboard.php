<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Проверка прав администратора
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Получаем статистику для дашборда
$stats = [
    'doctors' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetchColumn(),
    'patients' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn(),
    'appointments' => $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(),
    'today_appointments' => $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn()
];

// Последние 5 записей
$recent_appointments = $pdo->query("
    SELECT a.*, 
           d.full_name AS doctor_name,
           p.full_name AS patient_name
    FROM appointments a
    JOIN users d ON a.doctor_id = d.id
    JOIN users p ON a.patient_id = p.id
    ORDER BY a.appointment_date DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Сайдбар -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5>Меню администратора</h5>
                </div>
                <div class="card-body">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Дашборд
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_doctors.php">
                                <i class="fas fa-user-md"></i> Управление врачами
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_patients.php">
                                <i class="fas fa-users"></i> Управление пациентами
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_appointments.php">
                                <i class="fas fa-calendar-alt"></i> Управление записями
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register_doctor.php">
                                <i class="fas fa-plus-circle"></i> Добавить врача
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="system_settings.php">
                                <i class="fas fa-cog"></i> Настройки системы
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Основной контент -->
        <div class="col-md-9">
            <h2>Административная панель</h2>
            <p class="text-muted">Добро пожаловать, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Администратор') ?>!</p>

            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Врачи</h5>
                            <h2><?= $stats['doctors'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Пациенты</h5>
                            <h2><?= $stats['patients'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Все записи</h5>
                            <h2><?= $stats['appointments'] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Сегодня</h5>
                            <h2><?= $stats['today_appointments'] ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Последние записи -->
            <div class="card">
                <div class="card-header">
                    <h5>Последние записи на прием</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Врач</th>
                                <th>Пациент</th>
                                <th>Статус</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_appointments as $appointment): ?>
                            <tr>
                                <td><?= date('d.m.Y H:i', strtotime($appointment['appointment_date'])) ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $appointment['status'] === 'completed' ? 'success' : 
                                        ($appointment['status'] === 'canceled' ? 'danger' : 'warning') 
                                    ?>">
                                        <?= $appointment['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_appointment.php?id=<?= $appointment['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="manage_appointments.php" class="btn btn-primary">Все записи</a>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Быстрые действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2 d-md-flex">
                        <a href="register_doctor.php" class="btn btn-success me-md-2">
                            <i class="fas fa-user-plus"></i> Добавить врача
                        </a>
                        <a href="create_appointment.php" class="btn btn-primary me-md-2">
                            <i class="fas fa-calendar-plus"></i> Создать запись
                        </a>
                        <a href="system_settings.php" class="btn btn-secondary">
                            <i class="fas fa-cog"></i> Настройки
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>