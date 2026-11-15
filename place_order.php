<?php

require_once __DIR__ . "/includes/bootstrap.php";



require_once __DIR__ . "/includes/uploads.php";


// Customer must be logged in
if (!isset($_SESSION["user_id"])) {

    remember_next("checkout.php");

    header("Location: login.php");
    exit();

}


// Orders can only be placed from the checkout form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: checkout.php");
    exit();

}

csrf_verify();


// Cart cannot be empty
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {

    header("Location: cart.php");
    exit();

}


// Show a problem on the checkout page
function checkout_error(string $text): void
{
    $_SESSION["checkout_error"] = $text;

    header("Location: checkout.php");
    exit();
}


$user_id = (int) $_SESSION["user_id"];

$payment_method = $_POST["payment_method"] ?? "";


// Allow only valid payment methods
// (no online card gateway is connected, so card payment is not offered)
$allowed_payment_methods = PAYMENT_METHODS;


if (!in_array($payment_method, $allowed_payment_methods, true)) {

    header("Location: checkout.php");
    exit();

}


// ---------- Delivery / pickup details ----------

$fulfilment_type = $_POST["fulfilment_type"] ?? "";

if (!in_array($fulfilment_type, ["Delivery", "Pickup"], true)) {

    checkout_error("Please choose delivery or pickup.");

}


$delivery_address = trim($_POST["delivery_address"] ?? "");

if ($fulfilment_type === "Delivery") {

    if ($delivery_address === "") {

        checkout_error("Please enter your delivery address.");

    }

    if (mb_strlen($delivery_address) > 500) {

        checkout_error("The delivery address is too long.");

    }

} else {

    $delivery_address = null;

}


$timezone = new DateTimeZone("Asia/Colombo");

$requested = DateTime::createFromFormat(
    "Y-m-d\TH:i",
    trim($_POST["requested_datetime"] ?? ""),
    $timezone
);

if ($requested === false) {

    checkout_error("Please choose a preferred date and time.");

}

$earliest = new DateTime("+" . MIN_LEAD_HOURS . " hours", $timezone);

// 5 minute allowance for time spent on the checkout page
$earliest->modify("-5 minutes");

if ($requested < $earliest) {

    checkout_error(
        "Please choose a date and time at least " .
        MIN_LEAD_HOURS . " hours from now."
    );

}

$requested_datetime = $requested->format("Y-m-d H:i:00");


$order_notes = trim($_POST["order_notes"] ?? "");

if (mb_strlen($order_notes) > MAX_NOTES_LENGTH) {

    checkout_error("Order notes are too long.");

}

if ($order_notes === "") {

    $order_notes = null;

}


// ---------- Cart products (prices always come from the database) ----------

$total_cents = 0;

$cart_products = [];


$cart_rows = fetch_cart_items($conn, $_SESSION["cart"]);

foreach ($_SESSION["cart"] as $product_id => $quantity) {

    $product_id = (int) $product_id;

    $quantity = (int) $quantity;

    if ($quantity < 1 || $quantity > MAX_QTY_PER_ITEM) {

        header("Location: cart.php");
        exit();

    }

    if (!isset($cart_rows[$product_id])) {

        // Product no longer exists: send the customer back to review the cart
        unset($_SESSION["cart"][$product_id]);

        header("Location: cart.php");
        exit();

    }

    $product = $cart_rows[$product_id];

    if (!$product["is_available"]) {

        header("Location: cart.php");
        exit();

    }

    $price_cents = (int) round($product["price"] * 100);

    $total_cents += $price_cents * $quantity;

    $cart_products[] = [

        "product_id" => (int) $product["product_id"],

        "product_name" => $product["product_name"],

        "price" => number_format($price_cents / 100, 2, ".", ""),

        "quantity" => $quantity

    ];

}


// Cart must contain valid products
if (empty($cart_products)) {

    header("Location: cart.php");
    exit();

}


$total = number_format($total_cents / 100, 2, ".", "");


// ---------- Optional bank transfer slip (kept private) ----------

$payment_slip = null;

if (
    $payment_method === "Bank Transfer" &&
    isset($_FILES["payment_slip"]) &&
    $_FILES["payment_slip"]["error"] !== UPLOAD_ERR_NO_FILE
) {

    $slip_error = null;

    $payment_slip = lt_save_uploaded_image(
        $_FILES["payment_slip"],
        __DIR__ . "/private_uploads/slips/",
        $slip_error
    );

    if ($payment_slip === null) {

        checkout_error($slip_error ?? "Unable to upload the payment slip.");

    }

}


// New payments are initially pending
$payment_status = "Pending";


// ---------- Create the order and its items together ----------

try {

    $conn->begin_transaction();

    db_exec(
        $conn,
        "INSERT INTO orders
         (
             user_id,
             total_amount,
             order_status,
             payment_method,
             payment_status,
             fulfilment_type,
             delivery_address,
             requested_datetime,
             order_notes,
             payment_slip
         )
         VALUES (?, ?, 'Pending', ?, ?, ?, ?, ?, ?, ?)",
        "issssssss",
        [
            $user_id,
            $total,
            $payment_method,
            $payment_status,
            $fulfilment_type,
            $delivery_address,
            $requested_datetime,
            $order_notes,
            $payment_slip
        ]
    );

    $order_id = $conn->insert_id;


    // Add products to order_items (name saved as it was when ordered)
    foreach ($cart_products as $item) {

        db_exec(
            $conn,
            "INSERT INTO order_items
             (order_id, product_id, product_name, quantity, price)
             VALUES (?, ?, ?, ?, ?)",
            "iisis",
            [
                $order_id,
                $item["product_id"],
                $item["product_name"],
                $item["quantity"],
                $item["price"]
            ]
        );

    }

    $conn->commit();

} catch (Throwable $ex) {

    $conn->rollback();

    error_log("Order creation failed: " . $ex->getMessage());

    if ($payment_slip !== null) {

        $slip_path = __DIR__ . "/private_uploads/slips/" . $payment_slip;

        if (is_file($slip_path)) {
            unlink($slip_path);
        }

    }

    echo "Error creating order.";
    exit();

}


// Clear cart after successful order
unset($_SESSION["cart"]);


// Confirmation email
$lines = "";

foreach ($cart_products as $item) {

    $lines .= "- " . $item["product_name"] . " x " . $item["quantity"] . "\n";

}

lt_send_mail(
    (string) ($_SESSION["email"] ?? ""),
    "Order #" . $order_id . " received - " . SITE_NAME,
    "Thank you for your order!\n\n" .
    "Order #" . $order_id . "\n" . $lines . "\n" .
    "Total: Rs. " . number_format((float) $total, 2) . "\n" .
    $fulfilment_type . ": " . ($delivery_address ?? "Pickup from the bakery") . "\n" .
    "Preferred time: " . $requested_datetime . "\n" .
    "Payment: " . $payment_method . "\n\n" .
    "You can follow your order status in My Orders."
);


// Send customer to confirmation page
header("Location: order_confirmation.php?id=" . (int) $order_id);

exit();

?>
