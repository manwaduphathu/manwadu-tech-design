<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../store/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="add-product.php">Add Product</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="../includes/logout.php">Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>All Orders</h1>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Items</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT o.*, u.full_name AS name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC";

                    $result = $conn->query($query);
                    if ($result->num_rows > 0):
                        while ($order = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['name']) ?></td>
                            <td>R<?= number_format($order['total_price'], 2) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td>
                                <ul>
                                    <?php
                                    $orderId = $order['id'];
                                    $itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
                                    $stmt = $conn->prepare($itemsQuery);
                                    $stmt->bind_param("i", $orderId);
                                    $stmt->execute();
                                    $itemsResult = $stmt->get_result();
                                    while ($item = $itemsResult->fetch_assoc()):
                                    ?>
                                        <li><?= $item['product_name'] ?> (x<?= $item['quantity'] ?>) - R<?= number_format($item['price'], 2) ?></li>
                                    <?php endwhile; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                        echo "<tr><td colspan='6'>No orders found.</td></tr>";
                    endif;
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
