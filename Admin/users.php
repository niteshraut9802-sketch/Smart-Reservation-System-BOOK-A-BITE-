<?php
session_start();
include "../Backend/db.php";
include "auth_check.php";

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = mysqli_prepare($conn, "DELETE FROM reservations WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: users.php?success=" . urlencode("User removed."));
    exit;
}

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

$result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin</title>
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

        <h1>Manage Users</h1>

        <?php if ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone_no']); ?></td>
                            <td><?php echo htmlspecialchars(date("M j, Y", strtotime($u['created_at']))); ?></td>
                            <td>
                                <a href="users.php?delete=<?php echo $u['id']; ?>"
                                   class="delete-btn"
                                   onclick="return confirm('Remove this user? This also deletes their reservations.');">
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