<?php
session_start();
include "../Backend/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);

        if ($admin && password_verify($password, $admin["password"])) {
            $_SESSION["admin_id"]   = $admin["id"];
            $_SESSION["admin_name"] = $admin["full_name"];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Book a Bite</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <div class="login-screen">

        <div class="login-visual">
            <div class="login-visual-content">
                <div class="brand">✦ Book a Bite</div>
                <h2>Manage Every Detail<br>of Your Restaurant</h2>
                <p>Reservations, menu items, and guests — all in one place, built for the people who run the show.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-box">

                <div class="admin-icon">🔐</div>

                <h1>Admin Panel</h1>
                <p class="subtitle">Book a Bite — Management Console</p>

                <?php if ($error): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Admin Email" required>
                        <span class="input-icon">✉</span>
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" placeholder="Password" required>
                        <span class="input-icon">🔒</span>
                    </div>

                    <button type="submit">Login</button>

                </form>

            </div>
        </div>

    </div>

</body>
</html>