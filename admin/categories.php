<?php

require_once __DIR__ . "/admin_auth.php";

?>

<!DOCTYPE html>

<html>

<head>

    <title>Manage Categories - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/../includes/admin_header.php"; ?>



<!-- ================= MAIN ================= -->

<main id="main-content" class="admin-container">


    <div class="admin-welcome">

        <h2>📂 Manage Categories</h2>

        <p>
            Create and manage the categories used for your bakery products.
        </p>

        <a href="add_category.php" class="admin-button">
            + Add New Category
        </a>

    </div>



    <!-- ================= CATEGORY TABLE ================= -->

    <div class="admin-card">

        <h3>Category List</h3>


        <div class="table-container">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Category Name</th>

                    <th>Actions</th>

                </tr>


                <?php

                $sql = "SELECT *
                        FROM categories
                        ORDER BY category_id DESC";

                $result = mysqli_query($conn, $sql);


                if (mysqli_num_rows($result) > 0) {

                    while ($category = mysqli_fetch_assoc($result)) {

                ?>

                <tr>

                    <td>

                        <?php echo $category["category_id"]; ?>

                    </td>


                    <td>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $category["category_name"]
                            );

                            ?>

                        </strong>

                    </td>


                    <td>

                        <div class="table-actions"><a
                            href="edit_category.php?id=<?php echo $category["category_id"]; ?>"
                            class="table-button edit-button"
                        >
                            Edit
                        </a>


                        <?php if (admin_is_owner()) { ?><form
                            method="POST"
                            action="delete_category.php"
                            class="inline-form"
                            data-confirm="Are you sure you want to delete this category?"
                        >

                            <?php echo csrf_field(); ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?php echo (int) $category["category_id"]; ?>"
                            >

                            <button
                                type="submit"
                                class="table-button delete-button"
                            >
                                Delete
                            </button>

                        </form><?php } ?></div>

                    </td>

                </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="3">

                            No categories found.

                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>

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