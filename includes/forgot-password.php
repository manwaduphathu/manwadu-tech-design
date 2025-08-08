<?php
include_once '../includes/db.php';

$success = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expires, $email);
        $update->execute();

       $resetLink = "http://yourdomain.com/store/reset-password.php?token=$token";
        include_once '../includes/mailer.php';

        if (sendResetEmail($email, $resetLink)) {
            $success = "A reset link was sent to your email.";
        } else {
            $errors[] = "Failed to send email.";
        }

    } else {
        $errors[] = "Email not found.";
    }
}
?>
<h2>Forgot Password</h2>
<form method="POST">
    <input type="email" name="email" placeholder="Enter your email" required><br><br>
    <button type="submit">Send Reset Link</button>
</form>
<?= $success ?>
<?php foreach ($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
