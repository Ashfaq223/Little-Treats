<?php

require_once __DIR__ . "/bootstrap.php";

// Customer pages: send visitors to the login page and bring them back afterwards
if (!isset($_SESSION["user_id"])) {

    remember_next();

    header("Location: login.php");
    exit();

}
