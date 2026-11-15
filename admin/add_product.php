<?php

require_once __DIR__ . "/admin_auth.php";
require_once __DIR__ . "/../includes/uploads.php";
require_once __DIR__ . "/../includes/product_validation.php";

$message = "";
$message_type = "";

$product_name = "";
$description = "";
$price = "";
$category_id = "";
$is_available = 1;


// =========================================
// HANDLE ADD PRODUCT FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    $product_name = trim($_POST["product_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $category_id = trim($_POST["category_id"] ?? "");
    $is_available = isset($_POST["is_available"]) ? 1 : 0;

    $image_upload = $_FILES["product_image"] ?? ["error" => UPLOAD_ERR_NO_FILE];


    // =========================================
    // VALIDATION
    // =========================================

    $validation_error = lt_validate_product_fields(
        $conn,
        $product_name,
        $description,
        $price,
        $category_id
    );

    if ($validation_error !== "") {

        $message = $validation_error;
        $message_type = "error";

    } elseif ($image_upload["error"] === UPLOAD_ERR_NO_FILE) {

        $message = "Please select a product image.";
        $message_type = "error";

    } else {

        // =========================================
        // SAVE IMAGE (random name, real type check)
        // =========================================

        $image_error = null;

        $new_image_name = lt_save_uploaded_image(
            $image_upload,
            __DIR__ . "/../uploads/products/",
            $image_error
        );

        if ($new_image_name === null) {

            $message = $image_error;
            $message_type = "error";

        } else {

            // =========================================
            // INSERT PRODUCT
            // =========================================

            $price_value = (float)$price;
            $category_value = (int)$category_id;

            try {

                db_exec(
                    $conn,
                    "INSERT INTO products
                     (
                         category_id,
                         product_name,
                         description,
                         price,
                         image,
                         is_available
                     )
                     VALUES (?, ?, ?, ?, ?, ?)",
                    "issdsi",
                    [
                        $category_value,
                        $product_name,
                        $description,
                        $price_value,
                        $new_image_name,
                        $is_available
                    ]
                );

                audit_log($conn, "create", "product", (int) $conn->insert_id, "Added: " . $product_name);

                $message = "Product added successfully with image.";
                $message_type = "success";

                // Clear form after successful insertion
                $product_name = "";
                $description = "";
                $price = "";
                $category_id = "";
                $is_available = 1;

            } catch (Throwable $ex) {

                error_log("Add product failed: " . $ex->getMessage());

                // Delete uploaded image if database insert fails
                $uploaded_path = __DIR__ . "/../uploads/products/" . $new_image_name;

                if (is_file($uploaded_path)) {
                    unlink($uploaded_path);
                }

                $message = "Unable to add the product. Please try again.";
                $message_type = "error";

            }

        }

    }

}



// =========================================
// GET CATEGORIES
// =========================================

$category_sql = "SELECT category_id, category_name
                 FROM categories
                 ORDER BY category_name ASC";

$category_result = mysqli_query($conn, $category_sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Product - Little Treats Bakery</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

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


    <div class="admin-welcome">

        <h2>
            🧁 Add New Product
        </h2>

        <p>
            Add a new bakery product to your product catalogue.
        </p>

    </div>



    <!-- =========================================
         FORM CARD
    ========================================== -->

    <div class="admin-card">

        <h3>
            Product Information
        </h3>


        <!-- MESSAGE -->

        <?php if ($message !== "") { ?>

            <div
                class="message <?php echo htmlspecialchars($message_type); ?>"
            >

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>



        <!-- PRODUCT FORM -->

        <form
            method="POST"
            action="add_product.php"
            enctype="multipart/form-data"
        >

            <?php echo csrf_field(); ?>


            <!-- PRODUCT NAME -->

            <div class="form-group">

                <label for="product_name">
                    Product Name
                </label>

                <input
                    type="text"
                    id="product_name"
                    name="product_name"
                    value="<?php echo htmlspecialchars($product_name); ?>"
                    placeholder="Enter product name"
                    maxlength="100"
                    required
                >

            </div>



            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Enter product description"
                    maxlength="2000"
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>

            </div>



            <!-- PRICE -->

            <div class="form-group">

                <label for="price">
                    Price (Rs.)
                </label>

                <input
                    type="number"
                    id="price"
                    name="price"
                    value="<?php echo htmlspecialchars($price); ?>"
                    placeholder="Enter product price"
                    min="0.01"
                    step="0.01"
                    required
                >

            </div>



            <!-- CATEGORY -->

            <div class="form-group">

                <label for="category_id">
                    Category
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                >

                    <option value="">
                        Select Category
                    </option>


                    <?php

                    if (
                        $category_result &&
                        mysqli_num_rows($category_result) > 0
                    ) {

                        while (
                            $category = mysqli_fetch_assoc(
                                $category_result
                            )
                        ) {

                    ?>

                        <option
                            value="<?php echo (int)$category["category_id"]; ?>"
                            <?php
                            if (
                                (string)$category_id ===
                                (string)$category["category_id"]
                            ) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $category["category_name"]
                            );
                            ?>

                        </option>

                    <?php

                        }

                    } else {

                    ?>

                        <option value="" disabled>
                            No categories available
                        </option>

                    <?php

                    }

                    ?>

                </select>

            </div>



            <!-- PRODUCT IMAGE -->

            <div class="form-group">

                <label for="product_image">
                    Product Image
                </label>

                <input
                    type="file"
                    id="product_image"
                    name="product_image"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <small>
                    Allowed formats: JPG, JPEG, PNG and WEBP.
                    Maximum size: 5 MB.
                </small>

            </div>



            <!-- AVAILABILITY -->

            <div class="form-group">

                <label>
                    <input
                        type="checkbox"
                        name="is_available"
                        value="1"
                        <?php echo $is_available ? "checked" : ""; ?>
                    >
                    Available to order
                </label>

            </div>



            <!-- BUTTONS -->

            <div class="admin-form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    + Add Product
                </button>


                <a
                    href="products.php"
                    class="back-link"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>



    <!-- BACK TO PRODUCTS -->

    <br>

    <a
        href="products.php"
        class="back-link"
    >
        ← Back to Products
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
