<?php
// products.php - Admin product management page
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../store/login.php");
    exit;
}

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../style/admin-style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: auto; padding: 20px; background: white; border-radius: 8px; }
        h1 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        table th { background: #2c3e50; color: white; }
        .btn { padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; color: white; }
        .btn-add { background: #28a745; }
        .btn-edit { background: #007bff; }
        .btn-delete { background: #dc3545; }
        .actions { display: flex; gap: 10px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">All Users</a></li>
            <li><a href="add-product.php" >Add Product</a></li>
            <li><a href="orders.php" style="background-color: #34495e;">View Orders</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="../store/shop.php">Back to Shop</a></li>
        </ul>
    </div>
    <div class="container">
   
    <div class="top-bar">
        <h1>Manage Products</h1>
        <a href="add-product.php" class="btn btn-add">+ Add New Product</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Date Added</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="60"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>R<?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td><?= date("Y-m-d", strtotime($row['created_at'])) ?></td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No products found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div>


</body>
</html>
