<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE user_id='$uid'")
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
        }

        .profile-card {
            max-width: 420px;
            margin: auto;
        }
    </style>
</head>

<body>

    <div class="container my-5">

        <!-- BACK BUTTON -->
        <a href="index.php" class="btn btn-outline-success mb-3">
            ← Back to Home
        </a>

        <div class="card shadow profile-card p-4">

            <div class="text-center mb-3">
                <div class="rounded-circle bg-success text-white d-inline-flex
                        align-items-center justify-content-center"
                    style="width:90px; height:90px; font-size:32px;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h4 class="mt-2"><?php echo $user['name']; ?></h4>
                <p class="text-muted"><?php echo $user['email']; ?></p>
            </div>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name"
                        class="form-control"
                        value="<?php echo $user['name']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                        class="form-control"
                        value="<?php echo $user['email']; ?>" readonly>
                </div>

                <button name="update_profile"
                    class="btn btn-success w-100">
                    Update Profile
                </button>

            </form>
        </div>
    </div>

</body>

</html>

<?php
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];

    mysqli_query(
        $conn,
        "UPDATE users SET name='$name' WHERE user_id='$uid'"
    );

    $_SESSION['user_name'] = $name;

    echo "<script>
            alert('Profile updated successfully');
            window.location='index.php';
          </script>";
}
?>