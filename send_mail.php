<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'mail_config.php';

/**
 * Sends an email via Gmail SMTP.
 *
 * @param string $recipientEmail The email address to send to.
 * @param string $message        The HTML message body.
 * @return bool                  True if sent successfully, false otherwise.
 */
function sendEmail($recipientEmail, $message)
{
    $mail = new PHPMailer(true);

    try {

        // Gmail SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender
        $mail->setFrom(
            MAIL_USERNAME,
            'Book a Bite'
        );

        // Recipient
        $mail->addAddress($recipientEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation';
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        // Send email
        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log('Mailer Error: ' . $mail->ErrorInfo);

        return false;
    }
}
?>