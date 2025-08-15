<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['delivery_option'])) {
    header("Location: checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$delivery_option = $_SESSION['delivery_option'];
$address = null;
$address_id = null;

if ($delivery_option === 'delivery') {
    if (!isset($_SESSION['selected_address_id'])) {
        header("Location: select_address.php");
        exit;
    }
    $address_id = intval($_SESSION['selected_address_id']);
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
    $address = $stmt->get_result()->fetch_assoc();
    if (!$address) {
        header("Location: select_address.php");
        exit;
    }
}

// Fetch cart items + totals
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id
                        WHERE c.user_id = ? AND c.status = 'active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total_price = 0;
$total_items = 0;
$items = [];
while ($row = $cart_items->fetch_assoc()) {
    $row_total = $row['price'] * $row['quantity'];
    $total_price += $row_total;
    $total_items += $row['quantity'];
    $items[] = $row; // cache for insertion after payment
}

if ($total_items === 0) {
    // No items to pay for
    header("Location: cart.php");
    exit;
}

// Confirm “payment”
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // (Demo) Validate form fields minimally
    $card_number = trim($_POST['card_number'] ?? '');
    $card_name   = trim($_POST['card_name'] ?? '');
    $expiry      = trim($_POST['expiry'] ?? '');
    $cvv         = trim($_POST['cvv'] ?? '');

    if ($card_number === '' || $card_name === '' || $expiry === '' || $cvv === '') {
        $error = "Please complete all card fields.";
    } else {
        // Create order
        $order_number = str_pad((string)random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $status = 'pending';
        $delivery_type = ($delivery_option === 'delivery') ? 'home_delivery' : 'collect_in_store';

        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_price, status, delivery_type, address_id, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, NOW())");
        // address_id may be null; use i (int) but pass null correctly
        $addr = ($delivery_type === 'home_delivery') ? $address_id : null;
        $stmt->bind_param("isdssi", $user_id, $order_number, $total_price, $status, $delivery_type, $addr);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // Insert order items
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($items as $it) {
            $pid = (int)$it['product_id'];
            $qty = (int)$it['quantity'];
            $prc = (float)$it['price'];
            $stmtItem->bind_param("iiid", $order_id, $pid, $qty, $prc);
            $stmtItem->execute();
        }

        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND status = 'active'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Clean up checkout session data
        unset($_SESSION['delivery_option'], $_SESSION['selected_address_id']);

        header("Location: order_success.php?order_number=" . urlencode($order_number));
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
    <style>
        .payment-wrap { max-width: 1000px; margin: 40px auto; display: grid; grid-template-columns: 1.1fr .9fr; gap: 20px; }
        .card, .summary { background: #fff; border-radius: 10px; padding: 20px; }
        .field { margin-bottom: 12px; }
        .field input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; }
        .btn-primary { background: #0a1d3a; color: #fff; padding: 12px 16px; border: 0; border-radius: 8px; cursor: pointer; width: 100%; }
        .btn-primary:hover { opacity: .9; }
        .addr { font-size: 14px; color: #333; line-height: 1.5; }
        .items-mini { margin-top: 10px; border-top: 1px dashed #ddd; padding-top: 10px; max-height: 200px; overflow: auto; }
        .items-mini div { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
        .error { color: #b00020; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="payment-wrap">
        <form method="POST" class="card">
            <h2>Card Payment</h2>
            <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <div class="field"><input type="text" name="card_number" placeholder="Card Number" maxlength="19" required></div>
            <div class="field"><input type="text" name="card_name" placeholder="Name on Card" required></div>
            <div class="field"><input type="text" name="expiry" placeholder="Expiry (MM/YY)" required></div>
            <div class="field"><input type="text" name="cvv" placeholder="CVV" maxlength="4" required></div>
            <button type="submit" name="confirm_payment" class="btn-primary">Confirm Payment / Order</button>
        </form>

        <div class="summary">
            <h2>Order Summary</h2>
            <p><strong>Items:</strong> <?= (int)$total_items ?></p>
            <p><strong>Total Price:</strong> R<?= number_format($total_price, 2) ?></p>
            <p><strong>Delivery Type:</strong> <?= ($delivery_option === 'delivery') ? 'Home Delivery' : 'Collect in Store' ?></p>
            <?php if ($delivery_option === 'delivery' && $address): ?>
                <div class="addr">
                    <strong>Deliver To:</strong><br>
                    <?= htmlspecialchars($address['full_name']) ?><br>
                    <?= htmlspecialchars($address['phone_number']) ?><br>
                    <?= htmlspecialchars($address['address_line']) ?>, <?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['province']) ?>, <?= htmlspecialchars($address['postal_code']) ?>
                </div>
            <?php endif; ?>

            <div class="items-mini">
                <?php foreach ($items as $it): ?>
                    <div>
                        <span><?= htmlspecialchars($it['name']) ?> × <?= (int)$it['quantity'] ?></span>
                        <span>R<?= number_format($it['price'] * $it['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:12px;">
                <a href="cart.php">← Back to cart</a>
            </div>
        </div>
    </div>
</body>
</html>
