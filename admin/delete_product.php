<?php
session_start();
require_once '../includes/db.php';

// Ensure admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../store/login.php");
    exit;
}

// Get product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = intval($_GET['id']);

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    header("Location: products.php?msg=Product deleted successfully");
    exit;
} else {
    echo "Error deleting product.";
}
?>
