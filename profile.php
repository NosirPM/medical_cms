<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Проверка авторизации и роли
if (!isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit;
}

try {
    // Получаем данные пациента
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch();

    if (!$patient) {
        throw new Exception("Данные пациента не найдены");
    }

    // Ближайшие записи (только предстоящие)
    $stmt = $pdo->prepare("SELECT a.*, d.full_name AS doctor_name, d.specialization 
                          FROM appointments a
                          JOIN users d ON a.doctor_id = d.id
                          WHERE a.patient_id = ? AND a.status = 'confirmed'
                          AND a.appointment_date >= NOW()
                          ORDER BY a.appointment_date ASC
                          LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_appointments = $stmt->fetchAll();

    // История всех записей
    $stmt = $pdo->prepare("SELECT a.*, d.full_name AS doctor_name, d.specialization 
                          FROM appointments a
                          JOIN users d ON a.doctor_id = d.id
                          WHERE a.patient_id = ?
                          ORDER BY a.appointment_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $all_appointments = $stmt->fetchAll();

} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Боковая панель с профилем -->
            <div class="card profile-card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                    </div>
                    <h4><?= htmlspecialchars($patient['full_name']) ?></h4>
                    <p class="text-muted">Пациент</p>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <?= htmlspecialchars($patient['email']) ?>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-phone me-2 text-primary"></i>
                        <?= $patient['phone'] ? htmlspecialchars($patient['phone']) : 'Не указан' ?>
                    </li>
                </ul>
                <div class="card-body">
                    <a href="edit_profile.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-edit me-2"></i>Редактировать
                    </a>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="d-grid gap-2">
                <a href="appointment.php" class="btn btn-success quick-action-btn">
                    <i class="fas fa-calendar-plus me-2"></i>Новая запись
                </a>
                <a href="medical_history.php" class="btn btn-info quick-action-btn">
                    <i class="fas fa-file-medical me-2"></i>Моя история
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Ближайшие записи -->
            <div class="card profile-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Ближайшие записи</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcoming_appointments)): ?>
                        <div class="list-group">
                            <?php foreach ($upcoming_appointments as $app): ?>
                                <div class="list-group-item appointment-card mb-2">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($app['doctor_name']) ?></h6>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($app['specialization']) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">
                                                <?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?>
                                            </div>
                                            <span class="badge bg-success">Подтверждено</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <a href="cancel_appointment.php?id=<?= $app['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger">
                                            Отменить
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">У вас нет предстоящих записей</p>
                            <a href="appointment.php" class="btn btn-primary">
                                Записаться на прием
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- История всех записей -->
            <div class="card profile-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>История записей</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($all_appointments)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Врач</th>
                                        <th>Статус</th>
                                        <th>Причина</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_appointments as $app): ?>
                                        <tr>
                                            <td><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></td>
                                            <td>
                                                <?= htmlspecialchars($app['doctor_name']) ?>
                                                <small class="text-muted d-block"><?= $app['specialization'] ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $app['status'] === 'confirmed' ? 'success' : 
                                                    ($app['status'] === 'canceled' ? 'danger' : 'warning') 
                                                ?>">
                                                    <?= $app['status'] ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($app['reason']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">У вас еще нет записей</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>