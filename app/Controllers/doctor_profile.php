<?php
require_once __DIR__ . '/includes/bootstrap.php'; // Подключаем все зависимости

// Проверяем, что пользователь - врач
if (!isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header('Location: login.php');
    exit;
}

// Получаем ID врача
$doctor_id = $_SESSION['user_id'];

// Загружаем активные записи
$appointments = $pdo->prepare("
    SELECT a.*, u.full_name AS patient_name, u.phone 
    FROM appointments a
    JOIN users u ON a.patient_id = u.id
    WHERE a.doctor_id = ? AND a.status = 'confirmed'
    ORDER BY a.appointment_date
");
$appointments->execute([$doctor_id]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Личный кабинет врача</h2>
    
    <!-- Расписание на сегодня -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Сегодняшние приемы</h5>
        </div>
        <div class="card-body">
            <?php if ($appointments->rowCount() > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Время</th>
                            <th>Пациент</th>
                            <th>Телефон</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $appointments->fetch()): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($row['appointment_date'])) ?></td>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                                <a href="complete_appointment.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Завершить</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Нет запланированных приемов.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>