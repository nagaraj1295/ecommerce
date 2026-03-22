<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$where = '';
if (isset($_GET['user'])) {
    $uid = $_GET['user'];
    $where = "WHERE o.user_id='$uid'";
}

$orders = mysqli_query($conn, "
    SELECT o.*, u.name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    $where
    ORDER BY o.order_id DESC
");

/* USER FILTER */
$where = "";
$user_name = "All Users";

if (isset($_GET['user'])) {
    $uid = (int)$_GET['user'];
    $where = "WHERE o.user_id='$uid'";

    $u = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT name FROM users WHERE user_id='$uid'")
    );
    $user_name = $u ? $u['name'] : "Unknown User";
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->
            <div class="col-md-2 bg-dark min-vh-100">
                <h5 class="text-white text-center py-3">Admin Panel</h5>
                <a href="dashboard.php" class="d-block text-white p-2">Dashboard</a>
                <a href="orders.php" class="d-block text-white p-2 bg-success">Orders</a>
                <a href="logout.php" class="d-block text-danger p-2">Logout</a>
            </div>

            <!-- CONTENT -->
            <div class="col-md-10 p-4">
                <h4 class="mb-4">All Orders</h4>

                <table class="table table-bordered text-center align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Update</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $orders = mysqli_query($conn, "
                        SELECT o.*, u.name 
                        FROM orders o
                        JOIN users u ON o.user_id = u.user_id $where
                        ORDER BY o.order_id DESC
                    ");
                        $i=0;
                        while ($row = mysqli_fetch_assoc($orders)) {
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td>₹<?php echo $row['total_amount']; ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $row['order_status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['order_date']; ?></td>

                                <td>
                                    <a href="user_order_items.php?order=<?php echo (int)$row['order_id']; ?>&user=<?php echo (int)$row['user_id']; ?>"
                                        class="btn btn-primary btn-sm">
                                        View Items
                                    </a>
                                </td>

                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <select name="status" class="form-select form-select-sm">
                                            <option>Pending</option>
                                            <option>Shipped</option>
                                            <option>Delivered</option>
                                        </select>
                                        <button name="update_status" class="btn btn-success btn-sm">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="orders.php?delete=<?php echo $row['order_id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Delete this order?')">
                                        ❌
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</body>

</html>
<?php
if (isset($_POST['update_status'])) {

    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status
    mysqli_query($conn, "
        UPDATE orders 
        SET order_status='$status'
        WHERE order_id='$order_id'
    ");

    // If status is Delivered, also update payment status
    if ($status === 'Delivered') {
        mysqli_query($conn, "
            UPDATE payments 
            SET payment_status='Completed' 
            WHERE order_id='$order_id'
        ");
    }

    echo "<script>window.location='orders.php';</script>";
}

?>
<?php
/* DELETE ORDER */
if (isset($_GET['delete'])) {

    $order_id = $_GET['delete'];

    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$order_id'");
    mysqli_query($conn, "DELETE FROM payments WHERE order_id='$order_id'");
    mysqli_query($conn, "DELETE FROM orders WHERE order_id='$order_id'");

    echo "<script>alert('Order deleted');
          window.location='orders.php" . (isset($_GET['user']) ? '?user=' . $uid : '') . "';</script>";
}
?>