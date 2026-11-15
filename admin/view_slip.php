<?php

/*
 * Streams a customer's bank-transfer slip to a logged-in admin.
 * Slips are stored outside the public uploads folder.
 */

require_once __DIR__ . "/admin_auth.php";


$order_id = (int) ($_GET["id"] ?? 0);

$order = db_select(
    $conn,
    "SELECT payment_slip FROM orders WHERE order_id = ?",
    "i",
    [$order_id]
)->fetch_assoc();


if (!$order || !$order["payment_slip"]) {

    http_response_code(404);
    exit("No payment slip found.");

}


$file = __DIR__ . "/../private_uploads/slips/" . basename($order["payment_slip"]);

if (!is_file($file)) {

    http_response_code(404);
    exit("No payment slip found.");

}


$types = [
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
    "webp" => "image/webp"
];

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

header("Content-Type: " . ($types[$extension] ?? "application/octet-stream"));
header("Content-Length: " . filesize($file));
header("X-Content-Type-Options: nosniff");
header("Cache-Control: private, no-store");

readfile($file);

?>
