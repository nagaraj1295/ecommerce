<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* REMOVE CART ITEM */
if (isset($_GET['remove_cart'])) {
    $pid = $_GET['remove_cart'];

    if (isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
    }

    header("Location: cart.php");
    exit();
}

/* UPDATE QUANTITY */
if (isset($_GET['update'])) {
    $pid = $_GET['id'];
    $action = $_GET['update'];

    if ($action == 'inc') {
        $_SESSION['cart'][$pid]['qty']++;
    } elseif ($action == 'dec' && $_SESSION['cart'][$pid]['qty'] > 1) {
        $_SESSION['cart'][$pid]['qty']--;
    }
    header("Location: cart.php");
    exit();
}

/* REMOVE ITEM */
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
</head>

<body>

    <div class="container my-5">
        <h3 class="mb-4">🛒 My Cart</h3>
        <a href="index.php" class="btn btn-outline-success mb-3">Continue Shopping</a>

        <?php if (empty($_SESSION['cart'])) { ?>
            <div class="alert alert-warning">Your cart is empty</div>

        <?php } else { ?>

            <table class="table table-bordered align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Remove</th>
                    </tr>
                </thead>

                <tbody>

                    <?php
                    $grand_total = 0;

                    foreach ($_SESSION['cart'] as $id => $item) {
                        $total = $item['price'] * $item['qty'];
                        $grand_total += $total;
                    ?>
                        <tr>
                            <td>
                                <img src="../uploads/products/<?php echo $item['image']; ?>" width="60">
                            </td>
                            <td><?php echo $item['title']; ?></td>
                            <td>₹<?php echo $item['price']; ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>₹<?php echo $total; ?></td>
                            <td><a href="cart.php?remove_cart=<?php echo $id; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Remove this item from cart?')">
                                    ❌
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <h4 class="text-end">Grand Total: ₹<?php echo $grand_total; ?></h4>


            <div class="text-end mt-3">
                <a href="checkout.php" class="btn btn-success btn-lg">
                    Proceed to Checkout
                </a>
            </div>

        <?php } ?>
    </div>

</body>

</html>