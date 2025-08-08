<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address_line = $_POST['address_line'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];

    $stmt = $conn->prepare("INSERT INTO addresses (user_id, full_name, phone, address_line, city, province, postal_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssss", $user_id, $full_name, $phone, $address_line, $city, $province, $postal_code);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $address_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $address_id, $user_id);
    $stmt->execute();
}

$stmt = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Addresses | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/addresses-style.css">
</head>
<body>
    <header>
        <h1>My Address Book</h1>
        <nav>
            <a href="account.php">← Back to Account</a>
        </nav>
    </header>

    <section class="addresses">
        <h2>Saved Addresses</h2>
        <?php if ($addresses->num_rows > 0): ?>
            <?php while ($address = $addresses->fetch_assoc()): ?>
                <div class="address-card">
                    <p><strong><?= htmlspecialchars($address['full_name']) ?></strong></p>
                    <p><?= htmlspecialchars($address['phone']) ?></p>
                    <p><?= nl2br(htmlspecialchars($address['address_line'])) ?></p>
                    <p><?= htmlspecialchars($address['city']) ?>, <?= htmlspecialchars($address['province']) ?>, <?= htmlspecialchars($address['postal_code']) ?></p>
                    <a href="addresses.php?delete=<?= $address['id'] ?>" onclick="return confirm('Are you sure you want to delete this address?')">Delete</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No addresses saved.</p>
        <?php endif; ?>
    </section>

    <section class="add-address">
        <h2>Add New Address</h2>
        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <textarea name="address_line" placeholder="Street Address" required></textarea>
            <input type="text" name="city" placeholder="City" required>
            <input type="text" name="province" placeholder="Province" required>
            <input type="text" name="postal_code" placeholder="Postal Code" required>
            <button type="submit" name="add_address">Save Address</button>
        </form>
    </section>
</body>
</html>
