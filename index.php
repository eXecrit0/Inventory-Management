<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inventory_db';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$success_message = '';
$error_message = '';

// Add Product with Image Upload
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    
    // Handle image upload
    $image = null;
    $image_type = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $image_type = $_FILES['image']['type'];
    }

    // Use prepared statements to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO products (product_name, quantity, price, description, image, image_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siisss", $product_name, $quantity, $price, $description, $image, $image_type);

    if ($stmt->execute()) {
        $success_message = "New product added successfully";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Update Product
if (isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Handle image update
    $image = $_POST['existing_image'];
    $image_type = $_POST['existing_image_type'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $image_type = $_FILES['image']['type'];
    }

    // Use prepared statements to avoid SQL injection
    $stmt = $conn->prepare("UPDATE products SET product_name=?, quantity=?, price=?, description=?, image=?, image_type=? WHERE id=?");
    $stmt->bind_param("siisssi", $product_name, $quantity, $price, $description, $image, $image_type, $id);

    if ($stmt->execute()) {
        $success_message = "Product updated successfully";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Use prepared statements to avoid SQL injection
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $success_message = "Product deleted successfully";
    } else {
        $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch Products
$products = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        img {
            width: 100px;
            height: 100px;
        }
        .navbar-brand {
            font-size: 25px;
        }
        .nav-item {
            font-size: 15px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Shopping Inventory</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="item_display.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">Cart</a>
                </li>
            </ul>
        </div>
    </nav>
              
    <div class="container">
        <h1 class="mt-5">Inventory Management</h1>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <!-- Add New Product -->
        <h2 class="mt-4">Add New Product</h2>
        <form method="post" enctype="multipart/form-data" class="mt-3">
            <div class="form-group">
                <input type="text" class="form-control" name="product_name" placeholder="Product Name" required>
            </div>
            <div class="form-group">
                <input type="number" class="form-control" name="quantity" placeholder="Quantity" required>
            </div>
            <div class="form-group">
                <input type="number" step="0.01" class="form-control" name="price" placeholder="Price" required>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description" placeholder="Product Description"></textarea>
            </div>
            <div class="form-group">
                <input type="file" class="form-control-file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary" name="add_product">Add Product</button>
        </form>

        <!-- Product List -->
        <h2 class="mt-4">Product List</h2>
        <table class="table table-bordered mt-3">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td><?= htmlspecialchars($row['price']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <?php if ($row['image']): ?>
                            <img src="data:<?= htmlspecialchars($row['image_type']) ?>;base64,<?= base64_encode($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit=<?= htmlspecialchars($row['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete=<?= htmlspecialchars($row['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Edit Product -->
        <?php if (isset($_GET['edit'])): 
            $id = $_GET['edit'];
            $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        ?>
        <h2 class="mt-4">Edit Product</h2>
        <form method="post" enctype="multipart/form-data" class="mt-3">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
            <div class="form-group">
                <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
            </div>
            <div class="form-group">
                <input type="number" class="form-control" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required>
            </div>
            <div class="form-group">
                <input type="number" step="0.01" class="form-control" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <div class="form-group">
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image']) ?>">
                <input type="hidden" name="existing_image_type" value="<?= htmlspecialchars($product['image_type']) ?>">
                <input type="file" class="form-control-file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary" name="update_product">Update Product</button>
        </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
