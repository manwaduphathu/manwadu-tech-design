<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ensure delivery option is set
if (!isset($_SESSION['delivery_option'])) {
    header("Location: checkout.php");
    exit;
}

$delivery_option = $_SESSION['delivery_option'];
$address = null;

if ($delivery_option === 'delivery') {
    if (!isset($_SESSION['selected_address_id'])) {
        header("Location: select_address.php");
        exit;
    }
    $address_id = $_SESSION['selected_address_id'];
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $address = $stmt->get_result()->fetch_assoc();
}

// Fetch cart items
$stmt = $conn->prepare("SELECT c.*, p.name, p.price 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total_price = 0;
$total_items = 0;
while ($row = $cart_items->fetch_assoc()) {
    $total_price += $row['price'] * $row['quantity'];
    $total_items += $row['quantity'];
}

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_payment'])) {
    $order_number = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);
    $status = 'pending';
    $delivery_type = ($delivery_option === 'delivery') ? 'home_delivery' : 'collect_in_store';
    $address_id_value = ($delivery_option === 'delivery') ? $address_id : null;

    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_price, status, delivery_type, address_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdssi", $user_id, $order_number, $total_price, $status, $delivery_type, $address_id_value);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert items into order_items
    $stmt_cart = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $cart_data = $stmt_cart->get_result();

    while ($item = $cart_data->fetch_assoc()) {
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt_item->bind_param("iii", $order_id, $item['product_id'], $item['quantity']);
        $stmt_item->execute();
    }

    // Clear cart
    $conn->query("DELETE FROM cart WHERE user_id = $user_id");

    // Redirect to order confirmation
    header("Location: order_success.php?order_number=" . $order_number);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .form-section, .summary-section {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #28a745;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Payment</h2>

        <form method="POST">
            <div class="form-section">
                <h3>Card Details</h3>
                <input type="text" name="card_number" placeholder="Card Number" required>
                <input type="text" name="card_name" placeholder="Name on Card" required>
                <input type="text" name="expiry" placeholder="Expiry (MM/YY)" required>
                <input type="text" name="cvv" placeholder="CVV" required>
            </div>

            <div class="summary-section">
                <h3>Order Summary</h3>
                <p><strong>Items:</strong> <?= $total_items ?></p>
                <p><strong>Total Price:</strong> $<?= number_format($total_price, 2) ?></p>
                <p><strong>Delivery Type:</strong> <?= ($delivery_option === 'delivery') ? 'Home Delivery' : 'Collect in Store' ?></p>
                <?php if ($delivery_option === 'delivery' && $address): ?>
                    <p><strong>Delivery Address:</strong><br>
                    <?= htmlspecialchars($address['full_name']) ?><br>
                    <?= htmlspecialchars($address['phone_number']) ?><br>
                    <?= htmlspecialchars($address['address_line']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['province']) ?>, <?= htmlspecialchars($address['postal_code']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" name="confirm_payment">Confirm Payment / Order</button>
        </form>
    </div>
</body>
</html>
