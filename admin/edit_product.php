<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$product = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM products WHERE product_id='$id'")
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h4>Edit Product</h4>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow">

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo $product['title']; ?>" class="form-control">
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="number" name="price" value="<?php echo $product['price']; ?>" class="form-control">
        </div>

        <div class="mb-3">
            <label>Short Description</label>
            <input type="text" name="short_desc" value="<?php echo $product['short_desc']; ?>" class="form-control">
        </div>

        <div class="mb-3">
            <label>Long Description</label>
            <textarea name="long_desc" class="form-control"><?php echo $product['long_desc']; ?></textarea>
        </div>

        <div class="mb-3">
            <label>Update Image (optional)</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button name="update_product" class="btn btn-success">
            Update Product
        </button>

    </form>
</div>

</body>
</html>
<?php
if (isset($_POST['update_product'])) {

    $title = $_POST['title'];
    $price = $_POST['price'];
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/products/$image");

        mysqli_query($conn, "
            UPDATE products SET
            title='$title',
            price='$price',
            short_desc='$short_desc',
            long_desc='$long_desc',
            image='$image'
            WHERE product_id='$id'
        ");
    } else {
        mysqli_query($conn, "
            UPDATE products SET
            title='$title',
            price='$price',
            short_desc='$short_desc',
            long_desc='$long_desc'
            WHERE product_id='$id'
        ");
    }

    echo "<script>alert('Product updated'); window.location='view_products.php';</script>";
}
?>
