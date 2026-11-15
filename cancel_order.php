<?php

require_once __DIR__ . "/includes/auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    redirect("my_orders.php");

}

csrf_verify();

$order_id = (int) ($_POST["order_id"] ?? 0);
$user_id = (int) $_SESSION["user_id"];


// Only the owner can cancel, and only while the order is still Pending
$cancelled = db_exec(
    $conn,
    "UPDATE orders
     SET order_status = 'Cancelled',
         payment_status = IF(payment_status = 'Paid', 'Paid', 'Cancelled')
     WHERE order_id = ?
     AND user_id = ?
     AND order_status = 'Pending'",
    "ii",
    [$order_id, $user_id]
);


if ($cancelled === 1) {

    lt_send_mail(
        (string) $_SESSION["email"],
        "Your order #" . $order_id . " was cancelled",
        "Hello,\n\nYour order #" . $order_id . " at " . SITE_NAME . " has been cancelled.\n\n" .
        "If you already paid, please contact us and we will arrange your refund."
    );

    flash_set("success", "Order #" . $order_id . " has been cancelled.");

} else {

    flash_set("error", "This order can no longer be cancelled. Please contact us.");

}

redirect("my_orders.php");