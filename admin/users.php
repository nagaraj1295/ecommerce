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
    <title>Registered Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php include('includes/header.php'); ?>

</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->
            <?php include('includes/sidebar.php'); ?>

            <!-- MAIN CONTENT -->
            <div class="col-md-10 p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>👥 Registered Users</h4>
                    <span class="badge bg-success">
                        Total Users:
                        <?php
                        echo mysqli_num_rows(mysqli_query($conn, "SELECT user_id FROM users"));
                        ?>
                    </span>
                </div>

                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered On</th>
                            <th>Total Orders</th>
                            <th>View Orders</th>
                            <th>Delete</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $users = mysqli_query($conn, "SELECT * FROM users ORDER BY user_id DESC");
                        $i = 1;

                        while ($user = mysqli_fetch_assoc($users)) {

                            $uid = $user['user_id'];

                            // COUNT USER ORDERS
                            $order_count = mysqli_fetch_assoc(
                                mysqli_query(
                                    $conn,
                                    "SELECT COUNT(*) AS total FROM orders WHERE user_id='$uid'"
                                )
                            )['total'];
                        ?>
                            <tr>

                                <td><?php echo $i++; ?></td>
                                <td><?php echo $user['name']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td>
                                    <?php
                                    echo isset($user['created_at'])
                                        ? date('d-m-Y', strtotime($user['created_at']))
                                        : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $order_count; ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="orders.php?user=<?php echo $uid; ?>"
                                        class="btn btn-info btn-sm">
                                        View Orders
                                    </a>
                                </td>


                                <td>
                                    <a href="users.php?delete=<?php echo $uid; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this user permanently?')">
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
/* ---------- DELETE USER LOGIC ---------- */
if (isset($_GET['delete'])) {
    $uid = $_GET['delete'];

    // Optional safety: delete only if no orders
    $check = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT COUNT(*) AS total FROM orders WHERE user_id='$uid'"
        )
    );

    if ($check['total'] == 0) {
        mysqli_query($conn, "DELETE FROM users WHERE user_id='$uid'");
        echo "<script>alert('User deleted'); window.location='users.php';</script>";
    } else {
        echo "<script>alert('User has orders, cannot delete');</script>";
    }
}
?>