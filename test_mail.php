<?php
require 'send_email.php'; // wherever you save this file

$sent = sendEmail('codingwithniraj@gmail.com', '
    <h2>Booking Confirmed!</h2>
    <p>Thank you for booking with <strong>Book a Bite</strong>.</p>
    <p>Your reservation has been successfully confirmed.</p>
    <p>We look forward to serving you!</p>
');

if ($sent) {
    echo "Email sent successfully!";
} else {
    echo "Email could not be sent.";
}