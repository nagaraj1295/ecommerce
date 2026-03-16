<?php
session_start();
include('../includes/db.php');

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
$buy_now = isset($_GET['buy_now']) ? true : false;

/* FETCH PRODUCT */
$product = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM products WHERE product_id='$product_id'")
);

/* INIT CART */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ADD TO CART */
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['qty'] += $qty;
} else {
    $_SESSION['cart'][$product_id] = [
        'title' => $product['title'],
        'price' => $product['price'],
        'qty'   => $qty,
        'image' => $product['image']
    ];
}

/* 🔥 BUY NOW LOGIC */
if (isset($_GET['buy_now'])) {
    header("Location: checkout.php");
    exit();
}

/* NORMAL ADD TO CART */
header("Location: cart.php");
exit();
