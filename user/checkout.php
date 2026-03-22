<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container my-5">
        <h3 class="mb-4">Checkout</h3>
        <a href="cart.php" class="btn btn-outline-success mb-3">← Back to Cart</a>


        <div class="row">
            <!-- ADDRESS -->
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5>Delivery Address</h5>

                    <form method="POST">
                        <textarea name="address" class="form-control mb-3" required placeholder="Enter delivery address"></textarea>

                        <select name="payment_method" class="form-control" required>
                            <option value="">Select Payment Method</option>
                            <option value="COD">Cash on Delivery</option>
                            <option value="Online">Online Payment</option>
                        </select>


                        <button name="place_order" class="btn btn-success w-100 mt-3">Place Order</button>
                    </form>
                </div>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h5>Order Summary</h5>

                    <ul class="list-group mb-3">
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item) {
                            $sub = $item['price'] * $item['qty'];
                            $total += $sub;
                        ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <?php echo $item['title']; ?> × <?php echo $item['qty']; ?>
                                <span>₹<?php echo $sub; ?></span>
                            </li>
                        <?php } ?>
                    </ul>

                    <h5 class="text text-end">Total Payable: <strong>₹<?php echo $total; ?></strong></h5>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
<?php
if (isset($_POST['place_order'])) {

    // ✅ Make sure user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];

    // ✅ Calculate total once
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['qty'];
    }

    // ✅ Insert order
    mysqli_query($conn, "
        INSERT INTO orders (user_id, total_amount, order_status)
        VALUES ('$user_id', '$total', 'Pending')
    ");

    $order_id = mysqli_insert_id($conn);

    // ✅ Insert order items
    foreach ($_SESSION['cart'] as $pid => $item) {
        mysqli_query($conn, "
            INSERT INTO order_items (order_id, product_id, price, quantity)
            VALUES ('$order_id', '$pid', '{$item['price']}', '{$item['qty']}')
        ");
    }

    // ✅ Insert payment
    mysqli_query($conn, "
        INSERT INTO payments (order_id, amount, payment_method, payment_status)
        VALUES ('$order_id', '$total', '$payment_method', 'Pending')
    ");

    // ✅ Clear cart
    unset($_SESSION['cart']);

    // ✅ Redirect
    header("Location: my_orders.php");
    exit();
}
?>