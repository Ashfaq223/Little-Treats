<?php

require_once __DIR__ . "/admin_auth.php";

$message = "";
$message_type = "";

$category_name = "";


// =========================================
// HANDLE ADD CATEGORY FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    $category_name = trim($_POST["category_name"] ?? "");


    // =========================================
    // VALIDATION
    // =========================================

    if ($category_name === "") {

        $message = "Please enter a category name.";
        $message_type = "error";

    } elseif (strlen($category_name) > 100) {

        $message = "Category name is too long.";
        $message_type = "error";

    } else {


        // =========================================
        // CHECK FOR DUPLICATE CATEGORY
        // =========================================

        $check_sql = "SELECT category_id
                      FROM categories
                      WHERE category_name = ?";

        $check_stmt = mysqli_prepare($conn, $check_sql);


        if ($check_stmt) {

            mysqli_stmt_bind_param(
                $check_stmt,
                "s",
                $category_name
            );

            mysqli_stmt_execute($check_stmt);

            mysqli_stmt_store_result($check_stmt);


            if (mysqli_stmt_num_rows($check_stmt) > 0) {

                $message = "This category already exists.";
                $message_type = "error";

                mysqli_stmt_close($check_stmt);

            } else {

                mysqli_stmt_close($check_stmt);


                // =========================================
                // INSERT CATEGORY
                // =========================================

                $sql = "INSERT INTO categories (category_name)
                        VALUES (?)";

                $stmt = mysqli_prepare($conn, $sql);


                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "s",
                        $category_name
                    );


                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        audit_log($conn, "create", "category", (int) $conn->insert_id, "Added: " . $category_name);

                        header("Location: categories.php");
                        exit();

                    } else {

                        $message = "Unable to add the category. Please try again.";
                        $message_type = "error";

                        mysqli_stmt_close($stmt);

                    }

                } else {

                    $message = "Unable to process the request. Please try again.";
                    $message_type = "error";

                }

            }

        } else {

            $message = "Unable to check the category. Please try again.";
            $message_type = "error";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Category - Little Treats Bakery</title>

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


    <!-- PAGE INTRO -->

    <div class="admin-welcome">

        <h2>
            📂 Add New Category
        </h2>

        <p>
            Create a new category for organizing bakery products.
        </p>

    </div>



    <!-- =========================================
         CATEGORY FORM CARD
    ========================================== -->

    <div class="admin-card">


        <h3>
            Category Information
        </h3>


        <!-- MESSAGE -->

        <?php if ($message !== "") { ?>

            <div
                class="message <?php echo htmlspecialchars($message_type); ?>"
            >

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>



        <!-- CATEGORY FORM -->

        <form
            method="POST"
            action="add_category.php"
        >

        <?php echo csrf_field(); ?>


            <div class="form-group">

                <label for="category_name">
                    Category Name
                </label>


                <input
                    type="text"
                    id="category_name"
                    name="category_name"
                    value="<?php echo htmlspecialchars($category_name); ?>"
                    placeholder="Enter category name"
                    maxlength="100"
                    required
                >


                <small class="form-help">
                    Example: Cakes, Pastries, Cookies or Beverages.
                </small>

            </div>



            <!-- BUTTONS -->

            <div class="admin-form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    + Add Category
                </button>


                <a
                    href="categories.php"
                    class="back-link"
                >
                    Cancel
                </a>

            </div>


        </form>

    </div>



    <!-- BACK TO CATEGORIES -->

    <br>

    <a
        href="categories.php"
        class="back-link"
    >
        ← Back to Categories
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