<?php
session_start();
require_once '../includes/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../store/login.php");
    exit;
}

// Get total users
$totalUsers = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];

// Get deleted users
$deletedUsers = $conn->query("SELECT COUNT(*) AS count FROM users WHERE deleted = 1")->fetch_assoc()['count'] ?? 0;

// Total orders
$totalOrders = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];

// Total revenue
$totalRevenueResult = $conn->query("SELECT SUM(total_price) AS revenue FROM orders");
$totalRevenue = $totalRevenueResult->fetch_assoc()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/admin-style.css">
</head>
<body>
    <div class="admin-container">
        
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">View All Users</a></li>
                <li><a href="add-product.php">Add Product</a></li>
                <li><a href="orders.php">View Orders</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="dashboard">
            <h1>Welcome, Admin</h1>
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?= $totalUsers ?></p>
                </div>
                <div class="stat-card">
                    <h3>Deleted Accounts</h3>
                    <p><?= $deletedUsers ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p><?= $totalOrders ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <p>R<?= number_format($totalRevenue, 2) ?></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
