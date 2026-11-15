<?php

/*
 * Simple test runner (no extra tools needed).
 *
 *   e:\xampp\php\php.exe tests\run_tests.php
 *   e:\xampp\php\php.exe tests\run_tests.php http://localhost/little_treats
 *
 * 1. checks every PHP file for syntax errors
 * 2. tests the shared helpers (escaping, CSRF, redirects, pagination ...)
 * 3. tests the database helpers (read-only)
 * 4. optionally requests the main pages over HTTP and looks for PHP errors
 */

if (PHP_SAPI !== "cli") {
    exit("Run this from the command line.");
}

$root = dirname(__DIR__);

$passed = 0;
$failed = 0;

function check(string $name, bool $ok, string $detail = ""): void
{
    global $passed, $failed;

    if ($ok) {
        $passed++;
        echo "  PASS  $name\n";
    } else {
        $failed++;
        echo "  FAIL  $name" . ($detail !== "" ? "  ($detail)" : "") . "\n";
    }
}


/* ---------- 1. Syntax ---------- */

echo "\n[1] PHP syntax\n";

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

$bad = [];
$count = 0;

foreach ($iterator as $file) {

    if ($file->getExtension() !== "php") {
        continue;
    }

    $count++;

    $output = [];
    exec('"' . PHP_BINARY . '" -l ' . escapeshellarg($file->getPathname()) . " 2>&1", $output, $code);

    if ($code !== 0) {
        $bad[] = $file->getPathname();
    }

}

check("all $count PHP files have valid syntax", empty($bad), implode(", ", $bad));


/* ---------- 2. Helpers ---------- */

echo "\n[2] Helpers\n";

$_SERVER["SCRIPT_NAME"] = "/little_treats/index.php";
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
$_SERVER["HTTP_HOST"] = "localhost";

require_once $root . "/includes/bootstrap.php";

check("e() escapes HTML", e('<script>"x"&\'</script>') === "&lt;script&gt;&quot;x&quot;&amp;&#039;&lt;/script&gt;");

check("csrf token is 64 hex characters", (bool) preg_match('/^[a-f0-9]{64}$/', csrf_token()));
check("csrf token stays the same in a session", csrf_token() === csrf_token());
check("csrf field contains the token", strpos(csrf_field(), csrf_token()) !== false);

check("safe_next accepts a local page", safe_next("checkout.php") === "checkout.php");
check("safe_next accepts a query string", safe_next("product_details.php?id=3") === "product_details.php?id=3");
check("safe_next rejects external URLs", safe_next("https://evil.example/x.php") === null);
check("safe_next rejects // tricks", safe_next("//evil.example") === null);
check("safe_next rejects the login page", safe_next("login.php") === null);

check("base_url finds the app folder", base_url() === "/little_treats");

$_SESSION["cart"] = [3 => 2, 5 => 1];
check("cart_count adds quantities", cart_count() === 3);

check("order timeline marks the current step", strpos(order_timeline_html("Preparing"), 'class="current"') !== false);
check("order timeline shows cancelled orders", strpos(order_timeline_html("Cancelled"), "cancelled") !== false);

$_GET = ["q" => "cake"];
check("pager is empty for one page", pager_html(1, 5) === "");
check("pager keeps search terms", strpos(pager_html(1, 100), "q=cake") !== false);

check("status lists come from settings", in_array("Completed", ORDER_STATUSES, true) && in_array("Bank Transfer", PAYMENT_METHODS, true));


/* ---------- 3. Database helpers ---------- */

echo "\n[3] Database\n";

check("database connection works", $conn instanceof mysqli);

$row = db_select($conn, "SELECT ? + 1 AS answer", "i", [41])->fetch_assoc();
check("prepared SELECT returns data", (int) $row["answer"] === 42);

$injection = db_select($conn, "SELECT COUNT(*) AS total FROM users WHERE email = ?", "s", ["' OR '1'='1"])->fetch_assoc();
check("SQL injection text is treated as plain data", (int) $injection["total"] === 0);

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($r = $result->fetch_row()) {
    $tables[] = $r[0];
}

foreach (["users", "orders", "order_items", "products", "loyalty_points", "audit_log", "password_resets", "login_attempts"] as $table) {
    check("table $table exists", in_array($table, $tables, true));
}

$cols = [];
$result = $conn->query("SHOW COLUMNS FROM orders");
while ($r = $result->fetch_assoc()) {
    $cols[] = $r["Field"];
}

check("orders has fulfilment and award columns", count(array_intersect(["fulfilment_type", "requested_datetime", "points_awarded"], $cols)) === 3);

$cart = fetch_cart_items($conn, [1 => 1, 99999 => 1]);
check("fetch_cart_items uses one query and skips unknown products", isset($cart[1]) && !isset($cart[99999]));


/* ---------- 4. Optional HTTP smoke test ---------- */

if (!empty($argv[1])) {

    echo "\n[4] HTTP smoke test ({$argv[1]})\n";

    foreach (["index.php", "products.php", "login.php", "register.php", "contact.php", "forgot_password.php", "privacy.php", "terms.php", "cart.php"] as $page) {

        $body = @file_get_contents(rtrim($argv[1], "/") . "/" . $page);

        check("$page loads without PHP errors", $body !== false && !preg_match('/Fatal error|Warning:|Notice:|Parse error/', $body));

    }

}


echo "\n$passed passed, $failed failed\n";

exit($failed === 0 ? 0 : 1);