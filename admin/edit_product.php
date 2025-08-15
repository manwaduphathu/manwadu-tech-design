<?php
session_start();
require_once '../includes/db.php';

// Ensure admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../store/login.php");
    exit;
}

// Get product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid product ID.");
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);
    $category    = trim($_POST['category']);
    $stock       = intval($_POST['stock']); // Optional column

    // Update image if new one is uploaded
    $image_sql = "";
    if (!empty($_FILES['image']['name'])) {
        $image_path = "uploads/" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        $image_sql = ", image='$image_path'";
    }

    $update_sql = "UPDATE products 
                   SET name=?, description=?, price=?, category=?, stock=? $image_sql 
                   WHERE id=?";
    $stmt = $conn->prepare($update_sql);

    if ($image_sql) {
        $stmt->bind_param("ssdsii", $name, $description, $price, $category, $stock, $product_id);
    } else {
        $stmt->bind_param("ssdsii", $name, $description, $price, $category, $stock, $product_id);
    }

    if ($stmt->execute()) {
        header("Location: products.php?msg=Product updated successfully");
        exit;
    } else {
        echo "Error updating product.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="../style/admin-style.css">
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="products.php">Manage Products</a></li>
            <li><a href="../includes/logout.php">Logout</a></li>
        </ul>
    </aside>

    <main>
        <h1>Edit Product</h1>
        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label>Description:</label>
            <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

            <label>Price:</label>
            <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>

            <label>Category:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

            <label>Stock:</label>
            <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

            <label>Product Image:</label>
            <input type="file" name="image">
            <p>Current Image:</p>
            <img src="../<?= $product['image'] ?>" width="120">

            <button type="submit">Update Product</button>
        </form>
    </main>
</div>
</body>
</html>
