<?php
include "../Backend/db.php";

// CHANGE THESE before running, then delete this file after
$full_name = "Admin";
$email     = "admin@bookabite.com";
$password  = "admin123";

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO admins (full_name, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hashed);

if (mysqli_stmt_execute($stmt)) {
    echo "Admin created successfully. DELETE THIS FILE NOW.";
} else {
    echo "Failed: " . mysqli_error($conn);
}
?>