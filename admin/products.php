<?php

require_once __DIR__ . "/admin_auth.php";


/* ---------- Search, category filter and paging ---------- */

$search = trim($_GET["q"] ?? "");
$category_filter = (int) ($_GET["category"] ?? 0);
$page = current_page();

$where = ["products.is_active = 1"];
$types = "";
$params = [];

if ($search !== "") {

    $like = "%" . addcslashes(mb_substr($search, 0, 100), "%_\\") . "%";

    $where[] = "(products.product_name LIKE ? OR products.description LIKE ?)";
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;

}

if ($category_filter > 0) {

    $where[] = "products.category_id = ?";
    $types .= "i";
    $params[] = $category_filter;

}

$from = "FROM products
         LEFT JOIN categories ON products.category_id = categories.category_id
         WHERE " . implode(" AND ", $where);

$total_products = (int) db_select($conn, "SELECT COUNT(*) AS total " . $from, $types, $params)
    ->fetch_assoc()["total"];

$products_result = db_select(
    $conn,
    "SELECT products.*, categories.category_name " . $from .
    " ORDER BY products.product_id DESC LIMIT ? OFFSET ?",
    $types . "ii",
    array_merge($params, [PAGE_SIZE, ($page - 1) * PAGE_SIZE])
);

$category_list = db_select($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Manage Products - Little Treats Bakery</title>

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

        <h2>🧁 Manage Products</h2>

        <p>
            Add, edit and manage all bakery products.
        </p>

        <a href="add_product.php" class="admin-button">
            + Add New Product
        </a>

    </div>



    <!-- ================= PRODUCT TABLE ================= -->

    <div class="admin-card">

        <h3>Product List</h3>


        <form method="GET" class="admin-filters" role="search">

            <label class="visually-hidden" for="f-q">Search</label>
            <input type="search" id="f-q" name="q" value="<?php echo e($search); ?>" placeholder="Search products">

            <label class="visually-hidden" for="f-category">Category</label>
            <select id="f-category" name="category">

                <option value="0">All categories</option>

                <?php while ($c = $category_list->fetch_assoc()) { ?>
                    <option
                        value="<?php echo (int) $c["category_id"]; ?>"
                        <?php echo $category_filter === (int) $c["category_id"] ? "selected" : ""; ?>
                    >
                        <?php echo e($c["category_name"]); ?>
                    </option>
                <?php } ?>

            </select>

            <button type="submit">Filter</button>

            <?php if ($search !== "" || $category_filter > 0) { ?>
                <a href="products.php">Clear</a>
            <?php } ?>

        </form>


        <div class="table-container">

            <table>

                <tr>

                    <th>ID</th>

                    <th>Product Name</th>

                    <th>Description</th>

                    <th>Price</th>

                    <th>Category</th>

                    <th>Actions</th>

                </tr>


                <?php

                $result = $products_result;


                if (mysqli_num_rows($result) > 0) {

                    while ($product = mysqli_fetch_assoc($result)) {

                ?>

                <tr>

                    <td>
                        <?php echo $product["product_id"]; ?>
                    </td>


                    <td>

                        <strong>
                            <?php echo htmlspecialchars($product["product_name"]); ?>
                        </strong>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars($product["description"]);

                        ?>

                    </td>


                    <td>

                        <strong>
                            Rs. <?php echo number_format($product["price"], 2); ?>
                        </strong>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $product["category_name"] ?? "Uncategorized"
                        );

                        ?>

                    </td>


                    <td>

                        <div class="table-actions"><a
                            href="edit_product.php?id=<?php echo $product["product_id"]; ?>"
                            class="table-button edit-button"
                        >
                            Edit
                        </a>


                        <?php if (admin_is_owner()) { ?><form
                            method="POST"
                            action="delete_product.php"
                            class="inline-form"
                            data-confirm="Are you sure you want to delete this product?"
                        >

                            <?php echo csrf_field(); ?>

                            <input
                                type="hidden"
                                name="id"
                                value="<?php echo (int) $product["product_id"]; ?>"
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

                        <td colspan="6">

                            No products found.

                        </td>

                    </tr>

                <?php

                }

                ?>

            </table>

        </div>

        <?php echo pager_html($page, $total_products); ?>

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