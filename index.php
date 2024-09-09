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
    $image = '';
    $image_type = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        $image_type = $_FILES['image']['type'];  // Store image type (e.g., 'image/jpeg')
    }

    $sql = "INSERT INTO products (product_name, quantity, price, description, image, image_type) 
            VALUES ('$product_name', '$quantity', '$price', '$description', '$image', '$image_type')";
    if ($conn->query($sql) === TRUE) {
        $success_message = "New product added successfully";
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
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
        $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        $image_type = $_FILES['image']['type'];
    }

    $sql = "UPDATE products SET product_name='$product_name', quantity='$quantity', price='$price', description='$description', image='$image', image_type='$image_type' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Product updated successfully";
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Delete Product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        $success_message = "Product deleted successfully";
    } else {
        $error_message = "Error: " . $sql . "<br>" . $conn->error;
    }
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
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Inventory Management</h1>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= $success_message ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?= $error_message ?>
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
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['product_name'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['price'] ?></td>
                    <td><?= $row['description'] ?></td>
                    <td>
                        <?php if ($row['image']): ?>
                            <img src="data:<?= $row['image_type'] ?>;base64,<?= base64_encode($row['image']) ?>" alt="<?= $row['product_name'] ?>">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Edit Product -->
        <?php if (isset($_GET['edit'])): 
            $id = $_GET['edit'];
            $product = $conn->query("SELECT * FROM products WHERE id='$id'")->fetch_assoc();
        ?>
        <h2 class="mt-4">Edit Product</h2>
        <form method="post" enctype="multipart/form-data" class="mt-3">
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
            <div class="form-group">
                <input type="text" class="form-control" name="product_name" value="<?= $product['product_name'] ?>" required>
            </div>
            <div class="form-group">
                <input type="number" class="form-control" name="quantity" value="<?= $product['quantity'] ?>" required>
            </div>
            <div class="form-group">
                <input type="number" step="0.01" class="form-control" name="price" value="<?= $product['price'] ?>" required>
            </div>
            <div class="form-group">
                <textarea class="form-control" name="description"><?= $product['description'] ?></textarea>
            </div>
            <div class="form-group">
                <input type="hidden" name="existing_image" value="<?= $product['image'] ?>">
                <input type="hidden" name="existing_image_type" value="<?= $product['image_type'] ?>">
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
