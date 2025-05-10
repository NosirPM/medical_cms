<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . 'includes/bootstrap.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

$appointment_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Проверяем, что запись принадлежит пациенту
$stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
$stmt->execute([$appointment_id, $user_id]);

if (!$stmt->fetch()) {
    $_SESSION['error'] = "Запись не найдена или вам недоступна";
    header('Location: profile.php');
    exit;
}

// Меняем статус на "отменено"
$stmt = $pdo->prepare("UPDATE appointments SET status = 'canceled' WHERE id = ?");
$stmt->execute([$appointment_id]);

$_SESSION['success'] = "Запись успешно отменена";
header('Location: profile.php');
?>