<?php
session_start();
include('../includes/db.php');

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

/* CANCEL ORDER */
if (isset($_GET['cancel'])) {
    $order_id = $_GET['cancel'];

    // Check order belongs to user & status
    $check = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT order_status FROM orders
        WHERE order_id='$order_id' AND user_id='$uid'
    "));

    if ($check && $check['order_status'] == 'Pending') {
        mysqli_query($conn, "
            UPDATE orders SET order_status='Cancelled'
            WHERE order_id='$order_id'
        ");
        echo "<script>alert('Order cancelled successfully');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container my-5">
        <h3>My Orders</h3>
        <a href="index.php" class="btn btn-outline-success mb-3">← Back to Home</a>


        <table class="table table-bordered mt-3 text-center">
            <thead class="table-success">
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                $orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY order_id DESC");

                while ($row = mysqli_fetch_assoc($orders)) {
                ?>
                    <tr>
                        <td>#<?php echo $row['order_id']; ?></td>
                        <td>₹<?php echo $row['total_amount']; ?></td>
                        <td><?php echo $row['order_status']; ?></td>
                        <td><?php echo $row['order_date']; ?></td>
                        <td><?php if ($row['order_status'] == 'Pending') { ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    <input type="hidden" name="order_id"
                                        value="<?php echo $row['order_id']; ?>">

                                    <button name="cancel_order"
                                        class="btn btn-danger btn-sm">
                                        Cancel Order
                                    </button>
                                </form>
                            <?php } elseif ($row['order_status'] == 'Shipped') { ?>
                                <span class="text-muted">Order shipped – cannot cancel</span>
                            <?php } elseif ($row['order_status'] == 'Delivered') { ?>
                                <span class="text-muted">Order delivered successfully</span>
                            <?php } elseif ($row['order_status'] == 'Cancelled') { ?>
                                <span class="text-danger">Ordered cancelled</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>

</html>
<?php
if (isset($_POST['cancel_order'])) {

    $order_id = (int)$_POST['order_id'];
    $user_id  = $_SESSION['user_id'];

    /* Allow cancel ONLY if Pending */
    $check = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT order_status
        FROM orders
        WHERE order_id='$order_id'
        AND user_id='$user_id'
    "));

    if ($check && $check['order_status'] == 'Pending') {

        mysqli_query($conn, "
            UPDATE orders
            SET order_status='Cancelled'
            WHERE order_id='$order_id'
        ");

        /* Optional: update payment */
        mysqli_query($conn, "
            UPDATE payments
            SET payment_status='Cancelled'
            WHERE order_id='$order_id'
        ");

        echo "<script>alert('Order cancelled successfully');
              window.location='my_orders.php';</script>";
    } else {
        echo "<script>alert('This order cannot be cancelled');</script>";
    }
}
?>