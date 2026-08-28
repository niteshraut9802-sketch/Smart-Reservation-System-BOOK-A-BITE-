<?php

session_start();

include "Backend/db.php";
require "send_mail.php";


// =====================================================
// MUST BE LOGGED IN
// =====================================================

if (!isset($_SESSION['user_id'])) {
    header(
        "Location: login.php?error=" .
        urlencode("Please log in to make a reservation.")
    );
    exit;
}


$user_id   = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email     = $_SESSION['email'];

$error = isset($_GET['error'])
    ? htmlspecialchars($_GET['error'])
    : '';


// =====================================================
// FETCH MENU ITEMS
// =====================================================

$menu_items = [];

$menu_result = mysqli_query(
    $conn,
    "SELECT * FROM menu_items ORDER BY category, id"
);

while ($row = mysqli_fetch_assoc($menu_result)) {

    $numeric_price =
        (int) preg_replace('/[^0-9]/', '', $row['price']);

    $row['numeric_price'] = $numeric_price;

    $menu_items[$row['category']][] = $row;
}


// =====================================================
// HANDLE RESERVATION FORM
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // -------------------------------------------------
    // GET FORM DATA
    // -------------------------------------------------

    $phone    = clean($_POST['phone']);
    $date     = clean($_POST['date']);
    $time     = clean($_POST['time']);
    $guests   = clean($_POST['guests']);
    $seating  = clean($_POST['seating']);
    $occasion = clean($_POST['occasion']);
    $table_no = clean($_POST['table']);
    $preorder = clean($_POST['preorder']);


    // -------------------------------------------------
    // BASIC VALIDATION
    // -------------------------------------------------

    if (
        empty($phone) ||
        empty($date) ||
        empty($time) ||
        empty($guests) ||
        empty($seating) ||
        empty($occasion) ||
        empty($table_no)
    ) {

        header(
            "Location: Reserve.php?error=" .
            urlencode("Please fill in all required fields.")
        );

        exit;
    }


    // -------------------------------------------------
    // PHONE VALIDATION
    // -------------------------------------------------

    if (!preg_match("/^[0-9]{10}$/", $phone)) {

        header(
            "Location: Reserve.php?error=" .
            urlencode("Phone number must be exactly 10 digits.")
        );

        exit;
    }


    // -------------------------------------------------
    // PREVENT PAST DATES
    // -------------------------------------------------

    $today = date('Y-m-d');

    if ($date < $today) {

        header(
            "Location: Reserve.php?error=" .
            urlencode("Please select a valid future date.")
        );

        exit;
    }


    // =================================================
    // CHECK TABLE AVAILABILITY
    // =================================================

    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM reservations
         WHERE table_no = ?
         AND reservation_date = ?
         AND reservation_time = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $check_stmt,
        "sss",
        $table_no,
        $date,
        $time
    );

    mysqli_stmt_execute($check_stmt);

    mysqli_stmt_store_result($check_stmt);


    if (mysqli_stmt_num_rows($check_stmt) > 0) {

        mysqli_stmt_close($check_stmt);

        header(
            "Location: Reserve.php?error=" .
            urlencode(
                "Sorry, $table_no is already booked for that date and time. Please choose a different table or time."
            )
        );

        exit;
    }


    mysqli_stmt_close($check_stmt);


    // =================================================
    // CALCULATE PRE-ORDER TOTAL
    // =================================================

    $total_amount = 0;

    $pending_items = [];


    if (
        $preorder === 'yes' &&
        isset($_POST['item_qty']) &&
        is_array($_POST['item_qty'])
    ) {


        foreach ($_POST['item_qty'] as $menu_item_id => $qty) {

            $qty = (int) $qty;

            $menu_item_id = (int) $menu_item_id;


            if ($qty > 0) {


                // -------------------------------------
                // GET MENU ITEM FROM DATABASE
                // -------------------------------------

                $price_stmt = mysqli_prepare(
                    $conn,
                    "SELECT name, price, image_url
                     FROM menu_items
                     WHERE id = ?"
                );

                mysqli_stmt_bind_param(
                    $price_stmt,
                    "i",
                    $menu_item_id
                );

                mysqli_stmt_execute($price_stmt);

                $price_result =
                    mysqli_stmt_get_result($price_stmt);

                $item_row =
                    mysqli_fetch_assoc($price_result);

                mysqli_stmt_close($price_stmt);


                // -------------------------------------
                // CALCULATE PRICE
                // -------------------------------------

                if ($item_row) {

                    $unit_price =
                        (int) preg_replace(
                            '/[^0-9]/',
                            '',
                            $item_row['price']
                        );


                    $subtotal =
                        $unit_price * $qty;


                    $total_amount += $subtotal;


                    $pending_items[] = [

                        'menu_item_id' =>
                            $menu_item_id,

                        'name' =>
                            $item_row['name'],

                        'qty' =>
                            $qty,

                        'subtotal' =>
                            $subtotal,

                        'image_url' =>
                            $item_row['image_url'],

                    ];
                }
            }
        }
    }


    // =================================================
    // INSERT RESERVATION
    // =================================================

    $sql = "
        INSERT INTO reservations
        (
            user_id,
            phone_no,
            reservation_date,
            reservation_time,
            guests,
            seating,
            occasion,
            table_no,
            preorder,
            total_amount
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";


    $stmt = mysqli_prepare($conn, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "issssssssi",
        $user_id,
        $phone,
        $date,
        $time,
        $guests,
        $seating,
        $occasion,
        $table_no,
        $preorder,
        $total_amount
    );


    // =================================================
    // RESERVATION SUCCESS
    // =================================================

    if (mysqli_stmt_execute($stmt)) {


        // ---------------------------------------------
        // GET RESERVATION ID
        // ---------------------------------------------

        $reservation_id =
            mysqli_insert_id($conn);

        mysqli_stmt_close($stmt);


        // =================================================
        // INSERT PRE-ORDER ITEMS
        // =================================================

        $ordered_items = [];


        foreach ($pending_items as $item) {


            $item_stmt = mysqli_prepare(
                $conn,
                "INSERT INTO reservation_items
                (
                    reservation_id,
                    menu_item_id,
                    quantity
                )
                VALUES (?, ?, ?)"
            );


            mysqli_stmt_bind_param(
                $item_stmt,
                "iii",
                $reservation_id,
                $item['menu_item_id'],
                $item['qty']
            );


            mysqli_stmt_execute($item_stmt);

            mysqli_stmt_close($item_stmt);


            // -----------------------------------------
            // CREATE DISPLAY TEXT
            // -----------------------------------------

            $ordered_items[] =
                $item['name'] .
                " × " .
                $item['qty'] .
                " — Rs. " .
                number_format($item['subtotal']);
        }


        // =================================================
        // CREATE FOOD SECTION FOR EMAIL
        // =================================================

        $email_items = '';


        if (!empty($ordered_items)) {


            $email_items .= '
                <h3 style="
                    margin-top:25px;
                    color:#333;
                ">
                    Pre-ordered Food
                </h3>

                <ul>
            ';


            foreach ($ordered_items as $item) {

                $email_items .=
                    '<li>' .
                    htmlspecialchars($item) .
                    '</li>';
            }


            $email_items .= '
                </ul>
            ';
        }


        // =================================================
        // CREATE CONFIRMATION EMAIL
        // =================================================

        $email_message = '

        <div style="
            font-family: Arial, sans-serif;
            max-width: 650px;
            margin: auto;
            line-height: 1.6;
            color: #333;
        ">

            <h2 style="
                color: #d4a343;
                margin-bottom: 5px;
            ">
                Booking Confirmed!
            </h2>


            <p>
                Hello
                <strong>' .
                htmlspecialchars($full_name) .
                '</strong>,
            </p>


            <p>
                Thank you for choosing
                <strong>Book a Bite</strong>.
                Your reservation has been successfully confirmed.
            </p>


            <hr>


            <h3>
                Reservation Details
            </h3>


            <p>

                <strong>Reservation ID:</strong>
                #' .
                $reservation_id .
                '<br>


                <strong>Date:</strong> ' .
                htmlspecialchars(
                    date("M j, Y", strtotime($date))
                ) .
                '<br>


                <strong>Time:</strong> ' .
                htmlspecialchars($time) .
                '<br>


                <strong>Guests:</strong> ' .
                htmlspecialchars($guests) .
                '<br>


                <strong>Table:</strong> ' .
                htmlspecialchars($table_no) .
                '<br>


                <strong>Seating:</strong> ' .
                htmlspecialchars($seating) .
                '<br>


                <strong>Occasion:</strong> ' .
                htmlspecialchars($occasion) .
                '<br>


                <strong>Phone:</strong> ' .
                htmlspecialchars($phone) .

            '</p>


            ' . $email_items . '


            ' . (!empty($ordered_items) ? '

                <p style="
                    font-size:18px;
                    margin-top:20px;
                ">

                    <strong>
                        Total Amount:
                    </strong>

                    Rs. ' .
                    number_format($total_amount) .

                '

                </p>

            ' : '') . '


            <hr>


            <p>
                We look forward to seeing you at
                <strong>Book a Bite</strong>!
            </p>


            <p>
                Thank you,<br>

                <strong>
                    Book a Bite Team
                </strong>
            </p>


        </div>

        ';


        // =================================================
        // SEND EMAIL TO LOGGED-IN USER
        // =================================================

        $sent = sendEmail(
            $email,
            $email_message
        );


        // =================================================
        // SAVE WEBSITE CONFIRMATION
        // =================================================

        $_SESSION['confirmation'] = [

            'full_name' =>
                $full_name,

            'email' =>
                $email,

            'date' =>
                $date,

            'time' =>
                $time,

            'guests' =>
                $guests,

            'table_no' =>
                $table_no,

            'seating' =>
                $seating,

            'items' =>
                $ordered_items,

            'total_amount' =>
                $total_amount,

        ];


        // =================================================
        // REDIRECT AFTER RESERVATION
        // =================================================

        if ($sent) {

            $message =
                "Reservation confirmed! A confirmation email has been sent to your registered email.";


        } else {

            $message =
                "Reservation confirmed! However, the confirmation email could not be sent.";
        }


        header(
            "Location: Reserve.php?success=" .
            urlencode($message)
        );

        exit;


    } else {


        // =================================================
        // RESERVATION FAILED
        // =================================================

        mysqli_stmt_close($stmt);


        header(
            "Location: Reserve.php?error=" .
            urlencode("Something went wrong. Please try again.")
        );

        exit;
    }
}


// =====================================================
// SUCCESS MESSAGE
// =====================================================

$success = isset($_GET['success'])
    ? htmlspecialchars($_GET['success'])
    : '';


// =====================================================
// CONFIRMATION DATA
// =====================================================

$confirmation = null;


if (
    $success &&
    isset($_SESSION['confirmation'])
) {

    $confirmation =
        $_SESSION['confirmation'];

    unset($_SESSION['confirmation']);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reserve a Table | Book a Bite
    </title>

    <link
        rel="stylesheet"
        href="Reserve.css"
    >

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="header">

    <div class="logo">

        <span>✦</span>

        Book a Bite

    </div>


    <a
        href="restaurant.php"
        class="back-btn"
    >

        ← Back

    </a>

</header>



<!-- =====================================================
     RESERVATION PAGE
====================================================== -->

<main class="reservation-page">


    <div class="page-heading">

        <p>
            BOOK YOUR EXPERIENCE
        </p>


        <h1>
            Reserve Your Table
        </h1>


        <span>
            Choose your preferred date, time and dining experience.
        </span>

    </div>



    <!-- ERROR -->

    <?php if ($error): ?>

        <p class="error-message">

            <?php echo $error; ?>

        </p>

    <?php endif; ?>



    <!-- SUCCESS -->

    <?php if ($success): ?>

        <p class="success-message">

            <?php echo $success; ?>

        </p>

    <?php endif; ?>



    <!-- =================================================
         CONFIRMATION PREVIEW
    ================================================== -->

    <?php if ($confirmation): ?>

        <div class="email-preview">


            <div class="email-preview-header">

                <span>
                    ✉️ Confirmation Email
                </span>

            </div>



            <div class="email-preview-body">


                <p>

                    <strong>
                        To:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $confirmation['email']
                    );
                    ?>

                </p>



                <p>

                    <strong>
                        Subject:
                    </strong>

                    Your Reservation is Confirmed!

                </p>


                <hr>



                <p>

                    Thank you,
                    <?php
                    echo htmlspecialchars(
                        $confirmation['full_name']
                    );
                    ?>!

                </p>



                <p>

                    Your table has been reserved successfully.

                </p>



                <ul>

                    <li>

                        <strong>
                            Date:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            date(
                                "M j, Y",
                                strtotime(
                                    $confirmation['date']
                                )
                            )
                        );
                        ?>

                    </li>


                    <li>

                        <strong>
                            Time:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $confirmation['time']
                        );
                        ?>

                    </li>


                    <li>

                        <strong>
                            Guests:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $confirmation['guests']
                        );
                        ?>

                    </li>


                    <li>

                        <strong>
                            Table:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $confirmation['table_no']
                        );
                        ?>

                    </li>


                    <li>

                        <strong>
                            Seating:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $confirmation['seating']
                        );
                        ?>

                    </li>

                </ul>



                <?php if (!empty($confirmation['items'])): ?>


                    <p>

                        <strong>
                            Pre-ordered:
                        </strong>

                    </p>


                    <ul>

                        <?php foreach (
                            $confirmation['items']
                            as $item
                        ): ?>

                            <li>

                                <?php
                                echo htmlspecialchars($item);
                                ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>


                    <hr>


                    <p class="total-line">

                        <strong>

                            Total Amount:
                            Rs.

                            <?php
                            echo number_format(
                                $confirmation['total_amount']
                            );
                            ?>

                        </strong>

                    </p>


                <?php endif; ?>


                <p>

                    We look forward to seeing you at
                    Book a Bite!

                </p>


            </div>

        </div>

    <?php endif; ?>



    <!-- =================================================
         RESERVATION FORM
    ================================================== -->

    <form
        class="reservation-form"
        method="POST"
        action="Reserve.php"
        id="reservation-form"
    >


        <!-- =================================================
             CUSTOMER INFORMATION
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    01
                </span>


                <div>

                    <h2>
                        Customer Information
                    </h2>

                    <p>
                        Your registered account details
                    </p>

                </div>

            </div>



            <div class="input-grid">


                <!-- FULL NAME -->

                <div class="input-box">

                    <label>
                        Full Name
                    </label>


                    <input
                        type="text"
                        value="<?php echo htmlspecialchars($full_name); ?>"
                        readonly
                    >

                </div>



                <!-- EMAIL -->

                <div class="input-box">

                    <label>
                        Email
                    </label>


                    <input
                        type="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        readonly
                    >

                </div>



                <!-- PHONE -->

                <div class="input-box">

                    <label>
                        Phone Number
                    </label>


                    <input
                        type="tel"
                        name="phone"
                        placeholder="Enter phone number (10 digits)"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        inputmode="numeric"
                        title="Phone number must be exactly 10 digits"
                        required
                    >

                </div>


            </div>

        </section>



        <!-- =================================================
             RESERVATION DETAILS
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    02
                </span>


                <div>

                    <h2>
                        Reservation Details
                    </h2>

                    <p>
                        Choose when you want to dine
                    </p>

                </div>

            </div>



            <div class="input-grid">


                <!-- DATE -->

                <div class="input-box">

                    <label>
                        Date
                    </label>


                    <input
                        type="date"
                        name="date"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>



                <!-- TIME -->

                <div class="input-box">

                    <label>
                        Time
                    </label>


                    <select
                        name="time"
                        required
                    >

                        <option value="">
                            Select Time
                        </option>

                        <option>
                            11:00 AM
                        </option>

                        <option>
                            12:00 PM
                        </option>

                        <option>
                            01:00 PM
                        </option>

                        <option>
                            02:00 PM
                        </option>

                        <option>
                            05:00 PM
                        </option>

                        <option>
                            06:00 PM
                        </option>

                        <option>
                            07:00 PM
                        </option>

                        <option>
                            08:00 PM
                        </option>

                        <option>
                            09:00 PM
                        </option>

                    </select>

                </div>



                <!-- GUESTS -->

                <div class="input-box">

                    <label>
                        Number of Guests
                    </label>


                    <select
                        name="guests"
                        required
                    >

                        <option value="">
                            Select Guests
                        </option>

                        <option>
                            1 Guest
                        </option>

                        <option>
                            2 Guests
                        </option>

                        <option>
                            3 Guests
                        </option>

                        <option>
                            4 Guests
                        </option>

                        <option>
                            5 Guests
                        </option>

                        <option>
                            6 Guests
                        </option>

                        <option>
                            7 Guests
                        </option>

                        <option>
                            8 Guests
                        </option>

                    </select>

                </div>


            </div>

        </section>



        <!-- =================================================
             SEATING
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    03
                </span>


                <div>

                    <h2>
                        Choose Your Seating
                    </h2>

                    <p>
                        Select your preferred dining area
                    </p>

                </div>

            </div>



            <div class="choice-grid">


                <!-- INDOOR -->

                <label class="choice-card">

                    <input
                        type="radio"
                        name="seating"
                        value="Indoor"
                        required
                        onclick="showTables('Indoor')"
                    >


                    <div>

                        <strong>
                            Indoor
                        </strong>

                        <small>
                            Comfortable indoor dining
                        </small>

                    </div>

                </label>



                <!-- OUTDOOR -->

                <label class="choice-card">

                    <input
                        type="radio"
                        name="seating"
                        value="Outdoor"
                        onclick="showTables('Outdoor')"
                    >


                    <div>

                        <strong>
                            Outdoor
                        </strong>

                        <small>
                            Relaxed open-air dining
                        </small>

                    </div>

                </label>



                <!-- ROOFTOP -->

                <label class="choice-card">

                    <input
                        type="radio"
                        name="seating"
                        value="Rooftop"
                        onclick="showTables('Rooftop')"
                    >


                    <div>

                        <strong>
                            Rooftop
                        </strong>

                        <small>
                            Dining under the stars
                        </small>

                    </div>

                </label>


            </div>

        </section>



        <!-- =================================================
             TABLE SELECTION
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    04
                </span>


                <div>

                    <h2>
                        Select Your Table
                    </h2>

                    <p>
                        Choose a seating area above to see available tables
                    </p>

                </div>

            </div>



            <p
                id="no-seating-msg"
                class="food-desc"
            >
                Please select a seating area first.
            </p>



            <!-- INDOOR TABLES -->

            <div
                class="table-grid seating-tables"
                data-seating="Indoor"
                style="display:none;"
            >


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 01"
                    >

                    <div>

                        <strong>
                            Table 01
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 02"
                    >

                    <div>

                        <strong>
                            Table 02
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 03"
                    >

                    <div>

                        <strong>
                            Table 03
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 04"
                    >

                    <div>

                        <strong>
                            Table 04
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 05"
                    >

                    <div>

                        <strong>
                            Table 05
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 06"
                    >

                    <div>

                        <strong>
                            Table 06
                        </strong>

                        <small>
                            6 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 07"
                    >

                    <div>

                        <strong>
                            Table 07
                        </strong>

                        <small>
                            6 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Indoor - Table 08"
                    >

                    <div>

                        <strong>
                            Table 08
                        </strong>

                        <small>
                            8 Seats
                        </small>

                    </div>

                </label>


            </div>



            <!-- OUTDOOR TABLES -->

            <div
                class="table-grid seating-tables"
                data-seating="Outdoor"
                style="display:none;"
            >


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Outdoor - Table 01"
                    >

                    <div>

                        <strong>
                            Table 01
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Outdoor - Table 02"
                    >

                    <div>

                        <strong>
                            Table 02
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Outdoor - Table 03"
                    >

                    <div>

                        <strong>
                            Table 03
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Outdoor - Table 04"
                    >

                    <div>

                        <strong>
                            Table 04
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Outdoor - Table 05"
                    >

                    <div>

                        <strong>
                            Table 05
                        </strong>

                        <small>
                            6 Seats
                        </small>

                    </div>

                </label>


            </div>



            <!-- ROOFTOP TABLES -->

            <div
                class="table-grid seating-tables"
                data-seating="Rooftop"
                style="display:none;"
            >


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Rooftop - Table 01"
                    >

                    <div>

                        <strong>
                            Table 01
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Rooftop - Table 02"
                    >

                    <div>

                        <strong>
                            Table 02
                        </strong>

                        <small>
                            2 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Rooftop - Table 03"
                    >

                    <div>

                        <strong>
                            Table 03
                        </strong>

                        <small>
                            4 Seats
                        </small>

                    </div>

                </label>


                <label class="table">

                    <input
                        type="radio"
                        name="table"
                        value="Rooftop - Table 04"
                    >

                    <div>

                        <strong>
                            Table 04
                        </strong>

                        <small>
                            8 Seats
                        </small>

                    </div>

                </label>


            </div>

        </section>



        <!-- =================================================
             OCCASION
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    05
                </span>


                <div>

                    <h2>
                        Dining Occasion
                    </h2>

                    <p>
                        What brings you to Book a Bite?
                    </p>

                </div>

            </div>



            <div class="occasion-grid">


                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Birthday"
                        required
                    >

                    <span>
                        Birthday
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Anniversary"
                    >

                    <span>
                        Anniversary
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Date"
                    >

                    <span>
                        Date
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Casual Dining"
                    >

                    <span>
                        Casual Dining
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Friends get together"
                    >

                    <span>
                        Friends get together
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Business meeting"
                    >

                    <span>
                        Business meeting
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Valentine's Day"
                    >

                    <span>
                        Valentine's Day
                    </span>

                </label>



                <label>

                    <input
                        type="radio"
                        name="occasion"
                        value="Other's"
                    >

                    <span>
                        Other
                    </span>

                </label>


            </div>

        </section>



        <!-- =================================================
             FOOD PRE-ORDER
        ================================================== -->

        <section class="section">


            <div class="section-heading">

                <span>
                    06
                </span>


                <div>

                    <h2>
                        Food Pre-order
                    </h2>

                    <p>
                        Optional — order before you arrive
                    </p>

                </div>

            </div>



            <div class="preorder">


                <p>
                    Would you like to pre-order your food?
                </p>



                <label>

                    <input
                        type="radio"
                        name="preorder"
                        value="yes"
                        onclick="
                            document.getElementById('preorder-items').style.display='block';
                            updateTotal();
                        "
                    >

                    Yes

                </label>



                <label>

                    <input
                        type="radio"
                        name="preorder"
                        value="no"
                        checked
                        onclick="
                            document.getElementById('preorder-items').style.display='none';
                            updateTotal();
                        "
                    >

                    No

                </label>


            </div>



            <div
                id="preorder-items"
                class="food-list"
                style="display:none;"
            >


                <?php foreach (
                    $menu_items
                    as $category => $items
                ): ?>


                    <h3 class="food-category">

                        <?php
                        echo htmlspecialchars($category);
                        ?>

                    </h3>



                    <?php foreach (
                        $items
                        as $item
                    ): ?>


                        <div class="food">


                            <?php if (
                                !empty($item['image_url'])
                            ): ?>

                                <img
                                    src="<?php echo htmlspecialchars($item['image_url']); ?>"
                                    alt=""
                                    class="food-img"
                                >

                            <?php endif; ?>



                            <div class="food-info">


                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $item['name']
                                    );
                                    ?>

                                </strong>


                                <small>

                                    <?php
                                    echo htmlspecialchars(
                                        $item['price']
                                    );
                                    ?>

                                </small>



                                <?php if (
                                    !empty($item['description'])
                                ): ?>

                                    <p class="food-desc">

                                        <?php
                                        echo htmlspecialchars(
                                            $item['description']
                                        );
                                        ?>

                                    </p>

                                <?php endif; ?>


                            </div>



                            <input
                                type="number"
                                name="item_qty[<?php echo $item['id']; ?>]"
                                min="0"
                                value="0"
                                data-price="<?php echo $item['numeric_price']; ?>"
                                oninput="updateTotal()"
                                class="qty-input"
                            >


                        </div>


                    <?php endforeach; ?>


                <?php endforeach; ?>



                <div class="total-box">

                    <span>
                        Total Amount
                    </span>


                    <span id="total-display">
                        Rs. 0
                    </span>

                </div>


            </div>

        </section>



        <!-- =================================================
             CONFIRM
        ================================================== -->

        <div class="confirm">


            <div>

                <p>
                    READY TO DINE?
                </p>


                <h2>
                    Confirm Your Reservation
                </h2>


                <small>

                    Your reservation details will be saved
                    after confirmation.

                </small>

            </div>



            <button type="submit">

                Confirm Reservation →

            </button>


        </div>


    </form>


</main>



<!-- =====================================================
     FOOTER
====================================================== -->

<footer>

    © 2026 Book a Bite

</footer>



<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>


function showTables(seating) {


    document.getElementById(
        'no-seating-msg'
    ).style.display = 'none';


    document.querySelectorAll(
        '.seating-tables'
    ).forEach(function (group) {


        if (
            group.dataset.seating === seating
        ) {


            group.style.display = 'grid';


            group.querySelectorAll(
                'input[type="radio"]'
            ).forEach(
                r => r.required = false
            );


            group.querySelector(
                'input[type="radio"]'
            ).required = true;


        } else {


            group.style.display = 'none';


            group.querySelectorAll(
                'input[type="radio"]'
            ).forEach(function (r) {

                r.required = false;

                r.checked = false;

            });

        }

    });

}



function updateTotal() {


    const preorderYes =
        document.querySelector(
            'input[name="preorder"][value="yes"]'
        ).checked;


    const totalDisplay =
        document.getElementById(
            'total-display'
        );


    if (!preorderYes) {

        totalDisplay.textContent =
            'Rs. 0';

        return;
    }


    let total = 0;


    document.querySelectorAll(
        '.qty-input'
    ).forEach(function (input) {


        const price =
            parseInt(
                input.dataset.price,
                10
            ) || 0;


        const qty =
            parseInt(
                input.value,
                10
            ) || 0;


        total += price * qty;

    });


    totalDisplay.textContent =
        'Rs. ' +
        total.toLocaleString('en-IN');

}

</script>


</body>

</html>