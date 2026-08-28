<?php
session_start();
include "Backend/db.php";

$menu_items = [];
$result = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category, id");
while ($row = mysqli_fetch_assoc($result)) {
    $menu_items[$row['category']][] = $row;
}

$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu | Book a Bite</title>
    <link rel="stylesheet" href="menu.css">
</head>
<body>

    <header class="header">
        <div class="logo"><span>✦</span> Book a Bite</div>
        <a href="restaurant.php" class="back-btn">← Back</a>
    </header>

    <main class="menu-page">

        <div class="page-heading">
            <p>FROM THE KITCHEN</p>
            <h1>Our Menu</h1>
            <span>Seasonal, locally-sourced, crafted with care.</span>
        </div>

        <?php if (empty($menu_items)): ?>
            <p style="text-align:center; color: rgba(255,255,255,0.6);">Menu coming soon.</p>
        <?php endif; ?>

        <?php foreach ($menu_items as $category => $items): ?>

            <section class="menu-category">
                <h2><?php echo htmlspecialchars($category); ?></h2>

                <div class="menu-items">
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="menu-item-img">
                            <?php endif; ?>
                            <div class="item-top">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <span class="item-price"><?php echo htmlspecialchars($item['price']); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php endforeach; ?>

        <div class="menu-cta">
            <p>Ready to experience it yourself?</p>
            <a href="<?php echo $is_logged_in ? 'Reserve.php' : 'login.php'; ?>" class="primary-btn">
                Reserve a Table →
            </a>
        </div>

    </main>

    <footer>© <?php echo date("Y"); ?> Book a Bite</footer>

</body>
</html>