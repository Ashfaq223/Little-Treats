<?php

require_once __DIR__ . "/includes/bootstrap.php";


// Request must be POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: cart.php");
    exit();

}


// Check required fields
if (!isset($_POST["product_id"]) || !isset($_POST["quantity"])) {

    header("Location: cart.php");
    exit();

}


csrf_verify();

$product_id = (int) $_POST["product_id"];

$quantity = (int) $_POST["quantity"];

// Never allow more than the per-order limit
if ($quantity > MAX_QTY_PER_ITEM) {

    $quantity = MAX_QTY_PER_ITEM;

}


// Product ID must be valid
if ($product_id <= 0) {

    header("Location: cart.php");
    exit();

}


// Cart must exist
if (!isset($_SESSION["cart"])) {

    $_SESSION["cart"] = [];

}


// Product must already be in cart
if (!isset($_SESSION["cart"][$product_id])) {

    header("Location: cart.php");
    exit();

}


// If quantity is zero or negative,
// remove the product from cart
if ($quantity <= 0) {

    unset($_SESSION["cart"][$product_id]);

} else {

    // Update quantity
    $_SESSION["cart"][$product_id] = $quantity;

}


// Return to cart
header("Location: cart.php");
exit();

?>