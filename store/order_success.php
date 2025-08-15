<?php
session_start();
require_once '../includes/db.php';

// Check if order_number is passed
if (!isset($_GET['order_number'])) {
    header("Location: shop.php");
    exit;
}

$order_number = $_GET['order_number'];

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.id, o.order_number, o.total_price, o.status, o.delivery_type, o.address_id, o.created_at,
           a.full_name, a.phone_number, a.address_line, a.city, a.province, a.postal_code
    FROM orders o
    LEFT JOIN addresses a ON o.address_id = a.id
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->bind_param("si", $order_number, $_SESSION['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit;
}

$order = $order_result->fetch_assoc();

// Fetch order items
$item_stmt = $conn->prepare("
    SELECT p.name, p.price, oi.quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$item_stmt->bind_param("i", $order['id']);
$item_stmt->execute();
$item_result = $item_stmt->get_result();

$order_items = [];
while ($row = $item_result->fetch_assoc()) {
    $order_items[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Success | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
    <style>
        .receipt-container {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
        }
        h1 {
            color: green;
            text-align: center;
        }
        .order-info, .address-info, .items-table {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            text-align: right;
        }
        .btn {
            display: block;
            width: 200px;
            margin: 20px auto 0;
            padding: 10px;
            background: #28a745;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <h1>Order Successful 🎉</h1>
    <p style="text-align:center;">Thank you for your purchase!</p>

    <div class="order-info">
        <p><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
        <p><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
        <p><strong>Delivery Option:</strong> <?php echo ucfirst($order['delivery_type']); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
    </div>

    <?php if ($order['delivery_type'] === 'delivery' && $order['address_line']): ?>
    <div class="address-info">
        <h3>Delivery Address</h3>
        <p>
            <?php echo $order['full_name']; ?><br>
            <?php echo $order['phone_number']; ?><br>
            <?php echo $order['address_line']; ?><br>
            <?php echo $order['city'] . ", " . $order['province'] . " " . $order['postal_code']; ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="items-table">
        <h3>Items Ordered</h3>
        <table>
            <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
            <?php foreach ($order_items as $item): ?>
            <tr>
                <td><?php echo $item['name']; ?></td>
                <td>R<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p class="total">Total: R<?php echo number_format($order['total_price'], 2); ?></p>
    </div>

    <a href="shop.php" class="btn">Continue Shopping</a>
</div>

</body>
</html>
