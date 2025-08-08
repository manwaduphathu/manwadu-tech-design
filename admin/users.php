<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../store/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";;
// Fetch all users
$stmt = $conn->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users - Admin Panel</title>
    <link rel="stylesheet" href="../style/admin-style.css">
    <style>
        .users-container {
            padding: 40px;
            background-color: #f9f9f9;
            flex: 1;
        }

        .users-container h1 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-deleted {
            color: red;
            font-weight: bold;
        }

        .admin-layout {
            display: flex;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php" style="background-color: #34495e;">All Users</a></li>
            <li><a href="add-product.php">Add Product</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="../index.php">Back to Shop</a></li>
        </ul>
    </div>

    <div class="users-container">
        <h1>Registered Users</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Names</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td class="<?= $user['status'] === 'active' ? 'status-active' : 'status-deleted' ?>">
                                <?= htmlspecialchars($user['status']) ?>
                            </td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
