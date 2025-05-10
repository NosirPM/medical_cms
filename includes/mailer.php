<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class MedicalMailer {
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configure();
        $this->mail->Host = 'localhost';
$this->mail->Port = 1025; // SMTP-порт
$this->mail->SMTPAuth = false; // Авторизация не нужна
    }

    private function configureSMTP() {
        $this->mail->isSMTP();
        $this->mail->Host = 'localhost';  // Или 127.0.0.1
        $this->mail->Port = 1025;         // Порт MailHog
        $this->mail->SMTPAuth = false;    // Авторизация не требуется
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendSMS($to, $message) {
        $sid = "ACCOUNT_SID";
        $token = "AUTH_TOKEN";
        $client = new Twilio\Rest\Client($sid, $token);
    
        $client->messages->create(
            $to,
            [
                'from' => '+1234567890',
                'body' => $message
            ]
        );
        
    }    private function configure() {
        // Настройки SMTP (пример для Mailtrap)
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.mailtrap.io';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'ваш_логин';
        $this->mail->Password = 'ваш_пароль';
        $this->mail->Port = 2525;
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendAppointmentNotification($to, $patientName, $doctorName, $dateTime) {
        try {
            $this->mail->setFrom('clinic@example.com', 'Медицинский центр');
            $this->mail->addAddress($to);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Подтверждение записи на прием';
            
            $html = file_get_contents(__DIR__ . '/../templates/email_appointment.html');
            $html = str_replace(
                ['{{patient}}', '{{doctor}}', '{{datetime}}'],
                [$patientName, $doctorName, $dateTime],
                $html
            );
            
            $this->mail->Body = $html;
            $this->mail->send();
            
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}