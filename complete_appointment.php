<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$appointment_id = (int)$_GET['id'];
$doctor_id = $_SESSION['user_id'];

// Проверяем, что запись принадлежит врачу
$stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
$stmt->execute([$appointment_id, $doctor_id]);

if ($stmt->fetch()) {
    $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?")
       ->execute([$appointment_id]);
    $_SESSION['success'] = "Прием завершен";
} else {
    $_SESSION['error'] = "Запись не найдена";
}

header('Location: doctor_profile.php');