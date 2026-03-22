<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['order']) || !isset($_GET['user'])) {
    header("Location: users.php");
    exit();
}
if (!isset($_GET['order']) || !isset($_GET['user'])) {
    die("Invalid request");
}

$order_id = (int)$_GET['order'];
$user_id  = (int)$_GET['user'];

/* VERIFY ORDER BELONGS TO USER */
$check = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT o.order_id, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id='$order_id' AND o.user_id='$user_id'
"));


if (!$check) {
    die("Order not found for this user");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Order Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php include('includes/header.php'); ?>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include('includes/sidebar.php'); ?>

        <!-- MAIN -->
        <div class="col-md-10 p-4">

            <a href="orders.php?user=<?php echo $user_id; ?>"
               class="btn btn-outline-secondary mb-3">
               ← Back to User Orders
            </a>

            <h4>🧾 Order #<?php echo $order_id; ?>  
                <small class="text-muted">(<?php echo $check['name']; ?>)</small>
            </h4>

            <table class="table table-bordered align-middle text-center mt-3">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $items = mysqli_query($conn,"
                    SELECT oi.*, p.title
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.product_id
                    WHERE oi.order_id='$order_id'
                ");

                $i = 1;
                $grand = 0;

                while ($row = mysqli_fetch_assoc($items)) {
                    $total = $row['price'] * $row['quantity'];
                    $grand += $total;
                ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $row['title']; ?></td>
                        <td>₹<?php echo $row['price']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>₹<?php echo $total; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <h5 class="text-end">Grand Total: ₹<?php echo $grand; ?></h5>

        </div>
    </div>
</div>

</body>
</html>
