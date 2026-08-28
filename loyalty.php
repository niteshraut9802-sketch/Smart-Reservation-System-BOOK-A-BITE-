<?php
session_start();
include "Backend/db.php";

$is_logged_in = isset($_SESSION['user_id']);
$full_name    = $_SESSION['full_name'] ?? '';

$tiers = [
    ['name' => 'Bronze', 'min' => 0, 'max' => 499, 'perk' => 'Birthday dessert on us'],
    ['name' => 'Silver', 'min' => 500, 'max' => 1499, 'perk' => 'Priority booking + 10% off'],
    ['name' => 'Gold', 'min' => 1500, 'max' => 3499, 'perk' => 'Complimentary wine pairing'],
    ['name' => 'Platinum', 'min' => 3500, 'max' => null, 'perk' => 'Private rooftop table, always'],
];

$how_it_works = [
    'Earn 10 points for every Rs 150 spent dining with us.',
    'Points are added automatically after each visit.',
    'Redeem points for discounts, courses, or exclusive experiences.',
    'Tiers unlock automatically as your points grow.',
];

$user_points = 0;
$current_tier = null;
$next_tier = null;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];

  $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(total_amount), 0) AS total_spent FROM reservations WHERE user_id = ? AND status = 'completed'");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $total_spent = (int) $row['total_spent'];
    $user_points = intdiv($total_spent, 150) * 10;

    // Determine current tier
    foreach ($tiers as $tier) {
        if ($user_points >= $tier['min'] && ($tier['max'] === null || $user_points <= $tier['max'])) {
            $current_tier = $tier;
            break;
        }
    }

    // Determine next tier + points needed
    foreach ($tiers as $tier) {
        if ($tier['min'] > $user_points) {
            $next_tier = $tier;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Rewards | Book a Bite</title>
    <link rel="stylesheet" href="loyalty.css">
</head>
<body>

    <header class="header">
        <div class="logo"><span>✦</span> Book a Bite</div>
        <a href="restaurant.php" class="back-btn">← Back</a>
    </header>

    <main class="rewards-page">

        <div class="page-heading">
            <p>DINE • EARN • ENJOY</p>
            <h1>Loyalty Rewards</h1>
            <span>The more you dine with us, the more we give back.</span>
        </div>

        <?php if ($is_logged_in): ?>
            <div class="member-banner">
                <p>Welcome back, <?php echo htmlspecialchars($full_name); ?></p>

                <div class="points-display">
                    <span class="points-number"><?php echo number_format($user_points); ?></span>
                    <span class="points-label">points</span>
                </div>

                <?php if ($current_tier): ?>
                    <div class="current-tier-badge">
                        Current Tier: <strong><?php echo htmlspecialchars($current_tier['name']); ?></strong>
                    </div>
                <?php endif; ?>

                <?php if ($next_tier): ?>
                    <?php $points_needed = $next_tier['min'] - $user_points; ?>
                    <small><?php echo number_format($points_needed); ?> points to reach <?php echo htmlspecialchars($next_tier['name']); ?></small>
                <?php else: ?>
                    <small>You've reached our highest tier!</small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="member-banner guest">
                <p>Log in to start tracking your rewards</p>
                <a href="login.php" class="primary-btn">Login →</a>
            </div>
        <?php endif; ?>

        <section class="tiers-section">
            <h2>Membership Tiers</h2>

            <div class="tier-grid">
                <?php foreach ($tiers as $tier): ?>
                    <?php $is_current = $current_tier && $current_tier['name'] === $tier['name']; ?>
                    <div class="tier-card <?php echo $is_current ? 'active-tier' : ''; ?>">
                        <?php if ($is_current): ?>
                            <span class="current-badge">YOUR TIER</span>
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($tier['name']); ?></h3>
                        <p class="tier-points">
                            <?php echo number_format($tier['min']); ?><?php echo $tier['max'] !== null ? ' – ' . number_format($tier['max']) : '+'; ?> pts
                        </p>
                        <p class="tier-perk"><?php echo htmlspecialchars($tier['perk']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="how-section">
            <h2>How It Works</h2>

            <div class="how-list">
                <?php foreach ($how_it_works as $i => $step): ?>
                    <div class="how-item">
                        <span class="how-number"><?php echo $i + 1; ?></span>
                        <p><?php echo htmlspecialchars($step); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="rewards-cta">
            <p>Every reservation brings you closer to the next tier.</p>
            <a href="<?php echo $is_logged_in ? 'Reserve.php' : 'login.php'; ?>" class="primary-btn">
                Reserve a Table →
            </a>
        </div>

    </main>

    <footer>© <?php echo date("Y"); ?> Book a Bite</footer>

</body>
</html>