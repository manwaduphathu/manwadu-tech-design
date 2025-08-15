<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_option'])) {
    $_SESSION['delivery_option'] = $_POST['delivery_option'];

    if ($_POST['delivery_option'] === 'delivery') {
        header("Location: select_address.php");
        exit;
    } else {
        // collect in store
        unset($_SESSION['selected_address_id']); // not needed for collect
        header("Location: payment.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Checkout | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
    <style>
        .checkout-container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 10px; }
        .delivery-options { margin-top: 20px; }
        .delivery-options label { display: block; padding: 12px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 10px; cursor: pointer; }
        .delivery-options input { margin-right: 8px; }
        .btn-primary { background: #0a1d3a; color: #fff; padding: 10px 16px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-primary:hover { opacity: .9; }
        .back-link { display: inline-block; margin-bottom: 10px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="checkout-container">
        <a class="back-link" href="cart.php">← Back to cart</a>
        <h1>Checkout</h1>
        <p>Select how you would like to receive your order.</p>

        <form method="POST" class="delivery-options">
            <label>
                <input type="radio" name="delivery_option" value="collect" required>
                Collect in Store
            </label>
            <label>
                <input type="radio" name="delivery_option" value="delivery" required>
                Home Delivery
            </label>
            <button type="submit" class="btn-primary">Continue</button>
        </form>
    </div>
</body>
</html>
