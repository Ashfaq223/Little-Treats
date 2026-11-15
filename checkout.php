<?php

require_once __DIR__ . "/includes/bootstrap.php";




// Customer must be logged in
if (!isset($_SESSION["user_id"])) {

    remember_next();

    header("Location: login.php");
    exit();

}


// Cart must contain products
if (!isset($_SESSION["cart"]) || empty($_SESSION["cart"])) {

    header("Location: cart.php");
    exit();

}


$total = 0;

$cart_products = [];


// Get products from database
$has_unavailable = false;

$cart_rows = fetch_cart_items($conn, $_SESSION["cart"]);

foreach ($_SESSION["cart"] as $product_id => $quantity) {

    $product_id = (int) $product_id;

    $quantity = (int) $quantity;


    if (isset($cart_rows[$product_id])) {

        $product = $cart_rows[$product_id];

        if (!$product["is_available"]) {

            $has_unavailable = true;

        }

        $subtotal = $product["price"] * $quantity;

        $total += $subtotal;


        $cart_products[] = [

            "product_id" => $product["product_id"],

            "product_name" => $product["product_name"],

            "price" => $product["price"],

            "quantity" => $quantity,

            "subtotal" => $subtotal

        ];

    }

}


if (empty($cart_products) || $has_unavailable) {

    // Unavailable items are flagged on the cart page
    header("Location: cart.php");
    exit();

}


// Message from a failed order attempt (shown once)
$checkout_error = $_SESSION["checkout_error"] ?? "";

unset($_SESSION["checkout_error"]);


// Customer's saved address (used to pre-fill delivery address)
$profile = db_select(
    $conn,
    "SELECT address FROM users WHERE user_id = ?",
    "i",
    [(int) $_SESSION["user_id"]]
)->fetch_assoc();

$saved_address = $profile["address"] ?? "";


// Earliest delivery / pickup time (Sri Lanka time)
$earliest = new DateTime(
    "+" . MIN_LEAD_HOURS . " hours",
    new DateTimeZone("Asia/Colombo")
);

$earliest_value = $earliest->format("Y-m-d\TH:i");

?>


<!DOCTYPE html>

<html>

<head>

    <title>
        Checkout - Little Treats Bakery
    </title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= CHECKOUT ================= -->

<main id="main-content" class="checkout-container">


    <div class="checkout-heading">

        <p class="section-label">
            LITTLE TREATS BAKERY
        </p>

        <h2>
            Checkout
        </h2>

        <p>
            Review your order and select your payment method.
        </p>

    </div>


    <?php if ($checkout_error !== "") { ?>

        <div class="checkout-error">
            <?php echo e($checkout_error); ?>
        </div>

    <?php } ?>



    <div class="checkout-layout">


        <!-- ================= ORDER SUMMARY ================= -->

        <div class="checkout-card">


            <div class="checkout-card-header">

                <h3>
                    🛒 Order Summary
                </h3>

                <span>
                    <?php echo count($cart_products); ?>
                    item(s)
                </span>

            </div>



            <div class="checkout-items">


                <?php foreach ($cart_products as $item) { ?>


                    <div class="checkout-item">


                        <div class="checkout-item-info">

                            <h4>

                                <?php

                                echo htmlspecialchars(
                                    $item["product_name"]
                                );

                                ?>

                            </h4>


                            <p>

                                Rs.
                                <?php

                                echo number_format(
                                    $item["price"],
                                    2
                                );

                                ?>

                                ×

                                <?php echo $item["quantity"]; ?>

                            </p>

                        </div>


                        <strong>

                            Rs.
                            <?php

                            echo number_format(
                                $item["subtotal"],
                                2
                            );

                            ?>

                        </strong>


                    </div>


                <?php } ?>


            </div>



            <!-- TOTAL -->

            <div class="checkout-total">


                <span>
                    Total
                </span>


                <strong>

                    Rs.
                    <?php

                    echo number_format(
                        $total,
                        2
                    );

                    ?>

                </strong>


            </div>


        </div>



        <!-- ================= PAYMENT & CONFIRMATION ================= -->

        <div class="checkout-card confirmation-card">


            <div class="confirmation-icon" aria-hidden="true">
                🧁
            </div>


            <h3>
                Payment Method
            </h3>


            <p>
                Please select your preferred payment method.
            </p>



            <form
                method="POST"
                action="place_order.php"
                enctype="multipart/form-data"
            >

        <?php echo csrf_field(); ?>


                <!-- ================= PAYMENT METHODS ================= -->

                <div class="payment-methods">


                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="Cash on Delivery"
                            required
                        >

                        <span class="payment-option-icon" aria-hidden="true">
                            💵
                        </span>

                        <span class="payment-option-content">

                            <strong>
                                Cash on Delivery
                            </strong>

                            <small>
                                Pay when your order is delivered.
                            </small>

                        </span>

                    </label>



                    <label class="payment-option">

                        <input
                            type="radio"
                            name="payment_method"
                            value="Bank Transfer"
                        >

                        <span class="payment-option-icon" aria-hidden="true">
                            🏦
                        </span>

                        <span class="payment-option-content">

                            <strong>
                                Bank Transfer
                            </strong>

                            <small>
                                Transfer the payment to the bakery bank account.
                            </small>

                        </span>

                    </label>



                </div>



                <!-- ================= BANK TRANSFER DETAILS ================= -->

                <div class="checkout-extra" id="bank-transfer-box" hidden>

                    <strong>Bank transfer details</strong>

                    <p>
                        <?php

                        if (BANK_TRANSFER_DETAILS !== "") {

                            echo nl2br(e(BANK_TRANSFER_DETAILS));

                        } else {

                            echo "Our team will contact you with the bank details after you confirm your order.";

                        }

                        ?>
                    </p>

                    <label for="payment_slip">
                        Payment slip (optional, JPG, PNG or WEBP, max 5 MB)
                    </label>

                    <input
                        type="file"
                        id="payment_slip"
                        name="payment_slip"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>



                <!-- ================= DELIVERY / PICKUP ================= -->

                <div class="checkout-extra">

                    <strong>Delivery or pickup</strong>

                    <div class="checkout-extra-row">

                        <label>
                            <input
                                type="radio"
                                name="fulfilment_type"
                                value="Delivery"
                                checked
                            >
                            Delivery
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="fulfilment_type"
                                value="Pickup"
                            >
                            Pickup from bakery
                        </label>

                    </div>

                    <div id="delivery-address-box">

                        <label for="delivery_address">
                            Delivery address
                        </label>

                        <textarea
                            id="delivery_address"
                            name="delivery_address"
                            rows="3"
                            maxlength="500"
                            required
                        ><?php echo e($saved_address); ?></textarea>

                    </div>

                    <label for="requested_datetime">
                        Preferred date and time
                        (at least <?php echo (int) MIN_LEAD_HOURS; ?> hours from now)
                    </label>

                    <input
                        type="datetime-local"
                        id="requested_datetime"
                        name="requested_datetime"
                        min="<?php echo e($earliest_value); ?>"
                        required
                    >

                    <label for="order_notes">
                        Order notes (optional, e.g. a message on the cake)
                    </label>

                    <textarea
                        id="order_notes"
                        name="order_notes"
                        rows="2"
                        maxlength="<?php echo (int) MAX_NOTES_LENGTH; ?>"
                    ></textarea>

                </div>



                <!-- ================= NOTE ================= -->

                <div class="checkout-note">

                    <strong>
                        Freshly baked for you
                    </strong>

                    <p>
                        Your order will be prepared
                        with care by Little Treats Bakery.
                    </p>

                </div>



                <!-- ================= CONFIRM ORDER ================= -->

                <button
                    type="submit"
                    class="confirm-order-button"
                >
                    Confirm Order
                </button>


            </form>


            <script>

                // Show bank details for Bank Transfer; hide address for Pickup
                (function () {

                    var bankBox = document.getElementById("bank-transfer-box");
                    var addressBox = document.getElementById("delivery-address-box");
                    var address = document.getElementById("delivery_address");

                    function refresh() {

                        var payment = document.querySelector('input[name="payment_method"]:checked');
                        var fulfilment = document.querySelector('input[name="fulfilment_type"]:checked');

                        bankBox.hidden = !(payment && payment.value === "Bank Transfer");

                        var pickup = fulfilment && fulfilment.value === "Pickup";

                        addressBox.hidden = pickup;
                        address.required = !pickup;

                    }

                    document.querySelectorAll('input[name="payment_method"], input[name="fulfilment_type"]')
                        .forEach(function (input) {
                            input.addEventListener("change", refresh);
                        });

                    refresh();

                })();

            </script>



            <a
                href="cart.php"
                class="back-cart-link"
            >
                ← Back to Cart
            </a>


        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>
