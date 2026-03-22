<?php
include('../includes/db.php');

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

$sql = "SELECT p.*, c.category_title 
        FROM products p 
        JOIN categories c ON p.category_id = c.category_id 
        WHERE p.product_id = '$product_id'";

$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo $product['title']; ?> | FruitShop</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .product-img {
            border-radius: 20px;
            width: 100%;
            height: 420px;
            object-fit: cover;
        }

        .badge-category {
            background-color: #198754;
        }

        .rating i {
            color: #ffc107;
        }

        .spec-box {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .05);
        }
    </style>
</head>

<body>

    <!-- NAVBAR (simple reuse) -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="index.php">FruitShop</a>
            <a href="index.php" class="btn btn-outline-success btn-sm">← Back to Shop</a>
        </div>
    </nav>

    <!-- PRODUCT DETAILS -->
    <div class="container my-5">
        <div class="row g-5">

            <!-- LEFT: IMAGE -->
            <div class="col-md-6">
                <?php
                $imgs = mysqli_query($conn, "
    SELECT image FROM product_images 
    WHERE product_id='$product_id'
");
                ?>

                <div id="detailsCarousel"
                    class="carousel slide"
                    data-bs-ride="carousel"
                    data-bs-interval="3000">

                    <div class="carousel-inner">
                        <?php
                        $active = true;
                        while ($img = mysqli_fetch_assoc($imgs)) {
                        ?>
                            <div class="carousel-item <?php if ($active) {
                                                            echo 'active';
                                                            $active = false;
                                                        } ?>">
                                <img src="../uploads/products/<?php echo $img['image']; ?>"
                                    class="d-block w-100 rounded"
                                    style="height:420px; object-fit:cover;">
                            </div>
                        <?php } ?>
                    </div>
                </div>

            </div>

            <!-- RIGHT: DETAILS -->
            <div class="col-md-6">
                <span class="badge badge-category mb-2">
                    <?php echo $product['category_title']; ?>
                </span>

                <h2 class="fw-bold mt-2"><?php echo $product['title']; ?></h2>

                <!-- RATING (STATIC FOR NOW) -->
                <div class="rating mb-2">
                    ★★★★☆ <small class="text-muted">(4.2 Reviews)</small>
                </div>

                <h3 class="text-success fw-bold mb-3">
                    ₹<?php echo $product['price']; ?>
                </h3>

                <p class="text-muted">
                    <?php echo $product['short_desc']; ?>
                </p>

                <div class="mb-3 d-flex">
                    <strong>Delivery:</strong> <?php echo $product['delivery_days']; ?>
                    <div class="d-flex mx-5 align-items-center">
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="decreaseQty()">−</button>

                        <input type="text" id="qty" value="1"
                            class="form-control text-center mx-2"
                            style="width:60px;" readonly>

                        <button type="button" class="btn btn-outline-secondary"
                            onclick="increaseQty()">+</button>
                    </div>

                </div>

                <div class="d-grid gap-2">
                    <a href="add_to_cart.php?id=<?php echo $product_id; ?>&qty=1&buy_now=1"
                        class="btn btn-success btn-lg w-100 mt-2" id="buyNowBtn">Buy Now</a>
                    <a href="add_to_cart.php?id=<?php echo $product_id; ?>&qty=1" id="addToCartBtn" class="btn btn-outline-primary btn-lg w-100" id="addToCartBtn">Add to Cart</a>
                    <a href="add_to_wishlist.php?id=<?php echo $product['product_id']; ?>"
                        class="btn btn-outline-danger btn-lg">Add to Wishlist </a>
                </div>
            </div>
        </div>

        <!-- DESCRIPTION & SPECIFICATIONS -->
        <div class="row mt-5">
            <div class="col-md-8">
                <div class="spec-box mb-4">
                    <h5 class="fw-bold">Product Description</h5>
                    <p><?php echo $product['long_desc']; ?></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="spec-box">
                    <h5 class="fw-bold">Specifications</h5>
                    <ul class="list-unstyled">
                        <li>✔ Fresh Quality</li>
                        <li>✔ Farm Picked</li>
                        <li>✔ Hygienically Packed</li>
                        <li>✔ Organic Produce</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
    $reviews = mysqli_query($conn, "
    SELECT r.*, u.name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.product_id='$product_id'
");

    $avg = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT AVG(rating) AS avg_rating 
    FROM reviews WHERE product_id='$product_id'
"));
    ?>

    <h5>Customer Reviews (⭐ <?php echo round($avg['avg_rating'], 1); ?>/5)</h5>

    <?php
    if (mysqli_num_rows($reviews) == 0) {
        echo "<p>No reviews yet</p>";
    }

    while ($row = mysqli_fetch_assoc($reviews)) {
    ?>
        <div class="border p-2 mb-2">
            <strong><?php echo $row['name']; ?></strong><br>
            Rating: <?php echo str_repeat("⭐", $row['rating']); ?><br>
            <?php echo $row['review']; ?>
        </div>
    <?php } ?>

    <?php if (isset($_SESSION['user_id'])) { ?>
        <div class="card mt-4 p-3">
            <h5>Add Review</h5>

            <form method="POST">
                <label>Rating</label>
                <select name="rating" class="form-control mb-2" required>
                    <option value="">Select</option>
                    <option value="5">★★★★★</option>
                    <option value="4">★★★★</option>
                    <option value="3">★★★</option>
                    <option value="2">★★</option>
                    <option value="1">★</option>
                </select>

                <textarea name="review" class="form-control mb-2" placeholder="Write your review" required></textarea>

                <button name="submit_review" class="btn btn-success">
                    Submit Review
                </button>
            </form>
        </div>
    <?php } else { ?>
        <p class="text-muted mt-4">Login to write a review</p>
    <?php } ?>


    <!-- FOOTER -->
    <footer class="bg-dark text-white text-center py-3">
        © <?php echo date("Y"); ?> Fruits & Vegetable Shop
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let qtyInput = document.getElementById('qty');
        let buyNowBtn = document.getElementById('buyNowBtn');
        let addToCartBtn = document.getElementById('addToCartBtn');

        function updateLinks() {
            let qty = parseInt(qtyInput.value);

            buyNowBtn.href =
                "add_to_cart.php?id=<?php echo $product_id; ?>&qty=" + qty + "&buy_now=1";

            addToCartBtn.href =
                "add_to_cart.php?id=<?php echo $product_id; ?>&qty=" + qty;
        }

        function increaseQty() {
            qtyInput.value = parseInt(qtyInput.value) + 1;
            updateLinks();
        }

        function decreaseQty() {
            if (parseInt(qtyInput.value) > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                updateLinks();
            }
        }

        /* Ensure links are correct on page load */
        updateLinks();
    </script>


</body>

</html>
<?php
if (isset($_POST['submit_review'])) {

    $rating = $_POST['rating'];
    $review = $_POST['review'];
    $user_id = $_SESSION['user_id'];
    $product_id = $_GET['id'];

    mysqli_query($conn, "
        INSERT INTO reviews (product_id, user_id, rating, review)
        VALUES ('$product_id', '$user_id', '$rating', '$review')
    ");

    echo "<script>window.location='product_details.php?id=$product_id';</script>";
}
?>