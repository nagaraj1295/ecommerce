<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh;
            background: #212529;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 12px;
            display: block;
        }
        .sidebar a:hover {
            background: #198754;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">
            <h5 class="text-white text-center py-3">Admin Panel</h5>

            <a href="dashboard.php">Dashboard</a>
            <a href="insert_product.php">Insert Product</a>
            <a href="view_products.php">View Products</a>
            <a href="users.php">List Users</a>
            <a href="orders.php">Order Details</a>
            <a href="payments.php">Payment Details</a>
            <a href="profile.php">My Profile</a>
            <a href="logout.php" class="text-danger">Logout</a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Dashboard</h4>

                <div>
                    <img src="assets/images/<?php echo $_SESSION['admin_pic']; ?>" width="40" class="rounded-circle">
                    <strong><?php echo $_SESSION['admin_name']; ?></strong>
                </div>
            </div>

            <!-- STATS -->
            <div class="row text-center">

                <?php
                $products = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM products"));
                $users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
                $orders = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM orders"));
                ?>

                <div class="col-md-3">
                    <div class="card shadow p-3">
                        <a href="view_products.php" class="text-decoration-none text-dark">
                        <h5>Total Products</h5>
                        <h3><?php echo $products; ?></h3>
                        </a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow p-3">
                        <a href="users.php" class="text-decoration-none text-dark">
                        <h5>Total Users</h5>
                        <h3><?php echo $users; ?></h3>
                        </a>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow p-3">
                        <a href="orders.php" class="text-decoration-none text-dark">
                        <h5>Total Orders</h5>
                        <h3><?php echo $orders; ?></h3>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>
