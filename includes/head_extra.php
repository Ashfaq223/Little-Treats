<?php

// Shared <head> extras: description, icon, colour, scripts.
// A page can set $meta_description before including this file.

$lt_base = e(base_url());

$lt_description = $meta_description
    ?? "Freshly baked cakes, cupcakes, cookies and desserts from Little Treats Bakery. Order online for delivery or pickup.";

?>
<meta name="description" content="<?php echo e($lt_description); ?>">
<meta name="theme-color" content="#8B4513">
<?php if (lt_is_admin_area()) { ?>
<meta name="robots" content="noindex, nofollow">
<?php } ?>
<link rel="icon" type="image/png" href="<?php echo $lt_base; ?>/assets/images/logo.png">
<script src="<?php echo $lt_base; ?>/assets/js/app.js" defer></script>