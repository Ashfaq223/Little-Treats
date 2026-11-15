<?php

require_once __DIR__ . "/includes/bootstrap.php";

http_response_code(404);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Page Not Found - Little Treats Bakery</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex">

    <!-- Works from any address, because this page can be shown for any missing URL -->
    <base href="<?php echo e(site_origin()); ?>/">

    <link rel="stylesheet" href="assets/css/style.css">

    <?php include __DIR__ . "/includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<main id="main-content" class="legal-page" style="text-align: center;">

    <h2>We couldn't find that page</h2>

    <p>
        The page you are looking for may have been moved or no longer exists.
    </p>

    <p>
        <a href="index.php" class="primary-button">Back to Home</a>
        <a href="products.php" class="secondary-button">Browse Products</a>
    </p>

</main>

<?php include __DIR__ . "/includes/footer.php"; ?>

</body>

</html>