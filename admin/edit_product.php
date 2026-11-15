<?php

require_once __DIR__ . "/admin_auth.php";
require_once __DIR__ . "/../includes/uploads.php";
require_once __DIR__ . "/../includes/product_validation.php";

$message = "";


// Get product ID from URL
if (!isset($_GET["id"])) {

    header("Location: products.php");
    exit();

}

$product_id = (int) $_GET["id"];


// Get existing product (removed products cannot be edited)
$result = db_select(
    $conn,
    "SELECT * FROM products
     WHERE product_id = ?
     AND is_active = 1",
    "i",
    [$product_id]
);

if ($result->num_rows != 1) {

    echo "Product not found.";
    exit();

}

$product = $result->fetch_assoc();


// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $product_name = trim($_POST["product_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $category_id = trim($_POST["category_id"] ?? "");
    $is_available = isset($_POST["is_available"]) ? 1 : 0;

    $image_upload = $_FILES["product_image"] ?? ["error" => UPLOAD_ERR_NO_FILE];


    // Same validation rules as Add Product
    $validation_error = lt_validate_product_fields(
        $conn,
        $product_name,
        $description,
        $price,
        $category_id
    );

    $new_image_name = null;

    if ($validation_error !== "") {

        $message = $validation_error;

    } else {

        // A new image is optional when editing
        if ($image_upload["error"] !== UPLOAD_ERR_NO_FILE) {

            $image_error = null;

            $new_image_name = lt_save_uploaded_image(
                $image_upload,
                __DIR__ . "/../uploads/products/",
                $image_error
            );

            if ($new_image_name === null) {

                $message = $image_error;

            }

        }

    }


    if ($message === "") {

        try {

            if ($new_image_name !== null) {

                db_exec(
                    $conn,
                    "UPDATE products SET
                         product_name = ?,
                         description = ?,
                         price = ?,
                         category_id = ?,
                         is_available = ?,
                         image = ?
                     WHERE product_id = ?",
                    "ssdiisi",
                    [
                        $product_name,
                        $description,
                        (float) $price,
                        (int) $category_id,
                        $is_available,
                        $new_image_name,
                        $product_id
                    ]
                );

                // Remove the replaced picture (only files inside uploads/products)
                $old_image = basename((string) $product["image"]);

                $old_path = __DIR__ . "/../uploads/products/" . $old_image;

                if ($old_image !== "" && is_file($old_path)) {

                    unlink($old_path);

                }

            } else {

                db_exec(
                    $conn,
                    "UPDATE products SET
                         product_name = ?,
                         description = ?,
                         price = ?,
                         category_id = ?,
                         is_available = ?
                     WHERE product_id = ?",
                    "ssdiii",
                    [
                        $product_name,
                        $description,
                        (float) $price,
                        (int) $category_id,
                        $is_available,
                        $product_id
                    ]
                );

            }

            audit_log($conn, "update", "product", $product_id, "Edited: " . $product_name);

            header("Location: products.php");
            exit();

        } catch (Throwable $ex) {

            error_log("Edit product failed: " . $ex->getMessage());

            $message = "Error updating product.";

        }

    }

    // Keep what the admin typed when showing an error
    $product["product_name"] = $product_name;
    $product["description"] = $description;
    $product["price"] = $price;
    $product["category_id"] = $category_id;
    $product["is_available"] = $is_available;

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Product - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">

    <div class="admin-welcome">

        <h2>✏️ Edit Product</h2>

        <p>Update this product's details, picture and availability.</p>

    </div>

    <div class="admin-card">

        <h3>Product Information</h3>

        <?php if ($message !== "") { ?>

            <div class="message error" role="alert">
                <?php echo e($message); ?>
            </div>

        <?php } ?>

        <form method="POST" enctype="multipart/form-data">

            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input
                    type="text"
                    id="product_name"
                    name="product_name"
                    value="<?php echo e($product["product_name"]); ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    maxlength="2000"
                    required
                ><?php echo e($product["description"]); ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (Rs.)</label>
                <input
                    type="number"
                    id="price"
                    name="price"
                    value="<?php echo e($product["price"]); ?>"
                    min="0.01"
                    step="0.01"
                    required
                >
            </div>

            <div class="form-group">

                <label for="category_id">Category</label>

                <select id="category_id" name="category_id" required>

                    <?php

                    $category_result = db_select(
                        $conn,
                        "SELECT category_id, category_name
                         FROM categories
                         ORDER BY category_name"
                    );

                    while ($category = $category_result->fetch_assoc()) {

                    ?>

                        <option
                            value="<?php echo (int) $category["category_id"]; ?>"
                            <?php echo ((int) $category["category_id"] === (int) $product["category_id"]) ? "selected" : ""; ?>
                        >
                            <?php echo e($category["category_name"]); ?>
                        </option>

                    <?php } ?>

                </select>

            </div>

            <div class="form-group">

                <label for="product_image">Product Image</label>

                <?php if (!empty($product["image"])) { ?>

                    <p>
                        <img
                            src="../uploads/products/<?php echo e(rawurlencode($product["image"])); ?>"
                            alt="Current picture of <?php echo e($product["product_name"]); ?>"
                            style="max-width: 140px; border-radius: 8px;"
                        >
                    </p>

                <?php } ?>

                <input
                    type="file"
                    id="product_image"
                    name="product_image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Leave empty to keep the current image.
                    JPG, PNG or WEBP, maximum 5 MB.
                </small>

            </div>

            <div class="form-group">

                <label>
                    <input
                        type="checkbox"
                        name="is_available"
                        value="1"
                        <?php echo $product["is_available"] ? "checked" : ""; ?>
                    >
                    Available to order
                </label>

            </div>

            <div class="admin-form-actions">

                <button type="submit" class="admin-button">
                    Update Product
                </button>

                <a href="products.php" class="back-link">Cancel</a>

            </div>

        </form>

    </div>

</main>

</body>

</html>