<?php

session_start();

$error = "";

// Database connection
$connection = mysqli_connect("localhost", "root", "", "project");

// Check connection
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}


// Login form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];


    // Check if fields are empty
    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        // Find user using email
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";

        $stmt = mysqli_prepare($connection, $query);

        mysqli_stmt_bind_param($stmt, "s", $email);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $user = mysqli_fetch_assoc($result);


        // Check user and password
        if ($user && password_verify($password, $user["password"])) {

            // Store user information in session
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"]     = $user["email"];


            // Login successful
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


<body style="background-image: url('image/login background.png');">

    <div class="overlay"></div>


    <div class="container">

        <div class="login-box">

            <h1>Book a Bite</h1>

            <p class="subtitle">
                Reserve unforgettable dining experiences
            </p>


            <?php if ($error != "") { ?>

                <p class="error">
                    <?php echo htmlspecialchars($error); ?>
                </p>

            <?php } ?>


            <form method="POST" action="login.php">

                <input
                    type="email"
                    name="email"
                    placeholder="Email Address"
                    required
                >


                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >


                <button type="submit">
                    Login
                </button>


                <p>
                    Don't have an account?

                    <a href="register.php">
                        Register
                    </a>
                </p>

            </form>

        </div>

    </div>

</body>

</html>