<?php
session_start();
include "../Backend/db.php";
include "auth_check.php";

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_reservations = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM reservations"))['c'];
$total_menu_items = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM menu_items"))['c'];
$upcoming = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM reservations WHERE reservation_date >= CURDATE()"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Book a Bite</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body class="dashboard-body">

    <header class="admin-header">
        <div class="logo"><span>✦</span> Book a Bite Admin</div>
        <div class="admin-nav">
            <span class="welcome-text">Hi, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <main class="admin-main">

        <h1>Dashboard</h1>

        <div class="stats-grid">
            <div class="stat-card">
                <h2><?php echo $total_users; ?></h2>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $total_reservations; ?></h2>
                <p>Total Reservations</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $upcoming; ?></h2>
                <p>Upcoming Reservations</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $total_menu_items; ?></h2>
                <p>Menu Items</p>
            </div>
        </div>

        <div class="admin-links">
            <a href="reservations.php" class="admin-link-card">
                <h3>Manage Reservations</h3>
                <p>View and remove customer bookings</p>
            </a>

            <a href="users.php" class="admin-link-card">
                <h3>Manage Users</h3>
                <p>View and remove registered accounts</p>
            </a>

            <a href="menu.php" class="admin-link-card">
                <h3>Manage Menu</h3>
                <p>Add, edit, or remove menu items</p>
            </a>
        </div>

    </main>

</body>
</html>