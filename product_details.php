<?php

require_once __DIR__ . "/includes/bootstrap.php";




// Check if product ID exists
if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();

}


$product_id = (int) $_GET["id"];


// Get product information (removed products are hidden)
$sql = "SELECT products.*, categories.category_name
        FROM products

        LEFT JOIN categories
        ON products.category_id = categories.category_id

        WHERE products.product_id = ?
        AND products.is_active = 1";


$result = db_select($conn, $sql, "i", [$product_id]);


// Check if product exists
if ($result->num_rows != 1) {

    echo "Product not found.";
    exit();

}


$product = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>

<html>

<head>

    <title>
        <?php echo htmlspecialchars($product["product_name"]); ?>
        - Little Treats Bakery
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



<!-- ================= PRODUCT DETAILS ================= -->

<main id="main-content" class="product-details-container">


    <!-- BACK LINK -->

    <a
        href="products.php"
        class="back-link"
    >
        ← Back to Products
    </a>



    <div class="product-details-card">


        <!-- PRODUCT IMAGE PLACEHOLDER -->

        <div class="product-image-area">

            <?php

            $image_file = $product["image"] ?? "";

            $image_path = __DIR__ . "/uploads/products/" . $image_file;

            if ($image_file !== "" && is_file($image_path)) {

            ?>

                <img
                    class="product-detail-image"
                    src="uploads/products/<?php echo htmlspecialchars(rawurlencode($image_file)); ?>"
                    alt="<?php echo htmlspecialchars($product["product_name"]); ?>"
                >

            <?php

            } else {

            ?>

                <div class="product-image-placeholder" aria-hidden="true">
                    🧁
                </div>

            <?php

            }

            ?>

        </div>



        <!-- PRODUCT INFORMATION -->

        <div class="product-info">


            <p class="product-label">
                LITTLE TREATS BAKERY
            </p>


            <h2>

                <?php

                echo htmlspecialchars(
                    $product["product_name"]
                );

                ?>

            </h2>


            <!-- CATEGORY -->

            <p class="product-category">

                <?php

                echo htmlspecialchars(
                    $product["category_name"]
                );

                ?>

            </p>



            <!-- PRICE -->

            <div class="product-price">

                Rs.
                <?php

                echo number_format(
                    $product["price"],
                    2
                );

                ?>

            </div>



            <!-- DESCRIPTION -->

            <div class="product-description">

                <h3>
                    Description
                </h3>


                <p>

                    <?php

                    echo htmlspecialchars(
                        $product["description"]
                    );

                    ?>

                </p>

            </div>



            <!-- ADD TO CART -->

            <form
                method="POST"
                action="add_to_cart.php"
                class="add-cart-form"
            >

        <?php echo csrf_field(); ?>


                <input
                    type="hidden"
                    name="product_id"
                    value="<?php
                        echo (int) $product["product_id"];
                    ?>"
                >


                <div class="quantity-group">

                    <label for="quantity">
                        Quantity
                    </label>


                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        value="1"
                        min="1"
                        max="<?php echo MAX_QTY_PER_ITEM; ?>"
                        <?php echo $product["is_available"] ? "" : "disabled"; ?>
                        required
                    >

                </div>


                <?php if ($product["is_available"]) { ?>

                    <button
                        type="submit"
                        class="add-cart-button"
                    >
                        Add to Cart
                    </button>

                <?php } else { ?>

                    <button
                        type="button"
                        class="add-cart-button"
                        disabled
                    >
                        Currently Unavailable
                    </button>

                <?php } ?>


            </form>


            <!-- EXTRA INFORMATION -->

            <div class="product-benefits">

                <div>
                    🧁 Freshly Baked
                </div>

                <div>
                    ❤️ Made with Love
                </div>

                <div>
                    🎁 Perfect for Every Occasion
                </div>

            </div>


        </div>

    </div>


</main>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>