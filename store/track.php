<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$orderDetails = null;
$error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);

    // Fetch order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $orderDetails = $result->fetch_assoc();

        // Fetch order items
        $items = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
        $orderItems = [];
        while ($item = $items->fetch_assoc()) {
            $orderItems[] = $item;
        }
    } else {
        $error = "Order not found or does not belong to you.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Track My Order</title>
    <link rel="stylesheet" href="../style/track-style.css">
    <link rel="stylesheet" href="../style/addresses-style.css">

</head>
<body>
    <header>
        <h1>Track Your Order</h1>
        <nav>
            <a href="shop.php">← Back to shop</a>
        </nav>
    </header>
    
    <div class="track-container">

        <form method="POST" class="track-form">
            <label for="order_id">Enter Order ID:</label>
            <input type="number" name="order_id" id="order_id" required>
            <button type="submit">Track</button>
        </form>

        <?php if ($error): ?>
            <p class="error"><?= $error; ?></p>
        <?php endif; ?>

        <?php if ($orderDetails): ?>
            <div class="order-info">
                <h3>Order #<?= $orderDetails['id']; ?></h3>
                <p><strong>Status:</strong> <?= ucfirst($orderDetails['status']); ?></p>
                <p><strong>Order Date:</strong> <?= date("F j, Y, g:i a", strtotime($orderDetails['created_at'])); ?></p>
                <p><strong>Total Price:</strong> R<?= number_format($orderDetails['total_price'], 2); ?></p>

                <div class="items">
                    <h4>Items:</h4>
                    <ul>
                        <?php foreach ($orderItems as $item): ?>
                            <li><?= $item['product_name']; ?> x <?= $item['quantity']; ?> (R<?= $item['price']; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
