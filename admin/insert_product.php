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
    <title>Insert Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR (simple reuse) -->
            <div class="col-md-2 bg-dark min-vh-100">
                <h5 class="text-white text-center py-3">Admin Panel</h5>
                <a href="dashboard.php" class="d-block text-white p-2">Dashboard</a>
                <a href="insert_product.php" class="d-block text-white p-2 bg-success">Insert Product</a>
                <a href="view_products.php" class="d-block text-white p-2">View Products</a>
                <a href="logout.php" class="d-block text-danger p-2">Logout</a>
            </div>

            <!-- MAIN CONTENT -->
            <div class="col-md-10 p-4">
                <h4 class="mb-4">Insert New Product</h4>

                <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">

                    <!-- PRODUCT TITLE -->
                    <div class="mb-3">
                        <label class="form-label">Product Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <!-- CATEGORY -->
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $cats = mysqli_query($conn, "SELECT * FROM categories");
                            while ($cat = mysqli_fetch_assoc($cats)) {
                            ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo $cat['category_title']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- PRICE -->
                    <div class="mb-3">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>

                    <!-- SHORT DESC -->
                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <input type="text" name="short_desc" class="form-control" required>
                    </div>

                    <!-- LONG DESC -->
                    <div class="mb-3">
                        <label class="form-label">Long Description</label>
                        <textarea name="long_desc" class="form-control" rows="4" required></textarea>
                    </div>

                    <!-- DELIVERY -->
                    <div class="mb-3">
                        <label class="form-label">Delivery Days</label>
                        <input type="text" name="delivery_days" class="form-control" placeholder="e.g. 2-3 days" required>
                    </div>

                    <!-- IMAGE -->
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="images[]" multiple class="form-control" required>
                    </div>

                    <!-- SUBMIT -->
                    <button name="insert_product" class="btn btn-success">
                        Insert Product
                    </button>

                </form>
            </div>

        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>
<?php
if (isset($_POST['insert_product'])) {

    $title = $_POST['title'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];
    $delivery_days = $_POST['delivery_days'];

    // Insert first image into products table (main image)
    $main_image = $_FILES['images']['name'][0]; // First image as main
    $tmp_main_image = $_FILES['images']['tmp_name'][0];

    // Move image to uploads folder
    move_uploaded_file($tmp_main_image, "../uploads/products/$main_image");


    // Insert into database
    $sql = "INSERT INTO products 
        (category_id, title, short_desc, long_desc, price, image, delivery_days)
        VALUES 
        ('$category_id', '$title', '$short_desc', '$long_desc', '$price', '$main_image', '$delivery_days')";

    if (mysqli_query($conn, $sql)) {
        $pid = mysqli_insert_id($conn); // Get the last inserted product ID

        // Insert all images into product_images table
        foreach ($_FILES['images']['name'] as $key => $img) {
            $tmp_img = $_FILES['images']['tmp_name'][$key];

            if ($img != "") {
                move_uploaded_file($tmp_img, "../uploads/products/$img");

                mysqli_query($conn, "INSERT INTO product_images(product_id, image) 
                    VALUES ('$pid', '$img')");
            }
        }

        echo "<script>alert('Product inserted successfully');</script>";
        echo "<script>window.location.href='insert_product.php';</script>";
    } else {
        echo "<script>alert('Error inserting product');</script>";
    }
}

?>