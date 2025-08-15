<?php
session_start();
include_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../store/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category    = trim($_POST['category']);
    $price       = floatval($_POST['price']);
    $stock       = intval($_POST['stock']);
    $image       = $_FILES['image'];

    // Basic validation
    if ($name && $description && $category && $price > 0 && $stock >= 0 && $image['name']) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($image['type'], $allowedTypes)) {
            $message = "❌ Invalid image type. Please upload JPG, PNG, or WEBP.";
        } elseif ($image['size'] > 5 * 1024 * 1024) { // 5MB limit
            $message = "❌ Image is too large. Max size is 5MB.";
        } else {
            $targetDir  = "../uploads/";
            $imageName  = uniqid() . "_" . basename($image["name"]);
            $targetFile = $targetDir . $imageName;

            if (move_uploaded_file($image["tmp_name"], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO products (name, description, category, price, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdis", $name, $description, $category, $price, $stock, $imageName);

                if ($stmt->execute()) {
                    $_SESSION['flash_message'] = "✅ Product added successfully.";
                    header("Location: products.php");
                    exit;
                } else {
                    $message = "❌ Error inserting into database.";
                }
            } else {
                $message = "❌ Failed to upload image.";
            }
        }
    } else {
        $message = "❌ Please fill all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Admin</title>
    <link rel="stylesheet" href="../style/admin-style.css">
    <style>
        .admin-layout { display: flex; }
        .add-product-container { flex: 1; padding: 40px; background-color: #f9f9f9; }
        h1 { font-size: 28px; margin-bottom: 20px; color: #2c3e50; }
        form { background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 600px; }
        label { font-weight: bold; display: block; margin-top: 20px; margin-bottom: 5px; }
        input[type="text"], input[type="number"], textarea, input[type="file"], select {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px;
        }
        button { margin-top: 20px; background-color: #2c3e50; color: white; border: none; padding: 12px 20px; border-radius: 5px; font-size: 16px; cursor: pointer; }
        button:hover { background-color: #1a252f; }
        .message { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">All Users</a></li>
            <li><a href="add_product.php" style="background-color: #34495e;">Add Product</a></li>
            <li><a href="products.php">View Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="../store/shop.php">Back to Shop</a></li>
        </ul>
    </div>

    <div class="add-product-container">
        <h1>Add New Product</h1>
        <form method="post" enctype="multipart/form-data">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required></textarea>

            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">-- Select Category --</option>
                <option value="Electronics">Electronics</option>
                <option value="Clothing">Clothing</option>
                <option value="Home & Kitchen">Home & Kitchen</option>
                <option value="Other">Other</option>
            </select>

            <label for="price">Price (R)</label>
            <input type="number" step="0.01" id="price" name="price" required>

            <label for="stock">Stock Quantity</label>
            <input type="number" id="stock" name="stock" min="0" required>

            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <button type="submit">Add Product</button>
            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
        </form>
    </div>
</div>
</body>
</html>
