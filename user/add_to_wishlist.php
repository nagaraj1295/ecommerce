<?php
session_start();
include('../includes/db.php');

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

// If guest wants to add to wishlist, remember and redirect to login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_wishlist'][] = $product_id;
    $_SESSION['after_login_redirect'] = 'wishlist';

    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if already exists
$check = mysqli_query($conn, "
    SELECT * FROM wishlist 
    WHERE user_id='$user_id' AND product_id='$product_id'
");

if (mysqli_num_rows($check) == 0) {
    mysqli_query($conn, "
        INSERT INTO wishlist (user_id, product_id)
        VALUES ('$user_id', '$product_id')
    ");
}

header("Location: wishlist.php");
exit();
