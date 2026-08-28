<?php
session_start();

$error   = isset($_GET['error'])   ? htmlspecialchars($_GET['error'])   : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// Only process if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Database connection settings — update these to match your setup
    $host    = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $dbname  = 'project';

    $conn = mysqli_connect($host, $db_user, $db_pass, $dbname);

    if (!$conn) {
        header("Location: register.php?error=" . urlencode("Database connection failed."));
        exit;
    }

    // Get form values
    $full_name        = trim($_POST['Full_Name'] ?? '');
    $email             = trim($_POST['Email'] ?? '');
    $phone_no          = trim($_POST['Phone_no'] ?? '');
    $password          = $_POST['Password'] ?? '';
    $confirm_password  = $_POST['Confirm_Password'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone_no) || empty($password) || empty($confirm_password)) {
        header("Location: register.php?error=" . urlencode("All fields are required."));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=" . urlencode("Please enter a valid email address."));
        exit;
    }

    if ($password !== $confirm_password) {
        header("Location: register.php?error=" . urlencode("Passwords do not match."));
        exit;
    }

    if (strlen($password) < 6) {
        header("Location: register.php?error=" . urlencode("Password must be at least 6 characters."));
        exit;
    }

    // Check if email already exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        header("Location: register.php?error=" . urlencode("An account with this email already exists."));
        exit;
    }
    mysqli_stmt_close($stmt);

    // Hash the password before storing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user
    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, phone_no, password) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $full_name, $email, $phone_no, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: login.php?success=" . urlencode("Account created successfully! Please log in."));
        exit;
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: register.php?error=" . urlencode("Something went wrong. Please try again."));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Bite - Register</title>
    <link rel="stylesheet" href="register.css">
</head>

<body background="image/login background.png">

    <div class="overlay"></div>

    <div class="container">

        <div class="register-box">

            <h1>Book a Bite</h1>

            <p class="subtitle">
                Create Your Dining Account
            </p>

            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success-message"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="register.php" method="POST">

                <input
                    type="text"
                    name="Full_Name"
                    placeholder="Full Name"
                    required
                >

                <input
                    type="email"
                    name="Email"
                    placeholder="Email Address"
                    required
                >

                <input
                   type="tel"
                  name="Phone_no"
                  placeholder="Phone Number (10 digits)"
                pattern="[0-9]{10}"
                 maxlength="10"
                  inputmode="numeric"
                   title="Phone number must be exactly 10 digits"
                 required
>

                <input
                    type="password"
                    name="Password"
                    placeholder="Password"
                    required
                >

                <input
                    type="password"
                    name="Confirm_Password"
                    placeholder="Confirm Password"
                    required
                >

                <button type="submit">
                    Create Account
                </button>

                <p>
                    Already have an account?
                    <a href="login.php">Login</a>
                </p>

            </form>

        </div>

    </div>

</body>
</html>
