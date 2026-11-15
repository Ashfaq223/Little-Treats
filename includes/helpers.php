<?php

/*
 * General helpers shared by every page: URLs, flash messages, cart,
 * login redirects, pagination, audit log, admin roles and email.
 */

if (defined("LT_HELPERS_LOADED")) {
    return;
}

define("LT_HELPERS_LOADED", true);


/* =========================================
   URLS
========================================= */

// Path of the application root, e.g. "/little_treats"
function base_url(): string
{
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");

    $dir = dirname($script);

    if (lt_is_admin_area()) {
        $dir = dirname($dir);
    }

    return rtrim($dir === "." ? "" : $dir, "/");
}

// https://host/little_treats
function site_origin(): string
{
    $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

    return ($https ? "https://" : "http://") .
           ($_SERVER["HTTP_HOST"] ?? "localhost") . base_url();
}

function redirect(string $location): void
{
    header("Location: " . $location);
    exit();
}


/* =========================================
   FLASH MESSAGES (shown once, as a toast)
========================================= */

function flash_set(string $type, string $text, ?string $link = null, ?string $link_text = null): void
{
    $_SESSION["flash"] = [
        "type" => $type,
        "text" => $text,
        "link" => $link,
        "link_text" => $link_text
    ];
}

function flash_pull(): ?array
{
    $flash = $_SESSION["flash"] ?? null;

    unset($_SESSION["flash"]);

    return $flash;
}


/* =========================================
   CART
========================================= */

function cart_count(): int
{
    if (empty($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
        return 0;
    }

    return (int) array_sum($_SESSION["cart"]);
}

// One query for the whole cart (instead of one query per item)
function fetch_cart_items(mysqli $conn, array $cart): array
{
    $ids = array_values(array_filter(
        array_map("intval", array_keys($cart)),
        function ($id) {
            return $id > 0;
        }
    ));

    if (empty($ids)) {
        return [];
    }

    $placeholders = implode(",", array_fill(0, count($ids), "?"));

    $result = db_select(
        $conn,
        "SELECT * FROM products
         WHERE is_active = 1
         AND product_id IN ($placeholders)",
        str_repeat("i", count($ids)),
        $ids
    );

    $items = [];

    while ($row = $result->fetch_assoc()) {
        $items[(int) $row["product_id"]] = $row;
    }

    return $items;
}


/* =========================================
   LOGIN REDIRECTS ("take me back to where I was")
========================================= */

function remember_next(?string $target = null): void
{
    $target = $target ?? basename($_SERVER["SCRIPT_NAME"] ?? "");

    if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "GET" && !empty($_SERVER["QUERY_STRING"])) {
        $target .= "?" . $_SERVER["QUERY_STRING"];
    }

    if (safe_next($target) !== null) {
        $_SESSION["next"] = $target;
    }
}

// Only plain page names of this site are allowed (no external redirects)
function safe_next(?string $target): ?string
{
    if ($target === null || $target === "") {
        return null;
    }

    if (!preg_match('/^[a-z_]+\.php(\?[A-Za-z0-9_=&%.\-]*)?$/', $target)) {
        return null;
    }

    if (preg_match('/^(login|register|logout|forgot_password|reset_password)\.php/', $target)) {
        return null;
    }

    return $target;
}

function pull_next(): string
{
    $target = safe_next($_SESSION["next"] ?? null);

    unset($_SESSION["next"]);

    return $target ?? "index.php";
}


/* =========================================
   PAGINATION
========================================= */

function current_page(): int
{
    return max(1, (int) ($_GET["page"] ?? 1));
}

function pager_html(int $page, int $total_items, int $per_page = PAGE_SIZE): string
{
    $pages = (int) ceil($total_items / $per_page);

    if ($pages <= 1) {
        return "";
    }

    $params = $_GET;

    $link = function (int $p, string $label, bool $current = false) use ($params) {

        $params["page"] = $p;

        $query = http_build_query($params);

        return '<a href="?' . e($query) . '"' .
               ($current ? ' class="current" aria-current="page"' : "") .
               ">" . e($label) . "</a>";

    };

    $html = '<nav class="pager" aria-label="Pagination">';

    if ($page > 1) {
        $html .= $link($page - 1, "Previous");
    }

    $start = max(1, $page - 2);
    $end = min($pages, $page + 2);

    for ($p = $start; $p <= $end; $p++) {
        $html .= $link($p, (string) $p, $p === $page);
    }

    if ($page < $pages) {
        $html .= $link($page + 1, "Next");
    }

    return $html . "</nav>";
}


/* =========================================
   ADMIN: ROLES AND AUDIT LOG
========================================= */

function admin_is_owner(): bool
{
    // Sessions started before roles existed: read the role from the database
    if (!isset($_SESSION["admin_role"]) && !empty($_SESSION["admin_id"])) {

        global $conn;

        $row = db_select(
            $conn,
            "SELECT role FROM admins WHERE admin_id = ?",
            "i",
            [(int) $_SESSION["admin_id"]]
        )->fetch_assoc();

        $_SESSION["admin_role"] = $row["role"] ?? "staff";

    }

    return ($_SESSION["admin_role"] ?? "") === "owner";
}

// Owner-only pages and actions
function admin_require_owner(): void
{
    if (!admin_is_owner()) {

        http_response_code(403);

        exit("You do not have permission to do this. Please ask the bakery owner.");

    }
}

function audit_log(
    mysqli $conn,
    string $action,
    string $entity,
    ?int $entity_id = null,
    string $details = ""
): void {

    try {

        db_exec(
            $conn,
            "INSERT INTO audit_log
             (admin_id, admin_name, action, entity, entity_id, details, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            "isssiss",
            [
                (int) ($_SESSION["admin_id"] ?? 0),
                (string) ($_SESSION["admin_name"] ?? ""),
                $action,
                $entity,
                $entity_id,
                mb_substr($details, 0, 500),
                client_ip()
            ]
        );

    } catch (Throwable $ex) {

        // Never break the admin's action because logging failed
        error_log("Audit log failed: " . $ex->getMessage());

    }
}


/* =========================================
   EMAIL
   Sends with mail() and always records the message in logs/mail.log
   (a local XAMPP has no mail server, so the log is how you can read it).
========================================= */

function lt_send_mail(string $to, string $subject, string $body): bool
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Prevent header injection
    $subject = str_replace(["\r", "\n"], " ", $subject);

    $headers = "From: " . SITE_NAME . " <" . MAIL_FROM . ">\r\n" .
               "Content-Type: text/plain; charset=UTF-8\r\n";

    $sent = false;

    try {

        $sent = MAIL_ENABLED ? @mail($to, $subject, $body, $headers) : false;

    } catch (Throwable $ex) {

        $sent = false;

    }

    $log = "[" . date("Y-m-d H:i:s") . "] " . ($sent ? "SENT" : (MAIL_ENABLED ? "FAILED TO SEND" : "LOGGED ONLY (mail disabled)")) .
           " to: " . $to . "\nSubject: " . $subject . "\n" . $body . "\n" .
           str_repeat("-", 60) . "\n";

    @file_put_contents(__DIR__ . "/../logs/mail.log", $log, FILE_APPEND | LOCK_EX);

    return $sent;
}


/* =========================================
   ORDER PROGRESS TIMELINE
========================================= */

function order_timeline_html(string $status): string
{
    if ($status === "Cancelled") {
        return '<p class="order-cancelled-note">This order was cancelled.</p>';
    }

    $flow = ORDER_FLOW;

    $current = array_search($status, $flow, true);

    if ($current === false) {
        $current = 0;
    }

    $html = '<ol class="order-timeline" aria-label="Order progress">';

    foreach ($flow as $index => $step) {

        $class = $index < $current ? "done" : ($index === $current ? "current" : "");

        $html .= '<li class="' . $class . '"' .
                 ($index === $current ? ' aria-current="step"' : "") . '>' .
                 '<span class="dot"></span>' .
                 '<span class="label">' . e($step) . '</span></li>';

    }

    return $html . "</ol>";
}