<?php
session_start();
include('../includes/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>User Registration</h4>
                </div>

                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button name="register" class="btn btn-success w-100">
                            Register
                        </button>
                    </form>

                    <p class="text-center mt-3">
                        Already have an account?
                        <a href="login.php">Login</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
<?php
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email already registered')</script>";
    } else {
        $sql = "INSERT INTO users (name, email, password)
                VALUES ('$name', '$email', '$password')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Registration successful'); window.location='login.php';</script>";
        }
    }
}
?>
