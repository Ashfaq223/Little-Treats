<?php

require_once __DIR__ . "/admin_auth.php";

// Only the owner can manage admin accounts
admin_require_owner();

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    csrf_verify();

    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "staff";

    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill in every field.";
        $message_type = "error";

    } elseif (mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {

        $message = "Please enter a valid name and email address.";
        $message_type = "error";

    } elseif (strlen($password) < 10 || strlen($password) > 72) {

        $message = "Admin passwords must be 10 to 72 characters.";
        $message_type = "error";

    } elseif (!in_array($role, ADMIN_ROLES, true)) {

        $message = "Please choose a valid role.";
        $message_type = "error";

    } elseif (db_select($conn, "SELECT admin_id FROM admins WHERE email = ?", "s", [$email])->num_rows > 0) {

        $message = "An admin with this email already exists.";
        $message_type = "error";

    } else {

        db_exec(
            $conn,
            "INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)",
            "ssss",
            [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]
        );

        audit_log($conn, "create", "admin", (int) $conn->insert_id, "Added " . $role . ": " . $email);

        $message = "Admin account created.";
        $message_type = "success";

    }

}

$admins = db_select($conn, "SELECT admin_id, name, email, role FROM admins ORDER BY admin_id");

?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Accounts - Little Treats Bakery</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>

<body>

<?php include __DIR__ . "/../includes/admin_header.php"; ?>

<main id="main-content" class="admin-container">
    <div class="admin-welcome">

        <h2>🔑 Admin Accounts</h2>

        <p>Owners can do everything. Staff can manage orders, messages and rewards but cannot delete items or see this page.</p>

    </div>

    <div class="admin-card">

        <h3>Current admins</h3>

        <div class="table-container">

            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
                </thead>
                <tbody>
                    <?php while ($a = $admins->fetch_assoc()) { ?>
                        <tr>
                            <td>#<?php echo (int) $a["admin_id"]; ?></td>
                            <td><?php echo e($a["name"]); ?></td>
                            <td><?php echo e($a["email"]); ?></td>
                            <td><?php echo e($a["role"]); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

    <div class="admin-card">

        <h3>Add an admin</h3>

        <?php if ($message !== "") { ?>
            <div class="message <?php echo e($message_type); ?>" role="status"><?php echo e($message); ?></div>
        <?php } ?>

        <form method="POST" autocomplete="off">

            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="password">Password (at least 10 characters)</label>
                <input type="password" id="password" name="password" minlength="10" maxlength="72" autocomplete="new-password" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="staff">Staff</option>
                    <option value="owner">Owner</option>
                </select>
            </div>

            <div class="admin-form-actions">
                <button type="submit" class="admin-button">Create Admin</button>
                <a href="dashboard.php" class="back-link">Back to Dashboard</a>
            </div>

        </form>

    </div>
</main>

</body>

</html>