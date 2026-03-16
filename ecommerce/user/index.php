<?php
session_start();
include('../includes/db.php');

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

/* CATEGORY */
$cat = $_GET['cat'] ?? 'all';
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fruits & Vegetable Shop</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

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


        /* CATEGORY BUTTON */
        .category-btn input {
            display: none;
        }

        .category-btn label {
            padding: 8px 20px;
            border-radius: 30px;
            border: 1px solid #198754;
            cursor: pointer;
            margin: 0 5px;
        }

        .category-btn input:checked+label {
            background-color: #198754;
            color: white;
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

    <!-- CATEGORY FILTER -->
    <div class="container text-center my-4">
        <form method="GET">
            <div class="category-btn">
                <input type="radio" name="cat" value="all" id="all"
                    onchange="this.form.submit()" <?= $cat == 'all' ? 'checked' : '' ?>>
                <label for="all">All</label>

                <input type="radio" name="cat" value="fruits" id="fruits"
                    onchange="this.form.submit()" <?= $cat == 'fruits' ? 'checked' : '' ?>>
                <label for="fruits">Fruits</label>

                <input type="radio" name="cat" value="vegetables" id="veg"
                    onchange="this.form.submit()" <?= $cat == 'vegetables' ? 'checked' : '' ?>>
                <label for="veg">Vegetables</label>
            </div>
        </form>

    </div>

    <!-- PRODUCTS -->
    <div class="container" id="products">
        <div class="row">
            <?php

            $cat = $_GET['cat'] ?? 'all';

            if ($cat == 'fruits') {
                $sql = "SELECT * FROM products WHERE category_id = 1";
            } elseif ($cat == 'vegetables') {
                $sql = "SELECT * FROM products WHERE category_id = 2";
            } else {
                $sql = "SELECT * FROM products ORDER BY RAND()";
            }

            $res = mysqli_query($conn, $sql);
            if (mysqli_num_rows($res) == 0) {
                echo "<h5>No products available right now</h5>";
            }
            while ($row = mysqli_fetch_assoc($res)) {
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card">
                        <?php
                        $imgs = mysqli_query($conn, "
    SELECT image FROM product_images 
    WHERE product_id='{$row['product_id']}'
");
                        ?>

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
                            <h6 class="fw-semibold"><?php echo $row['title']; ?></h6>
                            <p class="text-muted small"><?php echo $row['short_desc']; ?></p>
                            <h5 class="text-success fw-bold">₹<?php echo $row['price']; ?></h5>

                            <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn btn-success btn-sm w-100"> Buy Now </a>


                            <a href="add_to_cart.php?id=<?php echo $row['product_id']; ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">Add to Cart </a>

                            <a href="add_to_wishlist.php?id=<?php echo $row['product_id']; ?>" class="btn btn-outline-danger btn-sm w-100 mt-2">Add to Wishlist</a>

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        © <?php echo date("Y"); ?> Fruits & Vegetable Shop
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>