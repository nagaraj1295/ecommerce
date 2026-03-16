<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Optional: delete image file (advanced)
    $img = mysqli_fetch_assoc(mysqli_query(
        $conn, "SELECT image FROM products WHERE product_id='$id'"
    ));

    if ($img && file_exists("../uploads/products/".$img['image'])) {
        unlink("../uploads/products/".$img['image']);
    }

    mysqli_query($conn, "DELETE FROM products WHERE product_id='$id'");
}

header("Location: view_products.php");
exit();
