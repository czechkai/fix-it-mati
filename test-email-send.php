<?php
require __DIR__ . '/autoload.php';

$mail = new \PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'fixitmati@gmail.com';
    $mail->Password = 'baekzlltzngeqgtm';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    // Disable SSL verification for systems with certificate issues
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    $mail->setFrom('fixitmati@gmail.com', 'FixItMati');
    $mail->addAddress('fixitmati@gmail.com');
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body = 'This is a test email to verify PHPMailer is working.';
    
    if ($mail->send()) {
        echo "✅ Email sent successfully!\n";
    } else {
        echo "❌ Error sending email: " . $mail->ErrorInfo . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "PHPMailer Error: " . $mail->ErrorInfo . "\n";
}
