<?php
session_start();

// Basic site info as variables — keeps content editable in one place
$restaurant = [
    'name'    => 'Book a Bite',
    'tagline' => 'MICHELIN RECOMMENDED • SINCE 2009',
    'address' => '48th Floor, Skyline Tower, Downtown',
    'phone'   => '(555) 123-4567',
    'hours'   => [
        'Mon – Thu' => '5:00 PM – 11:00 PM',
        'Fri – Sat' => '5:00 PM – 1:00 AM',
        'Sunday'    => '4:00 PM – 10:00 PM',
    ],
    'year'    => date('Y'),
];

// Signature dishes shown in the menu highlights section
$dishes = [
    [
        'name'  => 'Seared Scallops',
        'desc'  => 'Cauliflower purée, brown butter, crisp pancetta.',
        'price' => 'Rs. 3,200',
    ],
    [
        'name'  => 'Truffle Tagliatelle',
        'desc'  => 'Hand-cut pasta, black truffle, aged parmesan.',
        'price' => 'Rs. 3,900',
    ],
    [
        'name'  => 'Wagyu Ribeye',
        'desc'  => 'Charred to order, roasted bone marrow, red wine jus.',
        'price' => 'Rs. 7,800',
    ],
    [
        'name'  => 'Rooftop Tasting Menu',
        'desc'  => 'Five courses paired with our sommelier\'s selections.',
        'price' => 'Rs. 13,500',
    ],
];

// Guest testimonials
$testimonials = [
    [
        'quote'  => 'The rooftop view at sunset is unmatched — every course felt like an event of its own.',
        'author' => 'Maria T.',
    ],
    [
        'quote'  => 'Impeccable service and the truffle tagliatelle alone is worth the reservation.',
        'author' => 'James R.',
    ],
    [
        'quote'  => 'We celebrated our anniversary here two years running. It never disappoints.',
        'author' => 'Priya & Alex',
    ],
];

// Logged-in state, used in the navbar below
$is_logged_in = isset($_SESSION['user_id']);
$full_name    = $_SESSION['full_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $restaurant['name']; ?></title>
    <link rel="stylesheet" href="Restaurant.css" />
</head>
<body background="image/restaurant Background.png">

    <section class="hero">

        <!-- Navbar -->
        <header class="navbar">
            <div class="logo">
                <span class="icon">✦</span>
                <?php echo $restaurant['name']; ?>
            </div>

            <nav>
                <ul class="nav-links">
                    <li><a href="CheckReservation.php">Check Reservations</a></li>
                    <li><a href="loyalty.php">Loyalty Rewards</a></li>
                </ul>
            </nav>

            <?php if ($is_logged_in): ?>
                <div class="nav-account">
                    <span class="welcome-text">Hi, <?php echo htmlspecialchars($full_name); ?></span>
                    <a href="Reserve.php" class="book-btn">Book Now</a>
                    <a href="Backend/logout.php" class="logout-btn">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="book-btn">Login</a>
            <?php endif; ?>
        </header>

        <!-- Hero Content -->
        <div class="hero-content">

            <p class="tagline">
                ★★★★★ <?php echo htmlspecialchars($restaurant['tagline']); ?>
            </p>

            <h1>
                A Table Awaits <br>
                <span>Every Occasion</span>
            </h1>

            <p class="description">
                Reserve your perfect seat — indoors by candlelight,
                on our garden terrace, or atop our rooftop under the stars.
                Every detail crafted for your celebration.
            </p>

            <div class="buttons">
                <a href="<?php echo $is_logged_in ? 'Reserve.php' : 'login.php'; ?>" class="primary-btn">
                    Reserve a Table →
                </a>

                <a href="menu.php" class="secondary-btn">
                    View Menu
                </a>
            </div>

            <!-- Stats -->
            <div class="stats">

                <div class="stat-box">
                    <h2>3,200+</h2>
                    <p>Happy Diners</p>
                </div>

                <div class="stat-box">
                    <h2>48</h2>
                    <p>Tables Available</p>
                </div>

                <div class="stat-box">
                    <h2>4.9</h2>
                    <p>Avg. Rating</p>
                </div>

            </div>
        </div>

    </section>

    <!-- About Section -->
    <section class="about">
        <div class="about-content">
            <p class="section-label">Our Story</p>
            <h2>A Rooftop Escape Above the City</h2>
            <p>
                Perched on the 48th floor, Book a Bite has served intimate dinners
                and unforgettable celebrations since 2009. Our kitchen blends
                seasonal, locally-sourced ingredients with skyline views that
                change with every sunset — from candlelit indoor tables to our
                open-air garden terrace and rooftop deck.
            </p>
        </div>
    </section>

    <!-- Signature Dishes -->
    <section class="menu-highlights">
        <p class="section-label">From the Kitchen</p>
        <h2>Signature Dishes</h2>

        <div class="dish-grid">
            <?php foreach ($dishes as $dish): ?>
                <div class="dish-card">
                    <div class="dish-top">
                        <h3><?php echo htmlspecialchars($dish['name']); ?></h3>
                        <span class="dish-price"><?php echo htmlspecialchars($dish['price']); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($dish['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="menu.php" class="secondary-btn menu-link">View Full Menu →</a>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <p class="section-label">Guest Stories</p>
        <h2>What Diners Are Saying</h2>

        <div class="testimonial-grid">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <p class="quote">&ldquo;<?php echo htmlspecialchars($t['quote']); ?>&rdquo;</p>
                    <p class="author">— <?php echo htmlspecialchars($t['author']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Hours & Location -->
    <section class="info-strip">
        <div class="info-box">
            <h3>Hours</h3>
            <?php foreach ($restaurant['hours'] as $days => $time): ?>
                <p><span><?php echo $days; ?></span> <?php echo $time; ?></p>
            <?php endforeach; ?>
        </div>

        <div class="info-box">
            <h3>Location</h3>
            <p><?php echo htmlspecialchars($restaurant['address']); ?></p>
            <p><?php echo htmlspecialchars($restaurant['phone']); ?></p>
        </div>

        <div class="info-box">
            <h3>Dress Code</h3>
            <p>Smart casual to formal.</p>
            <p>Jackets suggested for rooftop seating.</p>
        </div>
    </section>

    <!-- Reservation CTA banner -->
    <section class="cta-banner">
        <h2>Ready to Reserve Your Table?</h2>
        <p>Availability is limited on weekends — book ahead to secure your view.</p>
        <a href="<?php echo $is_logged_in ? 'Reserve.php' : 'login.php'; ?>" class="primary-btn">Reserve a Table →</a>
    </section>

    <footer class="site-footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo footer-logo">
                    <span class="icon">✦</span>
                    <?php echo $restaurant['name']; ?>
                </div>
                <p>Fine dining above the city skyline since 2009.</p>
            </div>

            <div class="footer-col">
                <h4>Explore</h4>
                <a href="menu.php">Menu</a>
                <a href="CheckReservation.php">Reservations</a>
                <a href="loyalty.php">Loyalty Rewards</a>
            </div>

            <div class="footer-col">
                <h4>Follow</h4>
                <a href="#">Instagram</a>
                <a href="#">Facebook</a>
                <a href="#">Twitter</a>
            </div>

            <div class="footer-col">
                <h4>Newsletter</h4>
                <form action="backend/subscribe.php" method="POST" class="newsletter-form">
                    <input type="email" name="Email" placeholder="Your email" required>
                    <button type="submit">Join</button>
                </form>
            </div>
        </div>

        <p class="copyright">&copy; <?php echo $restaurant['year']; ?> <?php echo $restaurant['name']; ?>. All rights reserved.</p>
    </footer>

</body>
</html>