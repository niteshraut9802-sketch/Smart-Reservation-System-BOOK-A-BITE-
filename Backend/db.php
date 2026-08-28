<?php

$conn = mysqli_connect("localhost", "root", "", "project");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function clean($a) {
    return mysqli_real_escape_string($GLOBALS['conn'], trim($a));
}

?>