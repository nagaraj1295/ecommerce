<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h4>Order #<?php echo $order_id; ?> Items</h4>

    <table class="table table-bordered text-center align-middle mt-3">
        <thead class="table-success">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $items = mysqli_query($conn, "
                SELECT oi.*, p.title
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id='$order_id'
            ");

            while ($row = mysqli_fetch_assoc($items)) {
            ?>
            <tr>
                <td><?php echo $row['title']; ?></td>
                <td>₹<?php echo $row['price']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td>₹<?php echo $row['price'] * $row['quantity']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="orders.php" class="btn btn-secondary">Back</a>
</div>

</body>
</html>
