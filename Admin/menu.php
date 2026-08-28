<?php
session_start();

include "../Backend/db.php";
include "auth_check.php";

$error = "";
$success = isset($_GET['success'])
    ? htmlspecialchars($_GET['success'])
    : '';

/* =========================================================
   EDIT MODE
========================================================= */

$edit_item = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];

    $stmt = mysqli_prepare(
        $conn,
        "SELECT * FROM menu_items WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result_edit = mysqli_stmt_get_result($stmt);
    $edit_item = mysqli_fetch_assoc($result_edit);

    mysqli_stmt_close($stmt);

    if (!$edit_item) {
        $error = "Menu item not found.";
    }
}


/* =========================================================
   ADD MENU ITEM
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['add_item'])
) {

    $category = clean($_POST['category']);
    $name     = clean($_POST['name']);
    $desc     = clean($_POST['description']);
    $price    = clean($_POST['price']);

    if (
        empty($category) ||
        empty($name) ||
        empty($price)
    ) {

        $error = "Category, name, and price are required.";

    } else {

        $image_url = null;

        /* IMAGE UPLOAD */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $allowed_types = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $file_type = mime_content_type(
                $_FILES['image']['tmp_name']
            );

            if (in_array($file_type, $allowed_types)) {

                $ext = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                $filename =
                    'menu_' .
                    time() .
                    '_' .
                    rand(1000, 9999) .
                    '.' .
                    $ext;

                $upload_directory = '../image/menu/';
                $upload_path = $upload_directory . $filename;

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0755, true);
                }

                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $upload_path
                    )
                ) {

                    $image_url =
                        'image/menu/' . $filename;
                }
            }
        }


        /* INSERT */

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO menu_items
            (category, name, description, price, image_url)
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $category,
            $name,
            $desc,
            $price,
            $image_url
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header(
            "Location: menu.php?success=" .
            urlencode("Item added successfully.")
        );

        exit;
    }
}


/* =========================================================
   UPDATE MENU ITEM
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_item'])
) {

    $id       = (int) $_POST['id'];
    $category = clean($_POST['category']);
    $name     = clean($_POST['name']);
    $desc     = clean($_POST['description']);
    $price    = clean($_POST['price']);

    if (
        empty($category) ||
        empty($name) ||
        empty($price)
    ) {

        $error = "Category, name, and price are required.";

    } else {

        /* Get existing image */

        $old_stmt = mysqli_prepare(
            $conn,
            "SELECT image_url FROM menu_items WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $old_stmt,
            "i",
            $id
        );

        mysqli_stmt_execute($old_stmt);

        $old_result = mysqli_stmt_get_result($old_stmt);
        $old_item = mysqli_fetch_assoc($old_result);

        mysqli_stmt_close($old_stmt);

        $image_url = $old_item['image_url'] ?? null;


        /* ==========================================
           NEW IMAGE UPLOAD
        ========================================== */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] === UPLOAD_ERR_OK
        ) {

            $allowed_types = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            $file_type = mime_content_type(
                $_FILES['image']['tmp_name']
            );

            if (in_array($file_type, $allowed_types)) {

                $ext = strtolower(
                    pathinfo(
                        $_FILES['image']['name'],
                        PATHINFO_EXTENSION
                    )
                );

                $filename =
                    'menu_' .
                    time() .
                    '_' .
                    rand(1000, 9999) .
                    '.' .
                    $ext;

                $upload_directory = '../image/menu/';
                $upload_path = $upload_directory . $filename;

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0755, true);
                }

                if (
                    move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $upload_path
                    )
                ) {

                    /* Delete old image */

                    if (
                        !empty($image_url) &&
                        file_exists('../' . $image_url)
                    ) {
                        unlink('../' . $image_url);
                    }

                    $image_url =
                        'image/menu/' . $filename;
                }
            }
        }


        /* ==========================================
           UPDATE DATABASE
        ========================================== */

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE menu_items
             SET category = ?,
                 name = ?,
                 description = ?,
                 price = ?,
                 image_url = ?
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $category,
            $name,
            $desc,
            $price,
            $image_url,
            $id
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header(
            "Location: menu.php?success=" .
            urlencode("Item updated successfully.")
        );

        exit;
    }
}


/* =========================================================
   DELETE MENU ITEM
========================================================= */

if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    /* Get image first */

    $img_stmt = mysqli_prepare(
        $conn,
        "SELECT image_url FROM menu_items WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $img_stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($img_stmt);

    $img_result = mysqli_stmt_get_result($img_stmt);
    $img_row = mysqli_fetch_assoc($img_result);

    mysqli_stmt_close($img_stmt);


    /* Delete image */

    if (
        $img_row &&
        !empty($img_row['image_url']) &&
        file_exists('../' . $img_row['image_url'])
    ) {

        unlink('../' . $img_row['image_url']);
    }


    /* Delete database record */

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM menu_items WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);


    header(
        "Location: menu.php?success=" .
        urlencode("Item removed successfully.")
    );

    exit;
}


/* =========================================================
   GET ALL MENU ITEMS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT * FROM menu_items ORDER BY category, id"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Menu | Admin</title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>


<body class="dashboard-body">


<header class="admin-header">

    <div class="logo">
        <span>✦</span>
        Book a Bite Admin
    </div>

    <div class="admin-nav">

        <a
            href="dashboard.php"
            class="back-btn"
        >
            ← Dashboard
        </a>

        <a
            href="logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</header>


<main class="admin-main">


    <h1>
        <?php echo $edit_item ? "Edit Menu Item" : "Manage Menu"; ?>
    </h1>


    <!-- ERROR -->

    <?php if ($error): ?>

        <p class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>


    <!-- SUCCESS -->

    <?php if ($success): ?>

        <p class="success-message">
            <?php echo $success; ?>
        </p>

    <?php endif; ?>


    <!-- =====================================================
         ADD / EDIT FORM
    ====================================================== -->

    <form
        method="POST"
        action="menu.php"
        class="add-item-form"
        enctype="multipart/form-data"
    >


        <?php if ($edit_item): ?>

            <!-- EDIT -->

            <input
                type="hidden"
                name="update_item"
                value="1"
            >

            <input
                type="hidden"
                name="id"
                value="<?php echo $edit_item['id']; ?>"
            >

        <?php else: ?>

            <!-- ADD -->

            <input
                type="hidden"
                name="add_item"
                value="1"
            >

        <?php endif; ?>


        <!-- CATEGORY -->

        <input
            type="text"
            name="category"
            placeholder="Category (e.g. Starters)"
            value="<?php
                echo $edit_item
                    ? htmlspecialchars($edit_item['category'])
                    : '';
            ?>"
            required
        >


        <!-- NAME -->

        <input
            type="text"
            name="name"
            placeholder="Item Name"
            value="<?php
                echo $edit_item
                    ? htmlspecialchars($edit_item['name'])
                    : '';
            ?>"
            required
        >


        <!-- DESCRIPTION -->

        <input
            type="text"
            name="description"
            placeholder="Description"
            value="<?php
                echo $edit_item
                    ? htmlspecialchars($edit_item['description'])
                    : '';
            ?>"
        >


        <!-- PRICE -->

        <input
            type="text"
            name="price"
            placeholder="Price (e.g. Rs. 180)"
            value="<?php
                echo $edit_item
                    ? htmlspecialchars($edit_item['price'])
                    : '';
            ?>"
            required
        >


        <!-- IMAGE -->

        <input
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
        >


        <?php if ($edit_item): ?>

            <?php if (!empty($edit_item['image_url'])): ?>

                <div style="width:100%;">

                    <p
                        style="
                            color:rgba(244,234,216,0.7);
                            margin-bottom:8px;
                        "
                    >
                        Current Image:
                    </p>

                    <img
                        src="../<?php echo htmlspecialchars($edit_item['image_url']); ?>"
                        alt=""
                        style="
                            width:100px;
                            height:100px;
                            object-fit:contain;
                            border-radius:8px;
                            background:rgba(0,0,0,0.2);
                        "
                    >

                </div>

            <?php endif; ?>


            <button type="submit">
                Update Item
            </button>


            <a
                href="menu.php"
                style="
                    display:inline-block;
                    padding:12px 20px;
                    margin-left:8px;
                    color:#f4ead8;
                    text-decoration:none;
                    border:1px solid rgba(201,169,89,0.3);
                    border-radius:8px;
                "
            >
                Cancel
            </a>

        <?php else: ?>

            <button type="submit">
                Add Item
            </button>

        <?php endif; ?>


    </form>


    <!-- =====================================================
         MENU TABLE
    ====================================================== -->

    <div class="table-wrapper">

        <table class="admin-table">

            <thead>

                <tr>

                    <th>Image</th>

                    <th>Category</th>

                    <th>Name</th>

                    <th>Description</th>

                    <th>Price</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

                <?php while ($item = mysqli_fetch_assoc($result)): ?>

                    <tr>


                        <!-- IMAGE -->

                        <td>

                            <?php if (!empty($item['image_url'])): ?>

                                <img
                                    src="../<?php echo htmlspecialchars($item['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    style="
                                        width:60px;
                                        height:60px;
                                        object-fit:contain;
                                        border-radius:8px;
                                        background:rgba(0,0,0,0.2);
                                    "
                                >

                            <?php else: ?>

                                <span
                                    style="
                                        color:rgba(244,234,216,0.3);
                                    "
                                >
                                    No image
                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- CATEGORY -->

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $item['category']
                            );
                            ?>
                        </td>


                        <!-- NAME -->

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $item['name']
                            );
                            ?>
                        </td>


                        <!-- DESCRIPTION -->

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $item['description']
                            );
                            ?>
                        </td>


                        <!-- PRICE -->

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $item['price']
                            );
                            ?>
                        </td>


                        <!-- ACTION -->

                        <td>

                            <!-- EDIT -->

                            <a
                                href="menu.php?edit=<?php echo $item['id']; ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>


                            <!-- DELETE -->

                            <a
                                href="menu.php?delete=<?php echo $item['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Remove this item?');"
                            >
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