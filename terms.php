<?php

require_once __DIR__ . "/includes/bootstrap.php";

$meta_description = "Ordering, payment, cancellation and loyalty programme terms for Little Treats Bakery.";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Terms and Conditions - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="legal-page">
    <h2>Terms &amp; Conditions</h2>

    <h3>Orders</h3>
    <ul>
        <li>Orders must be placed at least <?php echo (int) MIN_LEAD_HOURS; ?> hours before the delivery or pickup time you choose.</li>
        <li>Prices are shown in Sri Lankan rupees (Rs.) and are confirmed when you place the order.</li>
        <li>An order is accepted once we confirm it. We may decline an order if we cannot prepare it in time.</li>
    </ul>

    <h3>Payment</h3>
    <p>
        You can pay by Cash on Delivery or by Bank Transfer. Bank transfer
        orders are prepared once payment is received or confirmed.
    </p>

    <h3>Cancellations</h3>
    <p>
        You can cancel an order yourself while its status is Pending. After
        we confirm an order, please <a href="contact.php">contact us</a>;
        made-to-order items may not be cancellable once preparation has started.
    </p>

    <h3>Loyalty points</h3>
    <ul>
        <li>You earn 1 point for every Rs. <?php echo number_format(LOYALTY_RS_PER_POINT); ?> of an order once it is marked Completed.</li>
        <li>Points can be redeemed for rewards listed on the Loyalty page. They have no cash value and cannot be transferred.</li>
        <li>We may change the programme or its rewards. Points already earned are kept.</li>
    </ul>

    <h3>Allergies</h3>
    <p>
        Our products may contain milk, eggs, nuts, gluten and other
        allergens. Please tell us about any allergy in your order notes
        or by contacting us before ordering.
    </p>
</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>