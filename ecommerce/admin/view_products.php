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
    <title>View Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark min-vh-100">
            <h5 class="text-white text-center py-3">Admin Panel</h5>
            <a href="dashboard.php" class="d-block text-white p-2">Dashboard</a>
            <a href="insert_product.php" class="d-block text-white p-2">Insert Product</a>
            <a href="view_products.php" class="d-block text-white p-2 bg-success">View Products</a>
            <a href="logout.php" class="d-block text-danger p-2">Logout</a>
        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">
            <h4 class="mb-4">All Products</h4>

            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $query = "
                        SELECT p.*, c.category_title 
                        FROM products p
                        JOIN categories c ON p.category_id = c.category_id
                        ORDER BY p.product_id DESC
                    ";
                    $products = mysqli_query($conn, $query);

                    while ($row = mysqli_fetch_assoc($products)) {
                    ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td>
                            <img src="../uploads/products/<?php echo $row['image']; ?>" width="60">
                        </td>
                        <td><?php echo $row['title']; ?></td>
                        <td>₹<?php echo $row['price']; ?></td>
                        <td><?php echo $row['category_title']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $row['product_id']; ?>"
                               class="btn btn-warning btn-sm">Edit</a>
                        </td>
                        <td>
                            <a href="delete_product.php?id=<?php echo $row['product_id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure?')">
                               Delete
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
