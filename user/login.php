<?php
session_start();
include('../includes/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>User Login</h4>
                </div>

                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button name="login" class="btn btn-success w-100">
                            Login
                        </button>
                    </form>

                    <p class="text-center mt-3">
                        Don’t have an account?
                        <a href="register.php">Register</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Invalid login details')</script>";
    }
}
?>
