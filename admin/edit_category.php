<?php

require_once __DIR__ . "/admin_auth.php";

$message = "";


if (!isset($_GET["id"])) {

    header("Location: categories.php");
    exit();

}


$category_id = (int) $_GET["id"];


$result = db_select(
    $conn,
    "SELECT * FROM categories WHERE category_id = ?",
    "i",
    [$category_id]
);


if ($result->num_rows != 1) {

    echo "Category not found.";
    exit();

}


$category = $result->fetch_assoc();


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    csrf_verify();

    $category_name = trim($_POST["category_name"] ?? "");


    if ($category_name === "") {

        $message = "Please enter a category name.";

    } elseif (mb_strlen($category_name) > 100) {

        $message = "Category name is too long.";

    } else {

        // Another category must not already use this name
        $duplicate = db_select(
            $conn,
            "SELECT category_id
             FROM categories
             WHERE category_name = ?
             AND category_id <> ?",
            "si",
            [$category_name, $category_id]
        );

        if ($duplicate->num_rows > 0) {

            $message = "This category already exists.";

        } else {

            db_exec(
                $conn,
                "UPDATE categories
                 SET category_name = ?
                 WHERE category_id = ?",
                "si",
                [$category_name, $category_id]
            );

            audit_log($conn, "update", "category", $category_id, "Renamed to: " . $category_name);

            header("Location: categories.php");
            exit();

        }

    }

    $category["category_name"] = $category_name;

}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Category - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">

    <div class="admin-welcome">

        <h2>✏️ Edit Category</h2>

        <p>Change the name of this category.</p>

    </div>

    <div class="admin-card">

        <h3>Category Information</h3>

        <?php if ($message !== "") { ?>

            <div class="message error" role="alert">
                <?php echo e($message); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="category_name">Category Name</label>
                <input
                    type="text"
                    id="category_name"
                    name="category_name"
                    value="<?php echo e($category["category_name"]); ?>"
                    maxlength="100"
                    required
                >
            </div>

            <div class="admin-form-actions">

                <button type="submit" class="admin-button">
                    Update Category
                </button>

                <a href="categories.php" class="back-link">Cancel</a>

            </div>

        </form>

    </div>

</main>

</body>

</html>