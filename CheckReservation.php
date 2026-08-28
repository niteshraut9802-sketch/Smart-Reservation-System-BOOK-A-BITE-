<?php
session_start();
include "Backend/db.php";

// Auto-complete past reservations
mysqli_query($conn, "UPDATE reservations SET status = 'completed' WHERE status = 'confirmed' AND reservation_date < CURDATE()");

// Must be logged in to view reservations
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=" . urlencode("Please log in to view your reservations."));
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle cancellation
if (isset($_POST['cancel_reservation'])) {
    $cancel_id = (int) $_POST['reservation_id'];

    // Only allow cancelling your own, still-confirmed, future reservations
    $cancel_stmt = mysqli_prepare($conn, "
        UPDATE reservations 
        SET status = 'cancelled' 
        WHERE id = ? AND user_id = ? AND status = 'confirmed' AND reservation_date >= CURDATE()
    ");
    mysqli_stmt_bind_param($cancel_stmt, "ii", $cancel_id, $user_id);
    mysqli_stmt_execute($cancel_stmt);
    mysqli_stmt_close($cancel_stmt);

    header("Location: CheckReservation.php?success=" . urlencode("Reservation cancelled."));
    exit;
}

$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// Fetch this user's reservations, most recent first
$stmt = mysqli_prepare($conn, "SELECT * FROM reservations WHERE user_id = ? ORDER BY reservation_date DESC, id DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$reservations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reservations[] = $row;
}
mysqli_stmt_close($stmt);

// Fetch pre-ordered items for all these reservations in one go
$items_by_reservation = [];
if (!empty($reservations)) {
    $ids = array_column($reservations, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $item_sql = "
        SELECT ri.reservation_id, ri.quantity, mi.name
        FROM reservation_items ri
        JOIN menu_items mi ON ri.menu_item_id = mi.id
        WHERE ri.reservation_id IN ($placeholders)
    ";

    $item_stmt = mysqli_prepare($conn, $item_sql);
    mysqli_stmt_bind_param($item_stmt, $types, ...$ids);
    mysqli_stmt_execute($item_stmt);
    $item_result = mysqli_stmt_get_result($item_stmt);

    while ($item_row = mysqli_fetch_assoc($item_result)) {
        $items_by_reservation[$item_row['reservation_id']][] = $item_row;
    }
    mysqli_stmt_close($item_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations | Book a Bite</title>
    <link rel="stylesheet" href="Checkreservation.css">
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="logo">
            <span>✦</span> Book a Bite
        </div>

        <a href="restaurant.php" class="back-btn">
            ← Back
        </a>
    </header>

    <main class="reservations-page">

        <div class="page-heading">
            <p>YOUR BOOKINGS</p>
            <h1>My Reservations</h1>
            <span>
                A look at everything you've booked with us.
            </span>
        </div>

        <?php if ($success): ?>
            <p class="success-message" style="text-align:center; margin-bottom: 30px;"><?php echo $success; ?></p>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>

            <div class="empty-state">
                <p>You don't have any reservations yet.</p>
                <a href="Reserve.php" class="primary-btn">Reserve a Table →</a>
            </div>

        <?php else: ?>

            <div class="reservation-list">

                <?php foreach ($reservations as $r): ?>

                    <?php
                    $is_future = $r['reservation_date'] >= date('Y-m-d');
                    $can_cancel = $is_future && $r['status'] === 'confirmed';
                    ?>

                    <div class="reservation-card">

                        <div class="card-top">
                            <h2><?php echo htmlspecialchars($r['table_no']); ?></h2>
                            <span class="status-badge status-<?php echo htmlspecialchars($r['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($r['status'])); ?>
                            </span>
                        </div>

                        <div class="card-grid">

                            <div class="detail">
                                <label>Date</label>
                                <p><?php echo htmlspecialchars(date("M j, Y", strtotime($r['reservation_date']))); ?></p>
                            </div>

                            <div class="detail">
                                <label>Time</label>
                                <p><?php echo htmlspecialchars($r['reservation_time']); ?></p>
                            </div>

                            <div class="detail">
                                <label>Guests</label>
                                <p><?php echo htmlspecialchars($r['guests']); ?></p>
                            </div>

                            <div class="detail">
                                <label>Seating</label>
                                <p><?php echo htmlspecialchars($r['seating']); ?></p>
                            </div>

                            <div class="detail">
                                <label>Occasion</label>
                                <p><?php echo htmlspecialchars($r['occasion']); ?></p>
                            </div>

                            <div class="detail">
                                <label>Phone</label>
                                <p><?php echo htmlspecialchars($r['phone_no']); ?></p>
                            </div>

                        </div>

                        <?php if ($r['preorder'] === 'yes' && !empty($items_by_reservation[$r['id']])): ?>
                            <div class="preorder-summary">
                                <label>Pre-ordered Food</label>
                                <div class="food-tags">
                                    <?php foreach ($items_by_reservation[$r['id']] as $item): ?>
                                        <span class="food-tag">
                                            <?php echo htmlspecialchars($item['name']); ?> × <?php echo (int)$item['quantity']; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ((int)$r['total_amount'] > 0): ?>
                                    <p class="total-line">Total: Rs. <?php echo number_format($r['total_amount']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="card-footer">
                            <small>Booked on <?php echo htmlspecialchars(date("M j, Y", strtotime($r['created_at']))); ?></small>

                            <?php if ($can_cancel): ?>
                                <form method="POST" action="CheckReservation.php" onsubmit="return confirm('Cancel this reservation?');">
                                    <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="cancel_reservation" value="1">
                                    <button type="submit" class="cancel-btn">Cancel Reservation</button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </main>

    <footer>
        © <?php echo date("Y"); ?> Book a Bite
    </footer>

</body>
</html>