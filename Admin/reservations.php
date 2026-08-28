<?php
session_start();
include "../Backend/db.php";
include "auth_check.php";

// Auto-complete past reservations
mysqli_query($conn, "UPDATE reservations SET status = 'completed' WHERE status = 'confirmed' AND reservation_date < CURDATE()");

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = mysqli_prepare($conn, "DELETE FROM reservations WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: reservations.php?success=" . urlencode("Reservation removed."));
    exit;
}

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

$result = mysqli_query($conn, "
    SELECT r.*, u.full_name, u.email
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.reservation_date DESC, r.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations | Admin</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body class="dashboard-body">

    <header class="admin-header">
        <div class="logo"><span>✦</span> Book a Bite Admin</div>
        <div class="admin-nav">
            <a href="dashboard.php" class="back-btn">← Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <main class="admin-main">

        <h1>Manage Reservations</h1>

        <?php if ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Guests</th>
                        <th>Table</th>
                        <th>Seating</th>
                        <th>Occasion</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($r['full_name']); ?><br>
                                <small><?php echo htmlspecialchars($r['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(date("M j, Y", strtotime($r['reservation_date']))); ?></td>
                            <td><?php echo htmlspecialchars($r['reservation_time']); ?></td>
                            <td><?php echo htmlspecialchars($r['guests']); ?></td>
                            <td><?php echo htmlspecialchars($r['table_no']); ?></td>
                            <td><?php echo htmlspecialchars($r['seating']); ?></td>
                            <td><?php echo htmlspecialchars($r['occasion']); ?></td>
                            <td>
                                <span class="status-pill status-<?php echo htmlspecialchars($r['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($r['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <a href="reservations.php?delete=<?php echo $r['id']; ?>"
                                   class="delete-btn"
                                   onclick="return confirm('Remove this reservation?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>