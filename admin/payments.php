<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


// Default filter
$filter = $_GET['filter'] ?? 'daily';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark min-vh-100">
            <h5 class="text-white text-center py-3">Admin Panel</h5>
            <a href="dashboard.php" class="d-block text-white p-2">Dashboard</a>
            <a href="payments.php" class="d-block text-white p-2 bg-success">Payments</a>
            <a href="logout.php" class="d-block text-danger p-2">Logout</a>
        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">Payment Reports</h4>

            <!-- FILTER BUTTONS -->
            <div class="mb-3">
                <a href="payments.php?filter=daily" class="btn btn-outline-success btn-sm">Daily</a>
                <a href="payments.php?filter=weekly" class="btn btn-outline-primary btn-sm">Weekly</a>
                <a href="payments.php?filter=monthly" class="btn btn-outline-dark btn-sm">Monthly</a>
            </div>

            <!-- REPORT TABLE -->
            <table class="table table-bordered text-center align-middle">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $total = 0;

                    if ($filter == 'daily') {
                        $query = "
                            SELECT * FROM payments
                            WHERE DATE(payment_date) = CURDATE()
                        ";
                    } elseif ($filter == 'weekly') {
                        $query = "
                            SELECT * FROM payments
                            WHERE YEARWEEK(payment_date, 1) = YEARWEEK(CURDATE(), 1)
                        ";
                    } else {
                        $query = "
                            SELECT * FROM payments
                            WHERE MONTH(payment_date) = MONTH(CURDATE())
                            AND YEAR(payment_date) = YEAR(CURDATE())
                        ";
                    }

                    $payments = mysqli_query($conn, $query);
                    $i = 1;

                    while ($row = mysqli_fetch_assoc($payments)) {
                        $total += $row['amount'];
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>#<?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['payment_method']; ?></td>
                        <td><?php echo $row['payment_status']; ?></td>
                        <td>₹<?php echo $row['amount']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['payment_date'])); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- TOTAL -->
            <div class="text-end mt-3">
                <h5>Total Collection: <span class="text-success">₹<?php echo $total; ?></span></h5>
            </div>

        </div>
    </div>
</div>

</body>
</html>
