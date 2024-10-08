<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'inventory_db';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle deletion of items from the cart
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $product_id = intval($_POST['product_id']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        if (empty($_SESSION['cart'])) {
            unset($_SESSION['cart']); // Clear cart if empty
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission issues
    exit();
}

// Handle quantity update
if (isset($_POST['action']) && ($_POST['action'] === 'increase' || $_POST['action'] === 'decrease')) {
    $product_id = intval($_POST['product_id']);
    $action = $_POST['action'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        if ($action === 'increase') {
            $_SESSION['cart'][$product_id]++;
        } elseif ($action === 'decrease') {
            if ($_SESSION['cart'][$product_id] > 1) {
                $_SESSION['cart'][$product_id]--;
            } else {
                unset($_SESSION['cart'][$product_id]);
                if (empty($_SESSION['cart'])) {
                    unset($_SESSION['cart']); // Clear cart if empty
                }
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission issues
    exit();
}

// Fetch Cart Items
$cart_items = [];
$total_cost = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $ids = implode(',', $product_ids);
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");

    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_cost += $row['price'] * $_SESSION['cart'][$row['id']];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .cart-item img {
            max-height: 100px;
            object-fit: cover;
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

    <div class="container mt-4">
        <h2>Your Cart</h2>
        <?php if (!empty($cart_items)) : ?>
            <?php foreach ($cart_items as $item) : ?>
                <div class="cart-item d-flex align-items-center">
                    <img src="data:<?php echo htmlspecialchars($item['image_type']); ?>;base64,<?php echo base64_encode($item['image']); ?>" class="mr-3" alt="Product Image">
                    <div>
                        <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <p>$<?php echo htmlspecialchars($item['price']); ?></p>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="decrease">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-warning btn-sm">-</button>
                        </form>
                        <span class="mx-2">Quantity: <?php echo $_SESSION['cart'][$item['id']]; ?></span>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="increase">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">+</button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <h3>Total Cost: $<?php echo number_format($total_cost, 2); ?></h3>
            <a href="checkout.php" class="btn btn-primary mt-3">Checkout</a>
        <?php else : ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>