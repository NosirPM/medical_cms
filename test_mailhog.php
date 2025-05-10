<?php
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/mailer.php';

$mailer = new MedicalMailer();
$result = $mailer->sendAppointmentNotification(
    'patient@test.local',
    'Иванов Иван',
    'Доктор Петрова',
    date('d.m.Y H:i', strtotime('+1 day'))
);

if ($result) {
    echo "Письмо отправлено! Проверьте MailHog: http://localhost:8025";
} else {
    echo "Ошибка: " . error_get_last()['message'];
}