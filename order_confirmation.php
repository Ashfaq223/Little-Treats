<?php

require_once __DIR__ . "/includes/bootstrap.php";




if (!isset($_SESSION["user_id"])) {

    remember_next();

    header("Location: login.php");
    exit();

}


if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();

}


$order_id = (int) $_GET["id"];

$user_id = (int) $_SESSION["user_id"];


$result = db_select(
    $conn,
    "SELECT *
     FROM orders
     WHERE order_id = ?
     AND user_id = ?",
    "ii",
    [$order_id, $user_id]
);


if ($result->num_rows != 1) {

    echo "Order not found.";
    exit();

}


$order = $result->fetch_assoc();

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        Order Confirmed - Little Treats Bakery
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



<!-- ================= CONFIRMATION ================= -->

<main id="main-content" class="confirmation-container">


    <div class="confirmation-box">


        <!-- SUCCESS ICON -->

        <div class="success-icon" aria-hidden="true">

            ✓

        </div>


        <p class="section-label">
            LITTLE TREATS BAKERY
        </p>


        <h2>
            Order Confirmed!
        </h2>


        <p class="confirmation-message">

            Thank you for your order!

            <br>

            Your delicious treats are now being prepared
            with care.

        </p>



        <!-- ORDER DETAILS -->

        <div class="order-confirmation-details">


            <!-- ORDER ID -->

            <div class="confirmation-detail">

                <span>
                    Order ID
                </span>

                <strong>

                    #<?php echo $order["order_id"]; ?>

                </strong>

            </div>



            <!-- TOTAL AMOUNT -->

            <div class="confirmation-detail">

                <span>
                    Total Amount
                </span>

                <strong>

                    Rs.
                    <?php

                    echo number_format(
                        $order["total_amount"],
                        2
                    );

                    ?>

                </strong>

            </div>



            <!-- DELIVERY / PICKUP -->

            <div class="confirmation-detail">

                <span>
                    <?php echo e($order["fulfilment_type"]); ?>
                </span>

                <strong>

                    <?php

                    if ($order["requested_datetime"]) {

                        echo e(date(
                            "d M Y, h:i A",
                            strtotime($order["requested_datetime"])
                        ));

                    }

                    ?>

                </strong>

            </div>



            <!-- PAYMENT METHOD -->

            <div class="confirmation-detail">

                <span>
                    Payment Method
                </span>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $order["payment_method"]
                    );

                    ?>

                </strong>

            </div>



            <!-- PAYMENT STATUS -->

            <div class="confirmation-detail">

                <span>
                    Payment Status
                </span>

                <strong class="status-badge">

                    <?php

                    echo htmlspecialchars(
                        $order["payment_status"]
                    );

                    ?>

                </strong>

            </div>



            <!-- ORDER STATUS -->

            <div class="confirmation-detail">

                <span>
                    Order Status
                </span>

                <strong class="status-badge">

                    <?php

                    echo htmlspecialchars(
                        $order["order_status"]
                    );

                    ?>

                </strong>

            </div>


        </div>



        <!-- MESSAGE -->

        <div class="confirmation-note">

            <strong>
                🧁 What's next?
            </strong>

            <p>

                Our bakery team will prepare your order
                and update the order status as it progresses.

                You can check the latest status anytime
                from <strong>My Orders</strong>.

            </p>

        </div>



        <!-- BUTTONS -->

        <div class="confirmation-actions">


            <a
                href="my_orders.php"
                class="primary-confirmation-button"
            >

                View My Orders

            </a>


            <a
                href="products.php"
                class="secondary-confirmation-button"
            >

                Continue Shopping

            </a>


        </div>


    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>
