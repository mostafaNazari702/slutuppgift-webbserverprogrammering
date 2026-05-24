<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_mail(string $to, string $subject, string $bodyHtml): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;
        $mail->AltBody = strip_tags($bodyHtml);

        $mail->send();

        $line = sprintf("[%s] OK: %s -> %s\n", date('Y-m-d H:i:s'), $subject, $to);
        file_put_contents(__DIR__ . '/../mail.log', $line, FILE_APPEND);
        return true;
    } catch (Exception $e) {
        $line = sprintf("[%s] FAIL: %s -> %s : %s\n",
            date('Y-m-d H:i:s'), $subject, $to, $mail->ErrorInfo);
        file_put_contents(__DIR__ . '/../mail.log', $line, FILE_APPEND);
        return false;
    }
}
