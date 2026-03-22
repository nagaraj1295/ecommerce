<?php
session_start();
include('../includes/db.php');

/* ---------- AUTH ---------- */
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

/* ---------- FETCH ADMIN (ALWAYS FRESH) ---------- */
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

/* Sync session pic if missing */
if (!isset($_SESSION['admin_pic']) || $_SESSION['admin_pic'] !== $admin['profile_pic']) {
    $_SESSION['admin_pic'] = $admin['profile_pic'];
}


/* ---------- UPDATE PROFILE ---------- */
if (isset($_POST['update_profile'])) {

    $name = trim($_POST['name']);
    $updates = [];
    $params = [];
    $types = "";

    $updates[] = "name = ?";
    $params[] = $name;
    $types .= "s";

    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updates[] = "password = ?";
        $params[] = $hashed;
        $types .= "s";
    }

    if (
        isset($_FILES['admin_pic']) &&
        $_FILES['admin_pic']['error'] === UPLOAD_ERR_OK
    ) {

        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['admin_pic']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            die("Invalid image format");
        }

        $pic_name = time() . '_' . uniqid() . '.' . $ext;
        $upload_path = __DIR__ . "/assets/images/" . $pic_name;

        if (move_uploaded_file($_FILES['admin_pic']['tmp_name'], $upload_path)) {

            $updates[] = "profile_pic = ?";
            $params[] = $pic_name;
            $types .= "s";

            $_SESSION['admin_pic'] = $pic_name;
        } else {
            die("Image upload failed");
        }
    }


    $sql = "UPDATE admins SET " . implode(', ', $updates) . " WHERE admin_id = ?";
    $params[] = $admin_id;
    $types .= "i";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    $_SESSION['admin_name'] = $name;

    header("Location: profile.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .profile-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
        }

        .preview-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .header-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- SIDEBAR -->
            <div class="col-md-2 bg-dark min-vh-100">
                <h5 class="text-white text-center py-3">Admin Panel</h5>
                <a href="dashboard.php" class="d-block text-white p-2">Dashboard</a>
                <a href="profile.php" class="d-block text-white p-2 bg-success">My Profile</a>
                <a href="logout.php" class="d-block text-danger p-2">Logout</a>
            </div>

            <!-- MAIN -->
            <div class="col-md-10 p-4">

                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>My Profile</h4>
                    <div class="d-flex align-items-center gap-2">
                        <img src="assets/images/<?php echo htmlspecialchars($_SESSION['admin_pic']); ?>"
                            class="rounded-circle header-img">

                        <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
                    </div>
                </div>

                <?php if (isset($_GET['updated'])) { ?>
                    <div class="alert alert-success">Profile updated successfully</div>
                <?php } ?>

                <div class="row">

                    <!-- PROFILE CARD (OLD IMAGE ONLY) -->
                    <div class="col-md-4">
                        <div class="card profile-card shadow text-center p-4">
                            <img src="assets/images/<?php echo htmlspecialchars($_SESSION['admin_pic']); ?>"
                                class="rounded-circle mx-auto mb-3">
                            <h5><?php echo htmlspecialchars($admin['name']); ?></h5>
                            <p class="text-muted"><?php echo htmlspecialchars($admin['email']); ?></p>
                        </div>
                    </div>

                    <!-- FORM -->
                    <div class="col-md-6">
                        <div class="card shadow p-4">

                            <form method="POST" enctype="multipart/form-data">

                                <div class="mb-3">
                                    <label>Name</label>
                                    <input type="text" name="name"
                                        value="<?php echo htmlspecialchars($admin['name']); ?>"
                                        class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email"
                                        value="<?php echo htmlspecialchars($admin['email']); ?>"
                                        class="form-control" readonly>
                                </div>

                                <div class="mb-3">
                                    <label>New Password</label>
                                    <input type="password" name="new_password"
                                        class="form-control"
                                        placeholder="Leave blank to keep current password">
                                </div>

                                <!-- IMAGE PREVIEW (CHANGES LIVE) -->
                                <div class="mb-3">
                                    <label>Profile Picture</label>
                                    <input type="file" name="admin_pic"
                                        class="form-control"
                                        onchange="previewImage(this)">
                                </div>

                                <div class="mb-3">
                                    <small class="text-muted">Preview</small><br>
                                    <img id="formPreview"
                                        src="assets/images/<?php echo htmlspecialchars($_SESSION['admin_pic']); ?>"
                                        class="rounded-circle preview-img">

                                </div>

                                <button name="update_profile"
                                    class="btn btn-success w-100">
                                    Update Profile
                                </button>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- PREVIEW SCRIPT -->
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('formPreview').src = e.target.result;
                    document.getElementById('headerPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>

</html>