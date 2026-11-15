<?php

require_once __DIR__ . "/includes/bootstrap.php";


if (isset($_GET["id"])) {

    $product_id = (int) $_GET["id"];


    if (isset($_SESSION["cart"][$product_id])) {

        unset($_SESSION["cart"][$product_id]);

    }

}


header("Location: cart.php");
exit();

?>