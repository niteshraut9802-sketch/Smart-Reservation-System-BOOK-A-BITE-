<?php
// Assumes session_start() was already called by the parent page
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php?error=" . urlencode("Please log in as admin."));
    exit;
}
?>