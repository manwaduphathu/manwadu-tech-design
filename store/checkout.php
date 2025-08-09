<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delivery_option'])) {
    $deliveryOption = $_POST['delivery_option'];

    if ($deliveryOption === 'delivery') {
        header("Location: select_address.php");
        exit;
    } elseif ($deliveryOption === 'collect') {
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
</head>
<body>
    <div class="checkout-container">
        <header>
            <h1>Checkout</h1>
            <nav>
                <a href="cart.php">← Back to cart</a>
            </nav>
        </header>
        <div class="delivery-options">
            <form method="POST" action="">
                <label>
                    <input type="radio" name="delivery_option" value="collect" required> Collect in Store
                </label>
                <label>
                    <input type="radio" name="delivery_option" value="delivery" required> Home Delivery
                </label>
                <button type="submit">Continue</button>
            </form>
        </div>
    </div>
    
    
   
</body>
</html>
