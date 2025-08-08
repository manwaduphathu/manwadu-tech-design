<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

require_once 'db.php'; 

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, name, email, phone, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $logged_in_user = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: /login.php");
    exit();
}
?>
