<?php

require_once __DIR__ . "/includes/bootstrap.php";




if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: products.php");
    exit();

}


csrf_verify();


// IDs and quantities are always whole numbers
$product_id = (int) ($_POST["product_id"] ?? 0);
$quantity = (int) ($_POST["quantity"] ?? 1);


if ($quantity < 1) {

    $quantity = 1;

}

if ($quantity > MAX_QTY_PER_ITEM) {

    $quantity = MAX_QTY_PER_ITEM;

}


// Check that the product exists, is not removed and can be ordered
$result = db_select(
    $conn,
    "SELECT product_id, is_available
     FROM products
     WHERE product_id = ?
     AND is_active = 1",
    "i",
    [$product_id]
);


if ($result->num_rows != 1) {

    echo "Product not found.";
    exit();

}

$product = $result->fetch_assoc();


if (!$product["is_available"]) {

    echo "Sorry, this product is currently unavailable.";
    exit();

}


// Create cart if necessary
if (!isset($_SESSION["cart"])) {

    $_SESSION["cart"] = [];

}


// If product already exists, increase quantity (up to the limit)
if (isset($_SESSION["cart"][$product_id])) {

    $_SESSION["cart"][$product_id] = min(
        MAX_QTY_PER_ITEM,
        (int) $_SESSION["cart"][$product_id] + $quantity
    );

} else {

    $_SESSION["cart"][$product_id] = $quantity;

}


// Stay on the product page and confirm with a message
flash_set(
    "success",
    "Added to your cart.",
    "cart.php",
    "View cart"
);

header("Location: product_details.php?id=" . $product_id);
exit();

?>
