<?php

session_start();

$error = "";

$connection = mysqli_connect("localhost", "root", "", "project");

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user["password"])) {

            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"]     = $user["email"];

            header("Location: restaurant.php");
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
    <title>Book a Bite - Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <div class="login-screen">

        <div class="login-visual">
            <div class="login-visual-content">
                <div class="brand">✦ Book a Bite</div>
                <h2>Every Table Tells<br>a Story</h2>
                <p>Reserve unforgettable dining experiences, curated for every celebration and every craving.</p>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-box">

                <div class="login-icon">✦</div>

                <h1>Welcome Back</h1>

                <p class="subtitle">
                    Log in to reserve your table
                </p>

                <?php if ($error != "") { ?>
                    <p class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php } ?>

                <form method="POST" action="login.php">

                    <div class="input-group">
                        <input
                            type="email"
                            name="email"
                            placeholder="Email Address"
                            required
                        >
                        <span class="input-icon">✉</span>
                    </div>

                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            placeholder="Password"
                            required
                        >
                        <span class="input-icon">🔒</span>
                    </div>

                    <button type="submit">
                        Login
                    </button>

                </form>

                <div class="divider">OR</div>

                <a href="Backend/google_login.php" class="google-btn">
                    <svg class="google-icon" viewBox="0 0 48 48">
                        <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6.1 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.2 8 3.1l6-6C34.5 5.1 29.6 3 24 3 12.4 3 3 12.4 3 24s9.4 21 21 21 21-9.4 21-21c0-1.2-.1-2.4-.4-3.5z"/>
                        <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.5 15.1 18.9 12 24 12c3.1 0 5.9 1.2 8 3.1l6-6C34.5 5.1 29.6 3 24 3c-7.7 0-14.3 4.3-17.7 10.7z"/>
                        <path fill="#4CAF50" d="M24 45c5.5 0 10.4-1.9 14.2-5.1l-6.6-5.4C29.6 36 26.9 37 24 37c-5.2 0-9.6-3.3-11.2-8l-6.6 5.1C9.6 40.6 16.2 45 24 45z"/>
                        <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.2 4.3-4.1 5.6l6.6 5.4C41.6 35.8 45 30.4 45 24c0-1.2-.1-2.4-.4-3.5z"/>
                    </svg>
                    Continue with Google
                </a>

                <p>
    Don't have an account?
    <a href="register.php" class="register-btn">Create an Account</a>
</p>

            </div>
        </div>

    </div>

</body>

</html>