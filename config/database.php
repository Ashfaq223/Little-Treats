<?php

require_once __DIR__ . "/../includes/security.php";

/*
 * Database credentials live in config/db_credentials.php
 * (not committed - see config/db_credentials.sample.php).
 */

$credentials_file = __DIR__ . "/db_credentials.php";

if (!is_file($credentials_file)) {

    error_log("Little Treats: config/db_credentials.php is missing.");

    http_response_code(503);

    exit("The service is temporarily unavailable.");

}

$db_config = require $credentials_file;

// Throw exceptions instead of printing raw SQL errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli(
        $db_config["host"],
        $db_config["username"],
        $db_config["password"],
        $db_config["database"]
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log("Little Treats DB connection failed: " . $e->getMessage());

    http_response_code(503);

    exit("The service is temporarily unavailable.");

}

unset($db_config, $credentials_file);

?>
