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

// Fetch Products
$products = $conn->query("SELECT * FROM products");

if ($products === FALSE) {
    die("Error fetching products: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .product-card img {
            max-height: 200px;
            object-fit: cover;
        }
        .carousel-inner img {
            width: 100%;
            height: auto;
        }
        .product-card .card-body {
            padding: 15px;
        }
        .product-card .card-title {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .product-card .card-text {
            color: #555;
        }
        .product-card .price {
            font-size: 1.5rem;
            color: #007bff;
            margin-top: 0.5rem;
        }
        .product-card .btn {
            margin-top: 1rem;
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
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <?php if ($products->num_rows > 0) : ?>
                <?php while ($product = $products->fetch_assoc()) : ?>
                    <div class="col-md-4">
                        <div class="product-card card">
                            <div id="carouselExampleRide<?php echo $product['id']; ?>" class="carousel slide" data-ride="carousel">
                                <div class="carousel-inner">
                                    <?php
                                    // Display image if available
                                    if (!empty($product['image'])) {
                                        echo '<div class="carousel-item active">';
                                        echo '<img src="data:' . htmlspecialchars($product['image_type']) . ';base64,' . base64_encode($product['image']) . '" class="d-block w-100" alt="Product Image">';
                                        echo '</div>';
                                    } else {
                                        echo '<div class="carousel-item active">';
                                        echo '<img src="path/to/default-image.jpg" class="d-block w-100" alt="Default Image">';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <!-- Carousel controls -->
                                <a class="carousel-control-prev" href="#carouselExampleRide<?php echo $product['id']; ?>" role="button" data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#carouselExampleRide<?php echo $product['id']; ?>" role="button" data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                            <div  id="container" class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                                <a href="#" class="btn btn-primary" id="no">Add to Cart</a>
                                <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-success" onclick="return confirm('Kupal kaba boss!!')">Buy</a>
                               
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p class="text-center">No products found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
       
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
