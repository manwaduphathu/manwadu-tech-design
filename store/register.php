<?php
session_start();
include_once '../includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $hash);
            $stmt->execute();
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $full_name;
            header("Location: shop.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/Login_Register.css">
</head>
<body>
    <main class="registration-main">
        <header class="login-header">
            <a href="../store/shop.php" class="logo-link">
                <h1 class="logo"><img src="../assets/logo.png" alt=""></h1>
            </a>
            <h2>Create Account</h2>
        </header>
        <?php foreach ($errors as $error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
        <form method="POST" class="registration-form" id="registrationForm">
            <div class="form-group">
                <label for="firstName">Full Name</label>
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" name="confirm" placeholder="Confirm Password" required><br><br>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        <div class="login-prompt">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
        
    </main>
</body>
</html>
