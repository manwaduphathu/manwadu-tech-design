<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $checkQuery = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND status = 'active'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $updateQuery = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $cart_id = intval($_POST['cart_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
    }
    header("Location: cart.php");
    exit;
}

// Handle removal of selected items
if (isset($_POST['remove_selected'])) {
    if (!empty($_POST['remove_ids'])) {
        $ids = implode(',', array_map('intval', $_POST['remove_ids']));
        $conn->query("DELETE FROM cart WHERE user_id = $user_id AND id IN ($ids)");
    }
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$query = "SELECT c.*, p.name, p.price, p.image 
          FROM cart c
          JOIN products p ON c.product_id = p.id
          WHERE c.user_id = ? AND c.status = 'active'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cartItems = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
</head>
<body>
<header>
    <div class="logo"><img src="../assets/logo.png" alt=""></div>
    <nav>
        <ul>
            <li><a href="../index.html">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="cart.php">Cart</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../includes/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<section class="products">
    <h1>My Shopping Cart</h1>

    <?php if ($cartItems->num_rows > 0): ?>
        <form method="POST" action="cart.php">
            <table>
                <tr>
                    <th>Remove</th>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
                <?php
                $grandTotal = 0;
                while ($item = $cartItems->fetch_assoc()):
                    $total = $item['price'] * $item['quantity'];
                    $grandTotal += $total;
                ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="remove_ids[]" value="<?= $item['id'] ?>">
                        </td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><img src="../uploads/<?= htmlspecialchars($item['image']) ?>" width="50"></td>
                        <td>R<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form method="POST" action="cart.php" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width:50px;">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>R<?= number_format($total, 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <h3>Grand Total: R<?= number_format($grandTotal, 2) ?></h3>
            <button type="submit" name="remove_selected">Remove Selected</button>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </form>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</section>

<footer>
    <p>&copy; 2025 Manwadu Tech & Design. All rights reserved.</p>
</footer>
</body>
</html>
