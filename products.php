<?php

require_once __DIR__ . "/includes/bootstrap.php";


/* ---------- Search, category filter and sorting ---------- */

$search = trim($_GET["q"] ?? "");
$category_filter = (int) ($_GET["category"] ?? 0);
$sort = $_GET["sort"] ?? "newest";

$sort_options = [
    "newest"     => "products.product_id DESC",
    "price_low"  => "products.price ASC",
    "price_high" => "products.price DESC",
    "name"       => "products.product_name ASC"
];

if (!isset($sort_options[$sort])) {
    $sort = "newest";
}

$where = ["products.is_active = 1"];
$types = "";
$params = [];

if ($search !== "") {
    $where[] = "(products.product_name LIKE ? OR products.description LIKE ?)";
    $like = "%" . addcslashes(mb_substr($search, 0, 100), "%_\\") . "%";
    $types .= "ss";
    $params[] = $like;
    $params[] = $like;
}

if ($category_filter > 0) {
    $where[] = "products.category_id = ?";
    $types .= "i";
    $params[] = $category_filter;
}

$categories = db_select(
    $conn,
    "SELECT category_id, category_name FROM categories ORDER BY category_name"
);

$meta_description = "Browse cakes, cupcakes, cookies and desserts from Little Treats Bakery.";

?>

<!DOCTYPE html>

<html>

<head>

    <title>Products - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>


<body>


<!-- ================= HEADER ================= -->

<?php include __DIR__ . "/includes/header.php"; ?>



<!-- ================= PAGE INTRO ================= -->

<section class="products-hero">

    <p class="section-label">
        LITTLE TREATS BAKERY
    </p>

    <h2>
        Our Delicious Treats
    </h2>

    <p>
        Discover freshly prepared cakes, brownies and
        other sweet treats made with care.
    </p>

</section>



<!-- ================= PRODUCTS ================= -->

<main id="main-content" class="products-container">


    <div class="products-heading">

        <h2>
            Explore Our Collection
        </h2>

        <p>
            Find something delicious for every occasion.
        </p>

    </div>


    <!-- SEARCH / FILTER / SORT -->

    <form method="GET" action="products.php" class="product-filters" role="search">

        <label class="visually-hidden" for="filter-q">Search products</label>
        <input
            type="search"
            id="filter-q"
            name="q"
            value="<?php echo e($search); ?>"
            placeholder="Search treats..."
            maxlength="100"
        >

        <label class="visually-hidden" for="filter-category">Category</label>
        <select id="filter-category" name="category">

            <option value="0">All categories</option>

            <?php while ($cat = $categories->fetch_assoc()) { ?>

                <option
                    value="<?php echo (int) $cat["category_id"]; ?>"
                    <?php echo $category_filter === (int) $cat["category_id"] ? "selected" : ""; ?>
                >
                    <?php echo e($cat["category_name"]); ?>
                </option>

            <?php } ?>

        </select>

        <label class="visually-hidden" for="filter-sort">Sort by</label>
        <select id="filter-sort" name="sort">
            <option value="newest" <?php echo $sort === "newest" ? "selected" : ""; ?>>Newest</option>
            <option value="price_low" <?php echo $sort === "price_low" ? "selected" : ""; ?>>Price: low to high</option>
            <option value="price_high" <?php echo $sort === "price_high" ? "selected" : ""; ?>>Price: high to low</option>
            <option value="name" <?php echo $sort === "name" ? "selected" : ""; ?>>Name A-Z</option>
        </select>

        <button type="submit">Apply</button>

        <?php if ($search !== "" || $category_filter > 0 || $sort !== "newest") { ?>
            <a href="products.php" class="filter-clear">Clear</a>
        <?php } ?>

    </form>


    <div class="product-grid">


        <?php

        $sql = "SELECT products.*, categories.category_name
                FROM products

                LEFT JOIN categories
                ON products.category_id = categories.category_id

                WHERE " . implode(" AND ", $where) . "

                ORDER BY " . $sort_options[$sort];


        $result = db_select($conn, $sql, $types, $params);


        if ($result && mysqli_num_rows($result) > 0) {


            while ($product = mysqli_fetch_assoc($result)) {

        ?>


        <!-- PRODUCT CARD -->

        <div class="product-card">


            <!-- PRODUCT IMAGE -->

            <div class="product-image-placeholder">

                <?php

                $image_file = $product["image"] ?? "";

                $image_path = __DIR__ . "/uploads/products/" . $image_file;

                if (
                    $image_file !== "" &&
                    is_file($image_path)
                ) {

                ?>

                    <img
                        src="uploads/products/<?php echo htmlspecialchars($image_file); ?>"
                        alt="<?php echo htmlspecialchars($product["product_name"]); ?>"
                        loading="lazy"
                        decoding="async"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;"
                    >

                <?php

                } else {

                ?>

                    🧁

                <?php

                }

                ?>

            </div>



            <div class="product-card-content">


                <span class="product-category">

                    <?php

                    echo htmlspecialchars(
                        $product["category_name"]
                    );

                    ?>

                </span>


                <h3>

                    <?php

                    echo htmlspecialchars(
                        $product["product_name"]
                    );

                    ?>

                </h3>


                <p class="product-description">

                    <?php

                    echo htmlspecialchars(
                        $product["description"]
                    );

                    ?>

                </p>


                <div class="product-bottom">


                    <strong class="product-price">

                        Rs.
                        <?php

                        echo number_format(
                            $product["price"],
                            2
                        );

                        ?>

                    </strong>


                    <a
                        href="product_details.php?id=<?php echo (int)$product["product_id"]; ?>"
                        class="product-button"
                    >
                        <?php
                        echo $product["is_available"]
                            ? "View Details"
                            : "Currently Unavailable";
                        ?>
                    </a>


                </div>


            </div>


        </div>


        <?php

            }


        } else {

        ?>


            <div class="no-products">

                <div class="no-products-icon" aria-hidden="true">
                    🧁
                </div>

                <h3>
                    No Products Available
                </h3>

                <p>
                    Please check back soon for our delicious treats.
                </p>

            </div>


        <?php

        }

        ?>


    </div>


</main>



<!-- ================= CTA ================= -->

<section class="products-cta">

    <h2>
        Found Something You Love?
    </h2>

    <p>
        Click on a product to see more details and place your order.
    </p>


    <?php if (!isset($_SESSION["user_id"])) { ?>

        <a href="register.php" class="primary-button">
            Create Your Account
        </a>

    <?php } else { ?>

        <a href="cart.php" class="primary-button">
            View My Cart
        </a>

    <?php } ?>

</section>



<!-- ================= FOOTER ================= -->

<?php include __DIR__ . "/includes/footer.php"; ?>


</body>

</html>