<?php

require_once __DIR__ . "/includes/auth.php";


$user_id = $_SESSION["user_id"];

?>

<!DOCTYPE html>

<html>

<head>

    <title>My Orders - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= PAGE HERO ================= -->

<section class="orders-hero">

    <p class="section-label">
        LITTLE TREATS BAKERY
    </p>

    <h2>
        My Orders
    </h2>

    <p>
        Track your orders and view your previous purchases.
    </p>

</section>



<!-- ================= ORDERS ================= -->

<main id="main-content" class="orders-container">


    <?php

    $result = db_select(
        $conn,
        "SELECT *
         FROM orders
         WHERE user_id = ?
         ORDER BY created_at DESC",
        "i",
        [(int) $user_id]
    );


    if ($result->num_rows > 0) {

    ?>


        <div class="orders-heading">

            <h2>
                Your Order History
            </h2>

            <p>
                Here you can view the status and details of your orders.
            </p>

        </div>


        <div class="orders-list">


        <?php

        while ($order = mysqli_fetch_assoc($result)) {

            $status = $order["order_status"];

            $status_class = strtolower($status);

            $payment_status = $order["payment_status"];

            $payment_status_class = strtolower($payment_status);

        ?>


            <!-- ORDER CARD -->

            <div class="order-card">


                <div class="order-card-top">


                    <div>

                        <span class="order-label">
                            ORDER
                        </span>

                        <h3>
                            #<?php echo $order["order_id"]; ?>
                        </h3>

                    </div>


                    <span class="order-status <?php echo e($status_class); ?>">

                        <?php

                        echo htmlspecialchars(
                            $status
                        );

                        ?>

                    </span>


                </div>



                <div class="order-card-info">


                    <!-- ORDER TOTAL -->

                    <div>

                        <span>
                            Order Total
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



                    <!-- PAYMENT METHOD -->

                    <div>

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

                    <div>

                        <span>
                            Payment Status
                        </span>

                        <strong class="payment-status <?php echo e($payment_status_class); ?>">

                            <?php

                            echo htmlspecialchars(
                                $payment_status
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- ORDER DATE -->

                    <div>

                        <span>
                            Order Date
                        </span>

                        <strong>

                            <?php

                            echo date(
                                "d M Y",
                                strtotime(
                                    $order["created_at"]
                                )
                            );

                            ?>

                        </strong>

                    </div>



                    <!-- ORDER TIME -->

                    <div>

                        <span>
                            Order Time
                        </span>

                        <strong>

                            <?php

                            echo date(
                                "h:i A",
                                strtotime(
                                    $order["created_at"]
                                )
                            );

                            ?>

                        </strong>

                    </div>


                </div>



                <?php echo order_timeline_html($status); ?>


                <div class="order-card-bottom">


                    <span class="order-message">

                        <?php

                        if ($status == "Pending") {

                            echo "Your order has been received.";

                        } elseif ($status == "Confirmed") {

                            echo "Your order has been confirmed.";

                        } elseif ($status == "Preparing") {

                            echo "Your delicious treats are being prepared.";

                        } elseif ($status == "Ready") {

                            echo "Your order is ready.";

                        } elseif ($status == "Completed") {

                            echo "Your order has been completed. Enjoy your treats!";

                        } elseif ($status == "Cancelled") {

                            echo "This order has been cancelled.";

                        } else {

                            echo "Your order is being processed.";

                        }

                        ?>

                    </span>


                    <a
                        href="order_details.php?id=<?php echo (int) $order["order_id"]; ?>"
                        class="order-details-button"
                    >
                        View Details
                    </a>


                    <?php if ($status === "Pending") { ?>

                        <form
                            method="POST"
                            action="cancel_order.php"
                            class="inline-form"
                            data-confirm="Cancel order #<?php echo (int) $order["order_id"]; ?>?"
                        >
                            <?php echo csrf_field(); ?>
                            <input
                                type="hidden"
                                name="order_id"
                                value="<?php echo (int) $order["order_id"]; ?>"
                            >
                            <button type="submit" class="cancel-order-button">
                                Cancel Order
                            </button>
                        </form>

                    <?php } ?>


                </div>


            </div>


        <?php

        }

        ?>


        </div>


    <?php

    } else {

    ?>


        <!-- NO ORDERS -->

        <div class="no-orders">


            <div class="no-orders-icon" aria-hidden="true">
                🧁
            </div>


            <h2>
                No Orders Yet
            </h2>


            <p>
                You haven't placed an order yet.
                Explore our delicious treats and place your first order.
            </p>


            <a
                href="products.php"
                class="primary-button"
            >
                Explore Products
            </a>


        </div>


    <?php

    }

    ?>


</main>



<!-- ================= SHOPPING CTA ================= -->

<section class="orders-cta">

    <h2>
        Looking for Something Delicious?
    </h2>

    <p>
        Explore our freshly baked treats and place your next order.
    </p>

    <a
        href="products.php"
        class="primary-button"
    >
        Continue Shopping
    </a>

</section>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>
