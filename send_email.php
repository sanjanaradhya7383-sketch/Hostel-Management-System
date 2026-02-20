<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes safely
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

// Prevent redeclaration
if (!function_exists('sendEmail')) {

    function sendEmail($to, $subject, $body) {

        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;

            // SYSTEM EMAIL (already created by you)
            $mail->Username   = 'hostelcomplaint1@gmail.com';

            // ⛔ REPLACE THIS ONLY (when you get app password)
            $mail->Password   = 'xgxr qrzq ydzo yqge';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender identity
            $mail->setFrom(
                'hostelcomplaint1@gmail.com',
                'Hostel Complaint System'
            );

            // Receiver (warden or student)
            $mail->addAddress($to);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;

        } catch (Exception $e) {
            // Silent fail so system doesn't crash
            return false;
        }
    }
}
