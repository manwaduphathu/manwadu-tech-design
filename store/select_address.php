<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['delivery_option']) || $_SESSION['delivery_option'] !== 'delivery') {
    header("Location: checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Add new address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $full_name    = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $address_line = trim($_POST['address_line']);
    $city         = trim($_POST['city']);
    $province     = trim($_POST['province']);
    $postal_code  = trim($_POST['postal_code']);
    $is_default   = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default) {
        $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    $stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone_number, address_line, city, province, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $full_name, $phone_number, $address_line, $city, $province, $postal_code, $is_default);
    if ($stmt->execute()) {
        $message = "Address added.";
    } else {
        $message = "Failed to add address.";
    }
}

// Select existing address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_address'])) {
    $selected_id = intval($_POST['address_id']);
    // Validate it belongs to this user
    $stmt = $conn->prepare("SELECT id FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $selected_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $_SESSION['selected_address_id'] = $selected_id;
        header("Location: payment.php");
        exit;
    } else {
        $message = "Invalid address selection.";
    }
}

// Pull addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Address | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
    <style>
        .address-container { max-width: 900px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .address-list, .add-address { background: #fff; border-radius: 10px; padding: 20px; }
        .address-card { border: 1px solid #e6e6e6; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
        .address-card label { display: flex; gap: 10px; cursor: pointer; }
        .default { color: green; font-weight: 600; }
        .btn { background: #0a1d3a; color: #fff; padding: 10px 14px; border: 0; border-radius: 6px; cursor: pointer; }
        .btn:hover { opacity: .9; }
        .muted { color: #777; font-size: 14px; }
        .msg { margin-bottom: 10px; color: #0a1d3a; }
        input[type=text] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="address-container">
        <div class="address-list">
            <h2>Select Delivery Address</h2>
            <?php if (!empty($message)): ?>
                <p class="msg"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST">
                <?php if ($addresses->num_rows > 0): ?>
                    <?php while ($row = $addresses->fetch_assoc()): ?>
                        <div class="address-card">
                            <label>
                                <input type="radio" name="address_id" value="<?= $row['id'] ?>" required>
                                <div>
                                    <strong><?= htmlspecialchars($row['full_name']) ?></strong>
                                    <?php if ($row['is_default']): ?><span class="default">(Default)</span><?php endif; ?><br>
                                    <?= htmlspecialchars($row['phone_number']) ?><br>
                                    <?= htmlspecialchars($row['address_line']) ?>, <?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['province']) ?>, <?= htmlspecialchars($row['postal_code']) ?>
                                </div>
                            </label>
                        </div>
                    <?php endwhile; ?>
                    <button type="submit" name="select_address" class="btn">Use Selected Address</button>
                <?php else: ?>
                    <p class="muted">No saved addresses. Please add one.</p>
                <?php endif; ?>
            </form>
        </div>

        <div class="add-address">
            <h2>Add New Address</h2>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <input type="text" name="address_line" placeholder="Address Line" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="province" placeholder="Province" required>
                <input type="text" name="postal_code" placeholder="Postal Code" required>
                <label><input type="checkbox" name="is_default"> Set as default</label>
                <br><br>
                <button type="submit" name="add_address" class="btn">Add Address</button>
            </form>
        </div>
    </div>
</body>
</html>
