<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../store/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $orderId = intval($_POST['order_id']);
    $status = $_POST['status'];

    $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update->bind_param("si", $status, $orderId);

    if ($update->execute()) {
        $message = "✅ Order #$orderId status updated to '$status'.";
    } else {
        $message = "❌ Failed to update order.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/admin-style.css">
    <style>
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
        .status-form {
            display: flex;
            gap: 5px;
        }
        select, button {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #2c3e50;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #1a252f;
        }
        .message {
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="add-product.php">Add Product</a></li>
            <li><a href="products.php">View Products</a></li>
            <li><a href="orders.php" style="background-color: #34495e;">Orders</a></li>
            <li><a href="../includes/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h1>All Orders</h1>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT o.*, u.full_name AS name 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          ORDER BY o.created_at DESC";

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
                                    <li><?= $item['product_id'] ?> (x<?= $item['quantity'] ?>) - R<?= number_format($item['price'], 2) ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="ready for collection" <?= $order['status'] === 'ready for collection' ? 'selected' : '' ?>>Ready for Collection</option>
                                    <option value="out for delivery" <?= $order['status'] === 'out for delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php
                    endwhile;
                else:
                    echo "<tr><td colspan='7'>No orders found.</td></tr>";
                endif;
                ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
