<?php

require_once __DIR__ . "/includes/bootstrap.php";




// Create cart if it does not exist
if (!isset($_SESSION["cart"])) {

    $_SESSION["cart"] = [];

}

?>

<!DOCTYPE html>

<html>

<head>

    <title>Shopping Cart - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= CART HERO ================= -->

<section class="cart-hero">

    <p class="section-label">
        LITTLE TREATS BAKERY
    </p>

    <h2>
        Your Shopping Cart
    </h2>

    <p>
        Review your delicious treats before checking out.
    </p>

</section>



<!-- ================= CART CONTENT ================= -->

<main id="main-content" class="cart-container">


<?php

if (empty($_SESSION["cart"])) {

?>


    <!-- EMPTY CART -->

    <div class="empty-cart">


        <div class="empty-cart-icon" aria-hidden="true">
            🛒
        </div>


        <h2>
            Your Cart is Empty
        </h2>


        <p>
            Looks like you haven't added any delicious treats yet.
        </p>


        <a
            href="products.php"
            class="primary-button"
        >
            Explore Our Products
        </a>


    </div>


<?php

} else {


    $total = 0;

?>


    <div class="cart-heading">

        <h2>
            Your Items
        </h2>

        <p>
            Check your items, update quantities or remove anything you don't need.
        </p>

    </div>



    <div class="cart-layout">


        <!-- ================= CART ITEMS ================= -->

        <div class="cart-items">


        <?php


        foreach ($_SESSION["cart"] as $product_id => $quantity) {


            $product_id = (int) $product_id;

            $quantity = (int) $quantity;


            // One query for the whole cart (not one per item)
            if (!isset($cart_rows)) {

                $cart_rows = fetch_cart_items($conn, $_SESSION["cart"]);

            }


            if (isset($cart_rows[$product_id])) {


                $product = $cart_rows[$product_id];


                $subtotal =
                    $product["price"] * $quantity;


                $total += $subtotal;


        ?>


            <!-- PRODUCT CART CARD -->

            <div class="cart-item">


                <div class="cart-product-icon">

                    <?php

                    $image_file = $product["image"] ?? "";

                    $image_path = __DIR__ . "/uploads/products/" . $image_file;

                    if ($image_file !== "" && is_file($image_path)) {

                    ?>

                        <img
                            src="uploads/products/<?php echo htmlspecialchars(rawurlencode($image_file)); ?>"
                            alt="<?php echo htmlspecialchars($product["product_name"]); ?>"
                        >

                    <?php

                    } else {

                        echo "🧁";

                    }

                    ?>

                </div>


                <div class="cart-product-info">


                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $product["product_name"]
                        );

                        ?>

                    </h3>

                    <?php if (!$product["is_available"]) { ?>

                        <p class="cart-product-price">
                            Currently unavailable - please remove this item to continue.
                        </p>

                    <?php } ?>


                    <p class="cart-product-price">

                        Rs.

                        <?php

                        echo number_format(
                            $product["price"],
                            2
                        );

                        ?>

                        each

                    </p>


                    <p class="cart-subtotal">

                        Subtotal:

                        <strong>

                            Rs.

                            <?php

                            echo number_format(
                                $subtotal,
                                2
                            );

                            ?>

                        </strong>

                    </p>


                </div>


                <!-- QUANTITY -->

                <form
                    method="POST"
                    action="update_cart.php"
                    class="quantity-form"
                >

        <?php echo csrf_field(); ?>


                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $product_id; ?>"
                    >


                    <label>
                        Quantity
                    </label>


                    <div class="quantity-control">


                        <input
                            type="number"
                            name="quantity"
                            value="<?php echo $quantity; ?>"
                            min="1"
                            max="<?php echo MAX_QTY_PER_ITEM; ?>"
                        >


                        <button type="submit">
                            Update
                        </button>


                    </div>


                </form>


                <!-- REMOVE -->

                <a
                    href="remove_from_cart.php?id=<?php echo $product_id; ?>"
                    class="remove-cart-item"
                    onclick="return confirm('Remove this product from your cart?');"
                >
                    Remove
                </a>


            </div>


        <?php

            }

        }


        ?>


        </div>



        <!-- ================= ORDER SUMMARY ================= -->

        <aside class="cart-summary">


            <h2>
                Order Summary
            </h2>


            <div class="summary-row">

                <span>
                    Items
                </span>

                <strong>

                    <?php

                    echo count($_SESSION["cart"]);

                    ?>

                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Subtotal
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


            <div class="summary-row">

                <span>
                    Delivery
                </span>

                <strong>
                    Free
                </strong>

            </div>


            <hr>


            <div class="summary-total">

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


            <a
                href="checkout.php"
                class="checkout-button"
            >
                Proceed to Checkout
            </a>


            <a
                href="products.php"
                class="continue-shopping"
            >
                ← Continue Shopping
            </a>


        </aside>


    </div>


<?php

}

?>


</main>



<!-- ================= CTA ================= -->

<section class="cart-cta">

    <h2>
        Freshly Baked Happiness Awaits
    </h2>

    <p>
        Add more of your favourite treats to your order.
    </p>

    <a
        href="products.php"
        class="primary-button"
    >
        Explore Products
    </a>

</section>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>