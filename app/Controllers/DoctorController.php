<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header('Location: login.php');
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Добавление нового слота расписания
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    // Валидация
    if (strtotime($start_time) >= strtotime($end_time)) {
        $_SESSION['error'] = "Время окончания должно быть позже времени начала";
    } else {
        $stmt = $pdo->prepare("INSERT INTO doctor_schedule (doctor_id, date, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$doctor_id, $date, $start_time, $end_time]);
        $_SESSION['success'] = "Слот расписания добавлен";
    }
}

// Загружаем текущее расписание
$schedule = $pdo->prepare("SELECT * FROM doctor_schedule WHERE doctor_id = ? ORDER BY date, start_time");
$schedule->execute([$doctor_id]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h2>Мое расписание</h2>
    
    <!-- Форма добавления слота -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Добавить время приема</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <label>Дата</label>
                        <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Время начала</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Время окончания</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Добавить</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Текущее расписание -->
    <div class="card">
        <div class="card-header">
            <h5>Мои доступные слоты</h5>
        </div>
        <div class="card-body">
            <?php if ($schedule->rowCount() > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Время</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($slot = $schedule->fetch()): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($slot['date'])) ?></td>
                            <td><?= date('H:i', strtotime($slot['start_time'])) ?> - <?= date('H:i', strtotime($slot['end_time'])) ?></td>
                            <td>
                                <a href="delete_slot.php?id=<?= $slot['id'] ?>" class="btn btn-sm btn-danger">Удалить</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Нет добавленных слотов расписания.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>