<?php
session_start();
$errors = [];

include_once '../includes/db.php';

// Handle login via "Remember Me" cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name);
        $stmt->fetch();
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        header("Location: ../store/shop.php");
        exit;
    }
}

// Handle login form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "Email and password are required.";
     } else {
        $stmt = $conn->prepare("SELECT id, full_name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hash, $role);
            $stmt->fetch();

            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = $role;

                // Remember me
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    setcookie("remember_token", $token, time() + (86400 * 30), "/"); // 30 days
                    $update = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $update->bind_param("si", $token, $id);
                    $update->execute();
                }

                // Redirect based on role
                if ($role === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../store/shop.php");
                }
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }

     }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/Login_Register.css">
</head>
<body>
    <main class="login-container">
        <header class="login-header">
            <a href="../store/shop.php" class="logo-link">
                <h1 class="logo"><img src="../assets/logo.png" alt=""></h1>
            </a>
            <h2>Login</h2>
        </header>

        <?php foreach ($errors as $error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <form method="POST" class="login-form" id="loginForm">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required><br><br>
                <span class="password-toggle" id="togglePassword">👁️</span>
            </div>

            <div class="form-options">
                <label><input type="checkbox" name="remember"> Remember Me</label><br><br>
                <a href="../includes/forgot-password.php">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="register-prompt">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
            
    </main>

    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            const passwordField = document.getElementById("password");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                this.textContent = "🙈"; 
            } else {
                passwordField.type = "password"; 
                this.textContent = "👁️"; 
            }
        });
    </script>

</body>
</html>
