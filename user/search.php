<?php
session_start();
include('../includes/db.php');

if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    header("Location: index.php");
    exit();
}

$search = trim($_GET['q']);
$search_safe = mysqli_real_escape_string($conn, $search);

/* SEARCH QUERY */
$products = mysqli_query($conn, "
    SELECT * FROM products
    WHERE title LIKE '%$search_safe%'
    ORDER BY RAND()
");


/* CART COUNT */
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}

/* WISHLIST COUNT */
$wishlist_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $res = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM wishlist WHERE user_id = $uid"
    );
    $row = mysqli_fetch_assoc($res);
    $wishlist_count = $row['total'] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Search: <?php echo htmlspecialchars($search); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        /* HERO */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, .5), rgba(0, 0, 0, .5)),
                url('https://images.unsplash.com/photo-1542838132-92c53300491e');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 90px 20px;
            border-radius: 0 0 30px 30px;
        }

        /* PRODUCT CARD */
        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, .15);
        }

        .product-card img {
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            display: flex;
            flex-direction: column;
        }

        .card-body .btn-group,
        .card-body .buttons {
            margin-top: auto;
        }

        .product-title {
            min-height: 45px;
            /* keeps titles equal */
            font-size: 15px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <!-- TOP BAR -->
    <div class="bg-dark text-white text-center py-1 small">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="me-2">
                Welcome <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        <?php else: ?>
            <span class="me-2">Welcome Guest</span>
            <a href="login.php" class="btn btn-outline-success btn-sm">Login</a>
        <?php endif; ?>


    </div>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">

            <!-- LOGO -->
            <a class="navbar-brand fw-bold text-success" href="index.php">
                FruitShop
            </a>

            <!-- MOBILE SEARCH ICON -->
            <button class="btn btn-outline-success d-lg-none ms-auto me-2"
                data-bs-toggle="collapse"
                data-bs-target="#mobileSearch">
                🔍
            </button>

            <!-- TOGGLER -->
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- NAV LINKS -->
            <div id="nav" class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="#products">Shop</a>
                    </li>

                    <!-- MY ACCOUNT -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold"
                            data-bs-toggle="dropdown" href="#">
                            My Account
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isset($_SESSION['user_id'])) { ?>
                                <li><a class="dropdown-item" href="my_orders.php">My Orders</a></li>
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            <?php } else { ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                            <?php } ?>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="contact.php">Contact Us</a>
                    </li>

                    <!-- CART -->
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="cart.php">
                            Cart
                            <?php if ($cart_count > 0) { ?>
                                <span class="badge bg-danger"><?php echo $cart_count; ?></span>
                            <?php } ?>
                        </a>
                    </li>

                    <!-- WISHLIST -->
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="wishlist.php">
                            Wishlist
                            <?php if ($wishlist_count > 0) { ?>
                                <span class="badge bg-danger"><?php echo $wishlist_count; ?></span>
                            <?php } ?>
                        </a>
                    </li>

                    <!-- DESKTOP SEARCH -->
                    <li class="nav-item d-none d-lg-block ms-3">
                        <form class="d-flex" action="search.php" method="GET">
                            <input class="form-control form-control-sm me-2"
                                type="search"
                                name="q"
                                placeholder="Search products..."
                                required>
                            <button class="btn btn-success btn-sm">
                                Search
                            </button>
                        </form>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- MOBILE SEARCH BAR -->
    <div class="collapse d-lg-none bg-light px-3 py-2" id="mobileSearch">
        <form class="d-flex" action="search.php" method="GET">
            <input class="form-control me-2"
                type="search"
                name="q"
                placeholder="Search products..."
                required>
            <button class="btn btn-success">Go</button>
        </form>
    </div>

    <!-- HERO -->
    <section class="hero text-center">
        <h1 class="fw-bold">Fruits & Vegetable Shop</h1>
        <p class="lead">Fresh fruits and vegetables delivered to your doorstep</p>
    </section>


    <div class="container my-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>
                Search results for:
                <strong><?php echo htmlspecialchars($search); ?></strong>
            </h5>

            <a href="index.php" class="btn btn-outline-success btn-sm">
                ← Back to Home
            </a>
        </div>

        <?php if (mysqli_num_rows($products) == 0) { ?>

            <div class="alert alert-warning">
                No products found.
            </div>

        <?php } else { ?>

            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($products)) { ?>

                    
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card product-card">
                                    <?php
                                    $imgs = mysqli_query($conn, "
    SELECT image FROM product_images 
    WHERE product_id='{$row['product_id']}'
");
                                    ?>
                                    <!-- PRODUCT IMAGE -->
                                    <div id="carousel_<?php echo $row['product_id']; ?>"
                                        class="carousel slide"
                                        data-bs-ride="carousel"
                                        data-bs-interval="2500">

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
                                                        class="d-block w-100"
                                                        style="height:200px; object-fit:cover;">
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="card-body text-center">

                                        <div class="product-title">
                                            <?php echo $row['title']; ?>
                                        </div>

                                        <p class="text-success fw-bold mb-2">
                                            ₹<?php echo $row['price']; ?>
                                        </p>

                                        <div class="buttons">
                                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>"
                                                class="btn btn-outline-primary btn-sm w-100 mb-1">
                                                View Details
                                            </a>

                                            <a href="add_to_cart.php?id=<?php echo $row['product_id']; ?>"
                                                class="btn btn-success btn-sm w-100">
                                                Add to Cart
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        <?php } ?>
                        </div>

                    <?php } ?>

                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include('./footer.php'); ?>
</body>

</html>