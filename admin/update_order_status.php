<?php

require_once __DIR__ . "/admin_auth.php";

$message = "";
$message_type = "";

$order_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;


// =========================================
// VALIDATE ORDER ID
// =========================================

if ($order_id <= 0) {

    header("Location: orders.php");
    exit();

}


// =========================================
// GET ORDER DETAILS
// =========================================

$sql = "SELECT *
        FROM orders
        WHERE order_id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {

    die("Unable to process the request.");

}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $order_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {

    mysqli_stmt_close($stmt);

    echo "Order not found.";
    exit();

}

$order = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// =========================================
// ALLOWED ORDER STATUSES
// =========================================

$allowed_statuses = ORDER_STATUSES;


// =========================================
// ALLOWED PAYMENT STATUSES
// =========================================

$allowed_payment_statuses = PAYMENT_STATUSES;


// =========================================
// HANDLE STATUS UPDATE
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = trim($_POST["status"] ?? "");
    $payment_status = trim($_POST["payment_status"] ?? "");


    // =========================================
    // VALIDATE ORDER STATUS
    // =========================================

    if (!in_array($status, $allowed_statuses, true)) {

        $message = "Invalid order status selected.";
        $message_type = "error";

    }

    // =========================================
    // VALIDATE PAYMENT STATUS
    // =========================================

    elseif (
        !in_array(
            $payment_status,
            $allowed_payment_statuses,
            true
        )
    ) {

        $message = "Invalid payment status selected.";
        $message_type = "error";

    }

    else {

        $old_order_status = $order["order_status"];
        $old_payment_status = $order["payment_status"] ?? "Pending";


        // =========================================
        // CHECK IF ANYTHING CHANGED
        // =========================================

        if (
            $status === $old_order_status &&
            $payment_status === $old_payment_status
        ) {

            $message = "No changes were made.";
            $message_type = "error";

        }

        else {


            // =========================================
            // UPDATE ORDER AND PAYMENT STATUS
            // =========================================

            $update_sql = "UPDATE orders
                           SET order_status = ?,
                               payment_status = ?
                           WHERE order_id = ?";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );


            if ($update_stmt) {

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "ssi",
                    $status,
                    $payment_status,
                    $order_id
                );


                // Status change and points award succeed or fail together
                $conn->begin_transaction();

                if (mysqli_stmt_execute($update_stmt)) {


                    // =========================================
                    // AWARD LOYALTY POINTS
                    // ONLY WHEN ORDER BECOMES COMPLETED
                    // =========================================

                    // The points_awarded flag makes sure an order can only
                    // ever give points once, even if it is set to Completed
                    // again after being changed back.

                    if ($status === "Completed") {

                        $claimed = db_exec(
                            $conn,
                            "UPDATE orders
                             SET points_awarded = 1
                             WHERE order_id = ?
                             AND points_awarded = 0",
                            "i",
                            [$order_id]
                        );

                        if ($claimed === 1) {

                            $order_total = (float)$order["total_amount"];

                            $earned_points = (int)floor(
                                $order_total / LOYALTY_RS_PER_POINT
                            );


                            if ($earned_points > 0 && $order["user_id"]) {

                                // Creates the loyalty account if it is missing
                                db_exec(
                                    $conn,
                                    "INSERT INTO loyalty_points (user_id, points)
                                     VALUES (?, ?)
                                     ON DUPLICATE KEY UPDATE points = points + VALUES(points)",
                                    "ii",
                                    [(int)$order["user_id"], $earned_points]
                                );

                            }

                        }

                    }

                    $conn->commit();


                    audit_log(
                        $conn,
                        "update_status",
                        "order",
                        $order_id,
                        "Order: " . $old_order_status . " -> " . $status .
                        "; Payment: " . $old_payment_status . " -> " . $payment_status
                    );


                    // Tell the customer when the order status changes
                    if ($status !== $old_order_status && $order["user_id"]) {

                        $customer = db_select(
                            $conn,
                            "SELECT name, email FROM users WHERE user_id = ?",
                            "i",
                            [(int) $order["user_id"]]
                        )->fetch_assoc();

                        if ($customer) {

                            lt_send_mail(
                                $customer["email"],
                                "Your order #" . $order_id . " is now " . $status,
                                "Hello " . $customer["name"] . ",\n\n" .
                                "The status of your order #" . $order_id . " at " . SITE_NAME .
                                " is now: " . $status . ".\n\n" .
                                "You can follow it any time in My Orders."
                            );

                        }

                    }


                    mysqli_stmt_close($update_stmt);


                    header("Location: orders.php");
                    exit();


                } else {

                    $message = "Unable to update the order. Please try again.";
                    $message_type = "error";

                }


                mysqli_stmt_close($update_stmt);


            } else {

                $message = "Unable to process the order update.";
                $message_type = "error";

            }

        }

    }

}


// =========================================
// STATUS CLASS
// =========================================

$status_class = strtolower(
    str_replace(
        " ",
        "-",
        $order["order_status"]
    )
);


// =========================================
// PAYMENT STATUS
// =========================================

$current_payment_status =
    $order["payment_status"] ?? "Pending";


$payment_status_class = strtolower(
    str_replace(
        " ",
        "-",
        $current_payment_status
    )
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Update Order #<?php echo $order_id; ?> -
        Little Treats Bakery
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        /* =========================================
           PAYMENT STATUS
        ========================================= */

        .payment-status-display {
            font-weight: 600;
        }

        .payment-status-display.pending {
            color: #a66a00;
        }

        .payment-status-display.paid {
            color: #2e7d32;
        }

        .payment-status-display.failed {
            color: #c62828;
        }

        .payment-status-display.cancelled {
            color: #777;
        }


        /* =========================================
           PAYMENT METHOD
        ========================================= */

        .payment-method-display {
            font-weight: 600;
            color: #4f3b32;
        }


        /* =========================================
           STATUS FORM SECTIONS
        ========================================= */

        .status-section {
            margin-bottom: 25px;
        }

        .status-section:last-child {
            margin-bottom: 0;
        }

        .status-section h4 {
            margin-bottom: 8px;
            color: #3f2d25;
        }

        .status-help {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: #777;
        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 600px) {

            .order-information {
                width: 100%;
            }

            .order-info-row {
                gap: 10px;
                flex-wrap: wrap;
            }

        }

    </style>

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>


<body>


<!-- =========================================
     ADMIN HEADER
========================================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- =========================================
     MAIN CONTENT
========================================= -->

<main id="main-content" class="admin-container">


    <!-- PAGE INTRO -->

    <div class="admin-welcome">

        <h2>
            📦 Update Order
        </h2>

        <p>
            Manage the order and payment status for this customer order.
        </p>

    </div>



    <!-- =========================================
         ORDER INFORMATION
    ========================================== -->

    <div class="admin-card">

        <h3>
            Order Information
        </h3>


        <div class="order-information">


            <!-- ORDER ID -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Order ID
                </span>

                <strong>
                    #<?php echo $order_id; ?>
                </strong>

            </div>


            <!-- CUSTOMER ID -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Customer ID
                </span>

                <strong>
                    #<?php echo (int)$order["user_id"]; ?>
                </strong>

            </div>


            <!-- TOTAL -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Total Amount
                </span>

                <strong>
                    Rs.
                    <?php
                    echo number_format(
                        (float)$order["total_amount"],
                        2
                    );
                    ?>
                </strong>

            </div>


            <!-- PAYMENT METHOD -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Payment Method
                </span>

                <strong class="payment-method-display">

                    <?php

                    echo htmlspecialchars(
                        $order["payment_method"] ?? "Not Selected"
                    );

                    ?>

                </strong>

            </div>


            <!-- DELIVERY / PICKUP -->

            <div class="order-info-row">

                <span class="order-info-label">
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

                    if (
                        $order["fulfilment_type"] === "Delivery" &&
                        $order["delivery_address"]
                    ) {

                        echo "<br>" . nl2br(e($order["delivery_address"]));

                    }

                    ?>

                </strong>

            </div>


            <?php if ($order["order_notes"]) { ?>

                <div class="order-info-row">

                    <span class="order-info-label">
                        Order Notes
                    </span>

                    <strong>
                        <?php echo nl2br(e($order["order_notes"])); ?>
                    </strong>

                </div>

            <?php } ?>


            <?php if ($order["payment_slip"]) { ?>

                <div class="order-info-row">

                    <span class="order-info-label">
                        Payment Slip
                    </span>

                    <strong>
                        <a
                            href="view_slip.php?id=<?php echo (int) $order_id; ?>"
                            target="_blank"
                            rel="noopener"
                        >
                            View uploaded slip
                        </a>
                    </strong>

                </div>

            <?php } ?>


            <!-- CURRENT ORDER STATUS -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Current Order Status
                </span>

                <span
                    class="order-status <?php echo htmlspecialchars($status_class); ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $order["order_status"]
                    );

                    ?>

                </span>

            </div>


            <!-- CURRENT PAYMENT STATUS -->

            <div class="order-info-row">

                <span class="order-info-label">
                    Current Payment Status
                </span>

                <span
                    class="payment-status-display <?php echo htmlspecialchars($payment_status_class); ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $current_payment_status
                    );

                    ?>

                </span>

            </div>


        </div>

    </div>



    <!-- =========================================
         UPDATE FORM
    ========================================== -->

    <div class="admin-card">

        <h3>
            Change Order & Payment Status
        </h3>


        <!-- MESSAGE -->

        <?php if ($message !== "") { ?>

            <div
                class="message <?php echo htmlspecialchars($message_type); ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>


        <form
            method="POST"
            action=""
        >

        <?php echo csrf_field(); ?>


            <!-- =====================================
                 ORDER STATUS
            ====================================== -->

            <div class="form-group status-section">

                <h4>
                    Order Status
                </h4>

                <label for="status">
                    Select Order Status
                </label>


                <select
                    id="status"
                    name="status"
                    required
                >

                    <option value="">
                        Select Order Status
                    </option>


                    <?php foreach (
                        $allowed_statuses
                        as $allowed_status
                    ) { ?>

                        <option
                            value="<?php echo htmlspecialchars($allowed_status); ?>"
                            <?php

                            if (
                                $allowed_status ===
                                $order["order_status"]
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php

                            echo htmlspecialchars(
                                $allowed_status
                            );

                            ?>

                        </option>

                    <?php } ?>

                </select>

                <small class="status-help">
                    This controls the progress of the customer's order.
                </small>

            </div>



            <!-- =====================================
                 PAYMENT STATUS
            ====================================== -->

            <div class="form-group status-section">

                <h4>
                    Payment Status
                </h4>

                <label for="payment_status">
                    Select Payment Status
                </label>


                <select
                    id="payment_status"
                    name="payment_status"
                    required
                >

                    <option value="">
                        Select Payment Status
                    </option>


                    <?php foreach (
                        $allowed_payment_statuses
                        as $allowed_payment_status
                    ) { ?>

                        <option
                            value="<?php echo htmlspecialchars($allowed_payment_status); ?>"
                            <?php

                            if (
                                $allowed_payment_status ===
                                $current_payment_status
                            ) {

                                echo "selected";

                            }

                            ?>
                        >

                            <?php

                            echo htmlspecialchars(
                                $allowed_payment_status
                            );

                            ?>

                        </option>

                    <?php } ?>

                </select>

                <small class="status-help">
                    This records whether the customer's payment has been received.
                </small>

            </div>



            <!-- ACTION BUTTONS -->

            <div class="admin-form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    Update Order
                </button>


                <a
                    href="orders.php"
                    class="back-link"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>



    <!-- =========================================
         LOYALTY INFORMATION
    ========================================== -->

    <div class="admin-card loyalty-info-card">

        <h3>
            ⭐ Loyalty Points
        </h3>

        <p>

            Loyalty points are awarded when an order
            is marked as <strong>Completed</strong>.

        </p>

        <p>

            Customers earn
            <strong>1 loyalty point</strong>
            for every Rs. <?php echo number_format(LOYALTY_RS_PER_POINT); ?> spent.

        </p>

    </div>



    <!-- BACK TO ORDERS -->

    <br>

    <a
        href="orders.php"
        class="back-link"
    >
        ← Back to Orders
    </a>


</main>



<!-- =========================================
     FOOTER
========================================= -->

<footer class="admin-footer">

    <p>
        © 2026 Little Treats Bakery |
        Administration Panel
    </p>

</footer>


</body>

</html>