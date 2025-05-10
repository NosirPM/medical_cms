<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once '../includes/config.php';


if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Получаем список записей
$appointments = $pdo->query("
    SELECT a.*, u1.full_name AS patient_name, u2.full_name AS doctor_name
    FROM appointments a
    JOIN users u1 ON a.patient_id = u1.id
    JOIN users u2 ON a.doctor_id = u2.id
")->fetchAll();
?>

<table>
    <tr>
        <th>Пациент</th>
        <th>Врач</th>
        <th>Дата и время</th>
        <th>Статус</th>
    </tr>
    <?php foreach ($appointments as $appointment): ?>
    <tr>
        <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
        <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
        <td><?= $appointment['appointment_date'] ?></td>
        <td><?= $appointment['status'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>