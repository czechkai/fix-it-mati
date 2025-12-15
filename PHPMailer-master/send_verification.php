<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);

$email = $_POST['email']; // from registration form
$token = bin2hex(random_bytes(32));

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yourgmail@gmail.com';
    $mail->Password = 'APP_PASSWORD_HERE';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('yourgmail@gmail.com', 'My App');
    $mail->addAddress($email);

    $verifyLink = "http://localhost/your-project/verify.php?token=$token";

    $mail->isHTML(true);
    $mail->Subject = 'Verify Your Email';
    $mail->Body = "
        <h3>Email Verification</h3>
        <p>Click the link below to verify your email:</p>
        <a href='$verifyLink'>Verify Email</a>
    ";

    $mail->send();
    echo "Verification email sent!";
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}
