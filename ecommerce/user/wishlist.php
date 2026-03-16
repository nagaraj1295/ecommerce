<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
?>



<!DOCTYPE html>
<html>

<head>
    <title>My Wishlist</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container my-5">
        <h3>❤️ My Wishlist</h3>
        <a href="index.php" class="btn btn-outline-success mb-3">
            ← Continue Shopping
        </a>


        <div class="row mt-4">
            <?php
            $query = "
            SELECT p.* 
            FROM wishlist w
            JOIN products p ON w.product_id = p.product_id
            WHERE w.user_id='$user_id'
        ";
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) == 0) {
                echo "<div class='alert alert-info'>
                        Your wishlist is empty.
                    </div>

                    <a href='index.php' class='btn btn-success'>Browse Products</a>";
            }


            while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="../uploads/products/<?php echo $row['image']; ?>" class="card-img-top">
                        <div class="card-body text-center">
                            <h6><?php echo $row['title']; ?></h6>
                            <p>₹<?php echo $row['price']; ?></p>
                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn btn-success btn-sm">
                                View
                            </a>
                            <a href="remove_wishlist.php?id=<?php echo $row['product_id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Remove from wishlist?')">
                                Remove
                            </a>

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

</body>

</html>