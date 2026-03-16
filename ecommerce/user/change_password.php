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
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f5f7fa; }
        .pass-card {
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

    <div class="card shadow pass-card p-4">

        <h4 class="text-center mb-4">🔐 Change Password</h4>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Current Password</label>
                <input type="password" name="current"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new"
                       class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm"
                       class="form-control" required>
            </div>

            <button name="change_password"
                    class="btn btn-success w-100">
                Update Password
            </button>

        </form>
    </div>
</div>

</body>
</html>

<?php
if (isset($_POST['change_password'])) {

    if (!password_verify($_POST['current'], $user['password'])) {
        echo "<script>alert('Current password is incorrect');</script>";
    }
    elseif ($_POST['new'] !== $_POST['confirm']) {
        echo "<script>alert('New passwords do not match');</script>";
    }
    else {
        $new_hash = password_hash($_POST['new'], PASSWORD_DEFAULT);

        mysqli_query($conn,
            "UPDATE users SET password='$new_hash' WHERE user_id='$uid'"
        );

        echo "<script>
                alert('Password updated successfully');
                window.location='index.php';
              </script>";
    }
}
?>
