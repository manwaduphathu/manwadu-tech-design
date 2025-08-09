<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle adding new address
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_address'])) {
    $full_name    = trim($_POST['full_name']);
    $phone_number = trim($_POST['phone_number']);
    $address_line = trim($_POST['address_line']);
    $city         = trim($_POST['city']);
    $province     = trim($_POST['province']);
    $postal_code  = trim($_POST['postal_code']);
    $is_default   = isset($_POST['is_default']) ? 1 : 0;

    if ($is_default) {
        // Set all other addresses to not default
        $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
    }

    $stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone_number, address_line, city, province, postal_code, is_default) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $user_id, $full_name, $phone_number, $address_line, $city, $province, $postal_code, $is_default);
    $stmt->execute();
}

// Handle selecting address
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['select_address'])) {
    $selected_address_id = intval($_POST['address_id']);
    $_SESSION['selected_address_id'] = $selected_address_id;

    header("Location: payment.php");
    exit;
}

// Fetch addresses
$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
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
        .address-container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .address-card {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .address-details {
            font-size: 14px;
        }
        .default-label {
            color: green;
            font-weight: bold;
        }
        .add-address-form {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-top: 25px;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #28a745;
            color: #fff;
            padding: 8px 14px;
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
    <div class="address-container">
        <h2>Select Delivery Address</h2>

        <form method="POST">
            <?php if ($addresses->num_rows > 0): ?>
                <?php while ($row = $addresses->fetch_assoc()): ?>
                    <div class="address-card">
                        <label>
                            <input type="radio" name="address_id" value="<?= $row['id'] ?>" required>
                            <div class="address-details">
                                <strong><?= htmlspecialchars($row['full_name']) ?></strong> 
                                <?php if ($row['is_default']): ?>
                                    <span class="default-label">(Default)</span>
                                <?php endif; ?><br>
                                <?= htmlspecialchars($row['phone_number']) ?><br>
                                <?= htmlspecialchars($row['address_line']) ?>, 
                                <?= htmlspecialchars($row['city']) ?>, 
                                <?= htmlspecialchars($row['province']) ?>, 
                                <?= htmlspecialchars($row['postal_code']) ?>
                            </div>
                        </label>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No saved addresses. Please add one below.</p>
            <?php endif; ?>

            <button type="submit" name="select_address">Use Selected Address</button>
        </form>

        <!-- Add New Address -->
        <div class="add-address-form">
            <h3>Add New Address</h3>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="phone_number" placeholder="Phone Number" required>
                <input type="text" name="address_line" placeholder="Address Line" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="province" placeholder="Province" required>
                <input type="text" name="postal_code" placeholder="Postal Code" required>
                <label>
                    <input type="checkbox" name="is_default"> Set as Default
                </label>
                <button type="submit" name="add_address">Add Address</button>
            </form>
        </div>
    </div>
</body>
</html>
