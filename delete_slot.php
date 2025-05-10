<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'doctor' || !isset($_GET['id'])) {
    header('Location: login.php');
    exit;
}

$slot_id = (int)$_GET['id'];
$doctor_id = $_SESSION['user_id'];

// Проверяем принадлежность слота
$stmt = $pdo->prepare("DELETE FROM doctor_schedule WHERE id = ? AND doctor_id = ?");
$stmt->execute([$slot_id, $doctor_id]);

$_SESSION['success'] = "Слот расписания удален";
header('Location: doctor_schedule.php');