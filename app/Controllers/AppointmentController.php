<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Проверка авторизации и роли
if (!isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Получаем список врачей
try {
    $stmt = $pdo->prepare("SELECT id, full_name, specialization FROM users WHERE role = 'doctor'");
    $stmt->execute();
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Ошибка получения списка врачей: " . $e->getMessage();
}

// Обработка формы записи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'] ?? '';
    $appointment_date = $_POST['appointment_date'] ?? '';
    $reason = $_POST['reason'] ?? '';

    try {
        // Проверяем доступность времени у врача
        $stmt = $pdo->prepare("SELECT id FROM appointments 
                              WHERE doctor_id = ? AND appointment_date = ?");
        $stmt->execute([$doctor_id, $appointment_date]);
        
        if ($stmt->fetch()) {
            $error = "Это время уже занято. Пожалуйста, выберите другое.";
        } else {
            // Создаем запись
            $stmt = $pdo->prepare("INSERT INTO appointments 
                                  (patient_id, doctor_id, appointment_date, reason, status) 
                                  VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $doctor_id, $appointment_date, $reason]);
            $appointment_id = $pdo->lastInsertId();

            // Отправляем уведомления
            sendAppointmentNotifications($appointment_id);

            $success = "Вы успешно записаны на прием! Уведомление отправлено на вашу почту.";
        }
    } catch (PDOException $e) {
        $error = "Ошибка записи на прием: " . $e->getMessage();
    }
}

function sendAppointmentNotifications($appointment_id) {
    global $pdo;
    
    // Получаем данные для уведомления
    $stmt = $pdo->prepare("SELECT 
        a.appointment_date, a.reason,
        p.full_name AS patient_name, p.email AS patient_email, p.phone AS patient_phone,
        d.full_name AS doctor_name, d.specialization
        FROM appointments a
        JOIN users p ON a.patient_id = p.id
        JOIN users d ON a.doctor_id = d.id
        WHERE a.id = ?");
    $stmt->execute([$appointment_id]);
    $data = $stmt->fetch();

    // Формируем сообщение
    $date = date('d.m.Y H:i', strtotime($data['appointment_date']));
    $subject = "Запись на прием к {$data['doctor_name']}";
    $message = "Уважаемый(ая) {$data['patient_name']},\n\n";
    $message .= "Вы записаны на прием к врачу {$data['doctor_name']} ({$data['specialization']})\n";
    $message .= "Дата и время: $date\n";
    $message .= "Причина: {$data['reason']}\n\n";
    $message .= "С уважением,\nМедицинский центр";

    // Отправляем email
    sendEmail($data['patient_email'], $subject, $message);

    // Обновляем статус уведомления
    $stmt = $pdo->prepare("UPDATE appointments SET notification_status = 'sent' WHERE id = ?");
    $stmt->execute([$appointment_id]);
}

function sendEmail($to, $subject, $message) {
    // Здесь должна быть реальная реализация через PHPMailer или mail()
    error_log("Email to $to: $subject - $message");
    // mail($to, $subject, $message);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <h2>Запись на прием</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Выберите врача:</label>
            <select name="doctor_id" class="form-select" required>
                <option value="">-- Выберите врача --</option>
                <?php foreach ($doctors as $doctor): ?>
                    <option value="<?= $doctor['id'] ?>">
                        <?= htmlspecialchars($doctor['full_name']) ?> 
                        (<?= htmlspecialchars($doctor['specialization']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Дата и время приема:</label>
            <input type="datetime-local" name="appointment_date" class="form-control" required
                   min="<?= date('Y-m-d\TH:i') ?>">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Причина обращения:</label>
            <textarea name="reason" class="form-control" rows="3" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Записаться</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>