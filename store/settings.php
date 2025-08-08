<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Handle image upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "../uploads/";
        $fileName = basename($_FILES["profile_picture"]["name"]);
        $targetFile = $targetDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
            $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, profile_picture=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $phone, $targetFile, $user_id);
        } else {
            $message = "Failed to upload image.";
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Failed to update profile.";
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $result = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $row = $result->fetch_assoc();

    if (password_verify($current, $row['password'])) {
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $new, $user_id);
        $stmt->execute();
        $message = "Password changed successfully!";
    } else {
        $message = "Current password is incorrect.";
    }
}

// Handle delete account
if (isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    session_destroy();
    header("Location: ../index.html");
    exit;
}

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/settings-style.css">
</head>
<body>
    <div class="settings-container">
        <h2>Account Settings</h2>
        <p><?= $message ?></p>

        <!-- Profile Form -->
        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

            <label>Profile Picture:</label>
            <input type="file" name="profile_picture">

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <hr>

        <!-- Password Form -->
        <form method="POST">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>

            <label>New Password:</label>
            <input type="password" name="new_password" required>

            <button type="submit" name="change_password">Change Password</button>
        </form>

        <hr>

        <!-- Delete Account -->
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account?');">
            <button type="submit" name="delete_account" style="background:red;color:white;">Delete My Account</button>
        </form>
    </div>
</body>
</html>
