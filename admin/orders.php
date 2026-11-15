<?php

require_once __DIR__ . "/admin_auth.php";


/* ---------- Search, filters and paging ---------- */

$search = trim($_GET["q"] ?? "");
$status_filter = $_GET["status"] ?? "";
$payment_filter = $_GET["payment"] ?? "";
$page = current_page();

$where = ["1 = 1"];
$types = "";
$params = [];

if ($search !== "") {

    $like = "%" . addcslashes(mb_substr($search, 0, 100), "%_\\") . "%";

    $where[] = "(orders.order_id = ? OR users.name LIKE ? OR users.email LIKE ?)";
    $types .= "iss";
    $params[] = (int) ltrim($search, "#");
    $params[] = $like;
    $params[] = $like;

}

if (in_array($status_filter, ORDER_STATUSES, true)) {

    $where[] = "orders.order_status = ?";
    $types .= "s";
    $params[] = $status_filter;

} else {

    $status_filter = "";

}

if (in_array($payment_filter, PAYMENT_STATUSES, true)) {

    $where[] = "orders.payment_status = ?";
    $types .= "s";
    $params[] = $payment_filter;

} else {

    $payment_filter = "";

}

$from = "FROM orders LEFT JOIN users ON users.user_id = orders.user_id WHERE " . implode(" AND ", $where);

$total_orders = (int) db_select($conn, "SELECT COUNT(*) AS total " . $from, $types, $params)
    ->fetch_assoc()["total"];

$orders_result = db_select(
    $conn,
    "SELECT orders.*, users.name AS customer_name " . $from .
    " ORDER BY orders.created_at DESC LIMIT ? OFFSET ?",
    $types . "ii",
    array_merge($params, [PAGE_SIZE, ($page - 1) * PAGE_SIZE])
);

?>

<!DOCTYPE html>

<html>

<head>

    <title>Manage Orders - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        /* ================= PAYMENT STATUS ================= */

        .payment-status {
            font-weight: 600;
        }

        .payment-status.pending {
            color: #a66a00;
        }

        .payment-status.paid {
            color: #2e7d32;
        }

        .payment-status.failed {
            color: #c62828;
        }

        .payment-status.cancelled {
            color: #777;
        }

        /* ================= PAYMENT METHOD ================= */

        .payment-method {
            font-size: 14px;
            color: #4f3b32;
            font-weight: 500;
        }

        /* ================= RESPONSIVE TABLE ================= */

        .table-container {
            overflow-x: auto;
        }

        table {
            min-width: 950px;
        }

    </style>

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- ================= MAIN ================= -->

<main id="main-content" class="admin-container">


    <div class="admin-welcome">

        <h2>📦 Manage Orders</h2>

        <p>
            View customer orders and manage order and payment status.
        </p>

    </div>



    <!-- ================= ORDERS TABLE ================= -->

    <div class="admin-card">

        <h3>Customer Orders</h3>


        <form method="GET" class="admin-filters" role="search">

            <label class="visually-hidden" for="f-q">Search</label>
            <input type="search" id="f-q" name="q" value="<?php echo e($search); ?>" placeholder="Order # or customer name / email">

            <label class="visually-hidden" for="f-status">Order status</label>
            <select id="f-status" name="status">
                <option value="">All order statuses</option>
                <?php foreach (ORDER_STATUSES as $s) { ?>
                    <option value="<?php echo e($s); ?>" <?php echo $status_filter === $s ? "selected" : ""; ?>><?php echo e($s); ?></option>
                <?php } ?>
            </select>

            <label class="visually-hidden" for="f-payment">Payment status</label>
            <select id="f-payment" name="payment">
                <option value="">All payment statuses</option>
                <?php foreach (PAYMENT_STATUSES as $s) { ?>
                    <option value="<?php echo e($s); ?>" <?php echo $payment_filter === $s ? "selected" : ""; ?>><?php echo e($s); ?></option>
                <?php } ?>
            </select>

            <button type="submit">Filter</button>

            <?php if ($search !== "" || $status_filter !== "" || $payment_filter !== "") { ?>
                <a href="orders.php">Clear</a>
            <?php } ?>

        </form>


        <div class="table-container">

            <table>

                <tr>

                    <th>Order ID</th>

                    <th>Customer</th>

                    <th>Total</th>

                    <th>Order Status</th>

                    <th>Payment Method</th>

                    <th>Payment Status</th>

                    <th>Date</th>

                    <th>Action</th>

                </tr>


                <?php

                $result = $orders_result;


                if (mysqli_num_rows($result) > 0) {

                    while ($order = mysqli_fetch_assoc($result)) {

                ?>

                <tr>

                    <!-- ORDER ID -->

                    <td>

                        <strong>
                            #<?php echo $order["order_id"]; ?>
                        </strong>

                    </td>


                    <!-- CUSTOMER ID -->

                    <td>

                        <?php

                        echo e($order["customer_name"] ?? "Deleted customer");

                        ?>
                        <small>(#<?php echo (int) $order["user_id"]; ?>)</small>

                    </td>


                    <!-- TOTAL -->

                    <td>

                        <strong>
                            Rs. <?php echo number_format($order["total_amount"], 2); ?>
                        </strong>

                    </td>


                    <!-- ORDER STATUS -->

                    <td>

                        <?php

                        $order_status = $order["order_status"];

                        ?>

                        <span class="status-badge">

                            <?php echo htmlspecialchars($order_status); ?>

                        </span>

                    </td>


                    <!-- PAYMENT METHOD -->

                    <td>

                        <span class="payment-method">

                            <?php

                            echo htmlspecialchars(
                                $order["payment_method"] ?? "Not Selected"
                            );

                            ?>

                        </span>

                    </td>


                    <!-- PAYMENT STATUS -->

                    <td>

                        <?php

                        $payment_status = $order["payment_status"] ?? "Pending";

                        $payment_status_class = strtolower(
                            str_replace(" ", "-", $payment_status)
                        );

                        ?>

                        <span class="payment-status <?php echo htmlspecialchars($payment_status_class); ?>">

                            <?php echo htmlspecialchars($payment_status); ?>

                        </span>

                    </td>


                    <!-- DATE -->

                    <td>

                        <?php echo htmlspecialchars($order["created_at"]); ?>

                    </td>


                    <!-- ACTION -->

                    <td>

                        <a
                            href="update_order_status.php?id=<?php echo $order["order_id"]; ?>"
                            class="table-button edit-button"
                        >
                            Update Status
                        </a>

                    </td>

                </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="8">

                            No orders found.

                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>

        <?php echo pager_html($page, $total_orders); ?>

    </div>


    <br>


    <a href="dashboard.php" class="back-link">
        ← Back to Dashboard
    </a>


</main>



<!-- ================= FOOTER ================= -->

<footer class="admin-footer">

    <p>
        © 2026 Little Treats Bakery | Administration Panel
    </p>

</footer>


</body>

</html>