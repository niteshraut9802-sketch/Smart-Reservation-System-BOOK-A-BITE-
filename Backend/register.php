<?php

include "db.php";

$name     = clean($_POST["Full_Name"]);
$email    = clean($_POST["Email"]);
$phone    = clean($_POST["Phone_no"]);
$password = clean($_POST["Password"]);
$confirm  = clean($_POST["Confirm_Password"]);

// Name validation 
if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
    header("Location: ../Register.php?error=" . urlencode("Invalid name"));
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../Register.php?error=" . urlencode("Invalid email"));
    exit;
}

// Phone validation
if (!preg_match("/^[0-9]{10}$/", $phone)) {
    header("Location: ../Register.php?error=" . urlencode("Phone number must be 10 digits"));
    exit;
}

// Password validation
if ($password != $confirm) {
    header("Location: ../Register.php?error=" . urlencode("Passwords do not match"));
    exit;
}

// Hash the password before saving
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Save user (using a prepared statement to prevent SQL injection)
$sql = "INSERT INTO users (Full_Name, Email, Phone_no, Password) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header("Location: ../login.php?success=" . urlencode("Registration successful! Please log in."));
    exit;
} else {
    mysqli_stmt_close($stmt);
    header("Location: ../Register.php?error=" . urlencode("Registration failed. Please try again."));
    exit;
}

?>