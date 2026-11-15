<?php

/*
 * Shared security helpers: error handling, hardened sessions, output
 * escaping, CSRF protection, prepared-statement helpers and login
 * throttling. Loaded automatically by config/database.php and by every
 * page that used to call session_start().
 */

if (defined("LT_SECURITY_LOADED")) {
    return;
}

define("LT_SECURITY_LOADED", true);

require_once __DIR__ . "/../config/settings.php";


/* =========================================
   ERROR HANDLING (S6)
   Never show SQL, file paths or stack traces to visitors.
========================================= */

error_reporting(E_ALL);
ini_set("display_errors", APP_DEBUG ? "1" : "0");
ini_set("log_errors", "1");

set_exception_handler(function ($e) {

    error_log(
        "Little Treats error: " . $e->getMessage() .
        " in " . $e->getFile() . ":" . $e->getLine()
    );

    if (!headers_sent()) {
        http_response_code(500);
    }

    echo "Something went wrong. Please try again later.";

});


/* =========================================
   OUTPUT ESCAPING (S8)
========================================= */

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}


/* =========================================
   SESSIONS (S4)
   - HttpOnly + SameSite cookies, Secure when on HTTPS
   - separate cookie for the admin area
   - idle timeout for the admin area
========================================= */

function lt_is_admin_area(): bool
{
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");

    return strpos($script, "/admin/") !== false;
}

function lt_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Command-line scripts (tests) have no browser cookie: use a plain array
    if (PHP_SAPI === "cli") {

        $_SESSION = $_SESSION ?? [];

        return;

    }

    $is_admin = lt_is_admin_area();

    $https = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";

    session_name($is_admin ? "LT_ADMIN_SESSID" : "LT_SESSID");

    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $https,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();

    if (
        $is_admin &&
        isset($_SESSION["last_activity"]) &&
        (time() - $_SESSION["last_activity"]) > ADMIN_IDLE_TIMEOUT
    ) {

        // Idle too long: drop the old session data
        $_SESSION = [];
        session_regenerate_id(true);

    }

    $_SESSION["last_activity"] = time();
}

lt_start_session();


/* =========================================
   CSRF PROTECTION (S3)
========================================= */

function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
           e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $submitted = $_POST["csrf_token"] ?? "";

    if (
        !is_string($submitted) ||
        !hash_equals(csrf_token(), $submitted)
    ) {

        http_response_code(403);

        exit(
            "Your session expired or the request was invalid. " .
            "Please go back, refresh the page and try again."
        );

    }
}


/* =========================================
   PREPARED STATEMENT HELPERS (S1)
========================================= */

// Run a SELECT and return a mysqli_result
function db_select(
    mysqli $conn,
    string $sql,
    string $types = "",
    array $params = []
): mysqli_result {

    $stmt = $conn->prepare($sql);

    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    $stmt->close();

    return $result;
}

// Run INSERT / UPDATE / DELETE and return the affected row count
function db_exec(
    mysqli $conn,
    string $sql,
    string $types = "",
    array $params = []
): int {

    $stmt = $conn->prepare($sql);

    if ($types !== "") {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $affected = $stmt->affected_rows;

    $stmt->close();

    return $affected;
}


/* =========================================
   LOGIN THROTTLING (S5)
========================================= */

function client_ip(): string
{
    return substr($_SERVER["REMOTE_ADDR"] ?? "unknown", 0, 45);
}

function login_is_locked(
    mysqli $conn,
    string $scope,
    string $identifier
): bool {

    $identifier = strtolower(substr($identifier, 0, 150));

    $by_identifier = db_select(
        $conn,
        "SELECT COUNT(*) AS total
         FROM login_attempts
         WHERE scope = ?
         AND identifier = ?
         AND attempted_at > (NOW() - INTERVAL " . (int) LOGIN_LOCK_MINUTES . " MINUTE)",
        "ss",
        [$scope, $identifier]
    )->fetch_assoc();

    if ((int) $by_identifier["total"] >= LOGIN_MAX_ATTEMPTS) {
        return true;
    }

    $by_ip = db_select(
        $conn,
        "SELECT COUNT(*) AS total
         FROM login_attempts
         WHERE scope = ?
         AND ip_address = ?
         AND attempted_at > (NOW() - INTERVAL " . (int) LOGIN_LOCK_MINUTES . " MINUTE)",
        "ss",
        [$scope, client_ip()]
    )->fetch_assoc();

    return (int) $by_ip["total"] >= LOGIN_MAX_ATTEMPTS_PER_IP;
}

function login_record_failure(
    mysqli $conn,
    string $scope,
    string $identifier
): void {

    db_exec(
        $conn,
        "INSERT INTO login_attempts (scope, identifier, ip_address)
         VALUES (?, ?, ?)",
        "sss",
        [$scope, strtolower(substr($identifier, 0, 150)), client_ip()]
    );

    // Keep the table small
    db_exec(
        $conn,
        "DELETE FROM login_attempts
         WHERE attempted_at < (NOW() - INTERVAL 1 DAY)"
    );

    // Small delay slows down automated guessing
    usleep(300000);
}

function login_clear_failures(
    mysqli $conn,
    string $scope,
    string $identifier
): void {

    db_exec(
        $conn,
        "DELETE FROM login_attempts
         WHERE scope = ? AND identifier = ?",
        "ss",
        [$scope, strtolower(substr($identifier, 0, 150))]
    );
}
