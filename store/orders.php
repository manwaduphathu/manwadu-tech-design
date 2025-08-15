<?php
session_start();
require_once '../includes/db.php';
//require_once '../includes/auth.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="../style/orders-style.css">
    <link rel="stylesheet" href="../style/addresses-style.css">
</head>
<body>
    <header>
        <h1>My Orders</h1>
        <nav>
            <a href="shop.php">← Back to shop</a>
        </nav>
    </header>
    <div class="orders-container">
        

        <?php if ($result->num_rows > 0): ?>
            <?php while ($order = $result->fetch_assoc()): ?>
                <div class="order-card">
                    <h3>Order Number: <?= $order['order_number']; ?></h3>
                    <p><strong>Status:</strong> <?= ucfirst($order['status']); ?></p>
                    <p><strong>Order Date:</strong> <?= date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
                    <p><strong>Total Price:</strong> R<?= number_format($order['total_price'], 2); ?></p>

                    <div class="order-items">
                        <h4>Items:</h4>
                        <ul>
                            <?php
                                $order_id = $order['id'];
                                $items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
                                while ($item = $items->fetch_assoc()):
                            ?>
                                <li><?= $item['product_id']; ?> x <?= $item['quantity']; ?> (R<?= $item['price']; ?>)</li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have no orders yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
