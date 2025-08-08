<?php
session_start();
// Include DB connection
include_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | Manwadu Tech & Design</title>
    <link rel="stylesheet" href="../style/shop-style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo"> <img src="../assets/logo.png" alt=""></div>
        <nav>
            <ul>
                <li><a href="../index.html">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="dropdown">
                        <a href="#">Account</a>
                        <ul class="dropdown-menu">
                            <li><a href="orders.php">My Orders</a></li>
                            <li><a href="track.php">Track Order</a></li>
                            <li><a href="settings.php">Settings</a></li>
                            <li><a href="addresses.php">Address Book</a></li>
                            <li><a href="../includes/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Search Bar -->
    <section class="search-bar">
        <form method="GET" action="shop.php">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
    </section>

    <!-- Product Listing -->
    <section class="products">
        <h2>Available Tech Products</h2>
        <div class="product-grid">
        <?php
            $search = $_GET['search'] ?? '';
            $query = "SELECT * FROM products WHERE name LIKE ?";
            $stmt = $conn->prepare($query);
            $likeSearch = "%$search%";
            $stmt->bind_param("s", $likeSearch);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
        ?>
            <div class="product-card">
                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p>R<?= number_format($row['price'], 2) ?></p>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php
                endwhile;
            else:
                echo "<p>No products found.</p>";
            endif;
        ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Manwadu Tech & Design. All rights reserved.</p>
    </footer>
</body>
</html>
