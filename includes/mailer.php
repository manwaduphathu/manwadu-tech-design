<?php
require_once __DIR__ . '../vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetEmail($toEmail, $resetLink) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'manwadup66@gmail.com';
        $mail->Password   = 'lbwukalcqrsegvef';   
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom('yourgmail@gmail.com', 'Manwadu Tech & Design');
        $mail->addAddress($toEmail);

        $token = bin2hex(random_bytes(32));
        $resetLink = "http://localhost/manwadu-tech-design/includes/reset-password.php?token=$token";

        // Save $token and expiry into DB here...

        $mail->Body = "
            Hi,<br><br>
            Click the link below to reset your password:<br><br>
            <a href='$resetLink'>$resetLink</a><br><br>
            If you didn't request this, you can ignore this email.<br><br>
            Regards,<br>
            <strong>Manwadu Tech & Design</strong>
        ";
        $mail->send();

        return true;
    }catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo; 
    return false;
    }

}
