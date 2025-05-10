<?php
require __DIR__ . '/../includes/bootstrap.php';

// 1. Логирование начала работы
file_put_contents(__DIR__ . '/reminders.log', 
    "[" . date('Y-m-d H:i:s') . "] Запуск отправки напоминаний\n", 
    FILE_APPEND
);

// 2. Запрос будущих приемов
$stmt = $pdo->query("
    SELECT 
        a.id,
        a.appointment_date,
        p.full_name AS patient_name,
        p.email,
        p.phone,
        d.full_name AS doctor_name
    FROM 
        appointments a
    JOIN 
        users p ON a.patient_id = p.id
    JOIN 
        users d ON a.doctor_id = d.id
    WHERE 
        a.status = 'confirmed'
        AND a.appointment_date BETWEEN 
            NOW() AND DATE_ADD(NOW(), INTERVAL 25 HOUR)
        AND a.reminder_sent = 0  // Чтобы не дублировать
");

$mailer = new MedicalMailer();
$sentCount = 0;

// 3. Обработка записей
while ($appointment = $stmt->fetch()) {
    $appointmentDate = date('d.m.Y H:i', strtotime($appointment['appointment_date']));
    
    // 4. Отправка email
    if (!empty($appointment['email'])) {
        $mailer->sendAppointmentNotification(
            $appointment['email'],
            $appointment['patient_name'],
            $appointment['doctor_name'],
            $appointmentDate
        );
    }

    // 5. Отправка SMS (если номер подтвержден)
    if (!empty($appointment['phone']) && str_starts_with($appointment['phone'], '+7')) {
        $smsText = "Напоминание: завтра в {$appointmentDate} прием у {$appointment['doctor_name']}";
        $mailer->sendSMS($appointment['phone'], $smsText);
    }
// После отправки письма добавьте лог
file_put_contents(
    __DIR__ . '/reminders.log',
    "[" . date('Y-m-d H:i:s') . "] Отправлено письмо для: {$appointment['email']}\n",
    FILE_APPEND
);
    // 6. Помечаем как отправленное
    $pdo->prepare("UPDATE appointments SET reminder_sent = 1 WHERE id = ?")
       ->execute([$appointment['id']]);
    
    $sentCount++;
}

// 7. Логирование результата
file_put_contents(__DIR__ . '/reminders.log', 
    "[" . date('Y-m-d H:i:s') . "] Отправлено напоминаний: $sentCount\n", 
    FILE_APPEND
);