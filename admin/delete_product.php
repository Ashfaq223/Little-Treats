<?php

require_once __DIR__ . "/admin_auth.php";


// Deleting changes data, so it must be a POST request with a valid token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: products.php");
    exit();

}

csrf_verify();


// Only the owner can remove products
admin_require_owner();

$product_id = (int) ($_POST["id"] ?? 0);


// The product is hidden (soft delete) instead of being erased, so old
// orders keep their product history and the image file is kept.
db_exec(
    $conn,
    "UPDATE products
     SET is_active = 0
     WHERE product_id = ?",
    "i",
    [$product_id]
);


audit_log($conn, "delete", "product", $product_id, "Product hidden (soft delete)");

header("Location: products.php");
exit();

?>
