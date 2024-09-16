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

if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Fetch the current quantity
        $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($quantity);
        $stmt->fetch();
        $stmt->close();

        if ($quantity > 0) {
            // Decrement the quantity
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - 1 WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "Purchase successful!";

        } else {
            echo "Sorry, this product is out of stock.";
        }
    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

$conn->close();
?>
