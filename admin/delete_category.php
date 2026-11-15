<?php

require_once __DIR__ . "/admin_auth.php";


// Deleting changes data, so it must be a POST request with a valid token
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: categories.php");
    exit();

}

csrf_verify();


// Only the owner can remove categories
admin_require_owner();

$category_id = (int) ($_POST["id"] ?? 0);


// A category that still has products (including hidden ones kept for
// order history) cannot be removed.
$in_use = db_select(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE category_id = ?",
    "i",
    [$category_id]
)->fetch_assoc();


if ((int) $in_use["total"] > 0) {

    echo "This category still has products. Move or delete its products first.";
    echo '<br><a href="categories.php">Back to Categories</a>';
    exit();

}


db_exec(
    $conn,
    "DELETE FROM categories WHERE category_id = ?",
    "i",
    [$category_id]
);


audit_log($conn, "delete", "category", $category_id, "Category deleted");

header("Location: categories.php");
exit();

?>
