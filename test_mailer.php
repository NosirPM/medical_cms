<?php
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
echo 'PHPMailer успешно загружен! Версия: ' . $mail::VERSION;