<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Use absolute path for autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
    $mail->SMTPAuth = true;
    $mail->Username = 'velvetbetches@gmail.com'; // Replace with your email
    $mail->Password = 'kzrwsuxcbtmfnipa'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    // Enable debug output (optional)
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

} catch (Exception $e) {
    error_log("Email configuration error: " . $e->getMessage());
}