<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $pid = $_GET['id'];
    $uid = $_SESSION['user_id'];

    mysqli_query($conn, "
        DELETE FROM wishlist
        WHERE user_id='$uid' AND product_id='$pid'
    ");
}

header("Location: wishlist.php");
exit();
