<?php

require_once __DIR__ . "/includes/auth.php";

$user_id = (int) $_SESSION["user_id"];

if (!isset($_GET["id"])) {

    redirect("my_orders.php");

}

$order_id = (int) $_GET["id"];


$order = db_select(
    $conn,
    "SELECT *
     FROM orders
     WHERE order_id = ?
     AND user_id = ?",
    "ii",
    [$order_id, $user_id]
)->fetch_assoc();


if (!$order) {

    http_response_code(404);

    echo "Order not found.";
    exit();

}


// Name saved at order time; falls back to the product name for old orders
$items = db_select(
    $conn,
    "SELECT order_items.*,
            COALESCE(order_items.product_name, products.product_name) AS product_name

     FROM order_items

     LEFT JOIN products
     ON order_items.product_id = products.product_id

     WHERE order_items.order_id = ?",
    "i",
    [$order_id]
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Order #<?php echo (int) $order["order_id"]; ?> - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="orders-container">

    <div class="orders-heading">

        <h2>Order #<?php echo (int) $order["order_id"]; ?></h2>

        <p>
            Placed on
            <?php echo e(date("d M Y, h:i A", strtotime($order["created_at"]))); ?>
        </p>

    </div>

    <div class="order-card">

        <div class="order-card-top">

            <div>
                <span class="order-label">STATUS</span>
                <h3><?php echo e($order["order_status"]); ?></h3>
            </div>

            <span class="order-status <?php echo e(strtolower($order["order_status"])); ?>">
                <?php echo e($order["payment_status"]); ?>
            </span>

        </div>

        <?php echo order_timeline_html($order["order_status"]); ?>

        <div class="detail-lines">

            <p>
                <strong><?php echo e($order["fulfilment_type"]); ?>:</strong>
                <?php

                if ($order["requested_datetime"]) {

                    echo e(date("d M Y, h:i A", strtotime($order["requested_datetime"])));

                }

                ?>
            </p>

            <?php if ($order["fulfilment_type"] === "Delivery" && $order["delivery_address"]) { ?>
                <p><strong>Address:</strong> <?php echo nl2br(e($order["delivery_address"])); ?></p>
            <?php } ?>

            <?php if ($order["order_notes"]) { ?>
                <p><strong>Notes:</strong> <?php echo nl2br(e($order["order_notes"])); ?></p>
            <?php } ?>

            <p><strong>Payment:</strong> <?php echo e($order["payment_method"]); ?></p>

        </div>

        <div class="table-scroll">

            <table class="detail-table">

                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($item = $items->fetch_assoc()) { ?>

                        <tr>
                            <td><?php echo e($item["product_name"]); ?></td>
                            <td>Rs. <?php echo number_format($item["price"], 2); ?></td>
                            <td><?php echo (int) $item["quantity"]; ?></td>
                            <td>Rs. <?php echo number_format($item["price"] * $item["quantity"], 2); ?></td>
                        </tr>

                    <?php } ?>

                </tbody>

                <tfoot>
                    <tr>
                        <th scope="row" colspan="3">Total</th>
                        <td><strong>Rs. <?php echo number_format($order["total_amount"], 2); ?></strong></td>
                    </tr>
                </tfoot>

            </table>

        </div>

        <div class="order-card-bottom">

            <a href="my_orders.php" class="order-details-button">
                &larr; Back to My Orders
            </a>

            <?php if ($order["order_status"] === "Pending") { ?>

                <form
                    method="POST"
                    action="cancel_order.php"
                    data-confirm="Cancel this order?"
                >
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="order_id" value="<?php echo (int) $order["order_id"]; ?>">
                    <button type="submit" class="cancel-order-button">Cancel Order</button>
                </form>

            <?php } ?>

        </div>

    </div>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>