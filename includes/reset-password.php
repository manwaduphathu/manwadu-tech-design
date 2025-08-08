<?php
include_once '../includes/db.php';

$errors = [];
$success = "";
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id);
            $stmt->fetch();
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $update->bind_param("si", $hash, $id);
            $update->execute();

            $success = "Password updated. You can now <a href='login.php'>log in</a>.";
        } else {
            $errors[] = "Invalid or expired token.";
        }
    }
}
?>
<h2>Reset Password</h2>
<form method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="password" name="password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm" placeholder="Confirm Password" required><br><br>
    <button type="submit">Reset Password</button>
</form>
<?= $success ?>
<?php foreach ($errors as $e) echo "<p style='color:red'>$e</p>"; ?>
