<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

/*
Database Schema Requirements:
- products table should have: slug VARCHAR(255), created_at DATETIME
- product_images table should have: is_main TINYINT(1) DEFAULT 0
- Ensure uploads/products/ directory exists and is writable
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Insert Product</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0d1117;
            --panel: rgba(255, 255, 255, 0.08);
            --panel-border: rgba(255, 255, 255, 0.15);
            --text: #f2f4f8;
            --muted: rgba(242, 244, 248, 0.65);
            --accent: #3ddc97;
            --accent2: #1e8fea;
        }

        body {
            background: radial-gradient(circle at top, rgba(61, 220, 151, 0.14), transparent 55%),
                radial-gradient(circle at bottom, rgba(30, 143, 234, 0.14), transparent 55%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .sidebar {
            height: 100vh;
            background: rgba(15, 23, 42, 0.95);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar h5 {
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        .sidebar a {
            color: var(--muted);
            text-decoration: none;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .sidebar a .bi {
            font-size: 1.1rem;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(61, 220, 151, 0.12);
            color: var(--text);
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            border-radius: 18px;
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.24);
            backdrop-filter: blur(14px);
            transition: transform 0.2s ease;
        }

        .panel:hover {
            transform: translateY(-4px);
        }

        .fade-in {
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-glow {
            background: linear-gradient(135deg, rgba(61, 220, 151, 0.9), rgba(30, 143, 234, 0.9));
            color: #0f172a;
            border: none;
            box-shadow: 0 12px 24px rgba(61, 220, 151, 0.25);
        }

        .btn-glow:hover {
            box-shadow: 0 20px 36px rgba(61, 220, 151, 0.35);
        }

        /* Enhanced Form Styling */
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: var(--text);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(61, 220, 151, 0.25);
            color: var(--text);
        }

        .form-control::placeholder {
            color: var(--muted);
        }

        .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: var(--text);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(61, 220, 151, 0.25);
        }

        .form-label {
            color: var(--text);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .file-input-label:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
        }

        .file-input-label.dragover {
            background: rgba(61, 220, 151, 0.1);
            border-color: var(--accent);
        }

        .file-preview-item {
            display: inline-block;
            margin: 0.25rem;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .file-preview-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 0.5rem;
        }

        .file-preview-item .file-info {
            display: inline-block;
            vertical-align: top;
        }

        .file-preview-item .file-name {
            font-size: 0.85rem;
            color: var(--text);
            display: block;
            margin-bottom: 0.25rem;
        }

        .file-preview-item .file-size {
            font-size: 0.75rem;
            color: var(--muted);
        }

        /* LIGHT MODE */
        body.light-mode {
            --bg: #f8f9ff;
            --panel: rgba(255, 255, 255, 0.92);
            --panel-border: rgba(15, 23, 42, 0.08);
            --text: #07101c;
            --muted: rgba(7, 16, 28, 0.6);
        }

        body.light-mode .sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-right-color: rgba(15, 23, 42, 0.1);
        }

        body.light-mode .sidebar a {
            color: rgba(15, 23, 42, 0.7);
        }

        body.light-mode .sidebar a:hover,
        body.light-mode .sidebar a.active {
            background: rgba(61, 220, 151, 0.14);
            color: rgba(15, 23, 42, 1);
        }

        body.light-mode .panel:hover {
            transform: translateY(-4px);
        }

        body.light-mode .form-control {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(15, 23, 42, 0.15);
            color: #07101c;
        }

        body.light-mode .form-control:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--accent);
        }

        body.light-mode .form-select {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(15, 23, 42, 0.15);
            color: #07101c;
        }

        body.light-mode .form-select:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--accent);
        }

        body.light-mode .file-input-label {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(15, 23, 42, 0.15);
            color: rgba(7, 16, 28, 0.6);
        }

        body.light-mode .file-input-label:hover {
            background: rgba(255, 255, 255, 1);
            border-color: var(--accent);
        }

        body.light-mode .file-preview-item {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(15, 23, 42, 0.1);
        }

        /* Theme toggle button visibility */
        .theme-toggle {
            border-color: var(--text) !important;
            color: var(--text) !important;
        }

        .theme-toggle:hover {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #0f172a !important;
        }

        body.light-mode .theme-toggle {
            border-color: var(--text) !important;
            color: var(--text) !important;
        }

        /* Mobile menu button visibility */
        .btn-outline-light {
            border-color: var(--text) !important;
            color: var(--text) !important;
        }

        .btn-outline-light:hover {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: #0f172a !important;
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0 d-none d-md-block">
            <h5 class="text-white text-center py-3">Admin Panel</h5>

            <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="insert_product.php" class="active"><i class="bi bi-plus-circle"></i> Insert Product</a>
            <a href="view_products.php"><i class="bi bi-box-seam"></i> View Products</a>
            <a href="users.php"><i class="bi bi-people"></i> List Users</a>
            <a href="orders.php"><i class="bi bi-receipt"></i> Order Details</a>
            <a href="payments.php"><i class="bi bi-credit-card"></i> Payment Details</a>
            <a href="profile.php"><i class="bi bi-person-circle"></i> My Profile</a>
            <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>

        <!-- MOBILE MENU BUTTON -->
        <div class="d-flex d-md-none w-100 p-3 justify-content-between align-items-center bg-dark">
            <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <i class="bi bi-list"></i>
            </button>

            <div class="d-flex align-items-center gap-2">
                <span class="text-white small">Admin Panel</span>
                <button class="btn btn-sm btn-outline-light theme-toggle" title="Toggle light/dark">
                    <i class="bi bi-moon-stars theme-icon"></i>
                </button>
            </div>
        </div>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <div class="list-group list-group-flush">
                    <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="insert_product.php" class="list-group-item list-group-item-action active">Insert Product</a>
                    <a href="view_products.php" class="list-group-item list-group-item-action">View Products</a>
                    <a href="users.php" class="list-group-item list-group-item-action">List Users</a>
                    <a href="orders.php" class="list-group-item list-group-item-action">Order Details</a>
                    <a href="payments.php" class="list-group-item list-group-item-action">Payment Details</a>
                    <a href="profile.php" class="list-group-item list-group-item-action">My Profile</a>
                    <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 header-group fade-in">
                <div>
                    <h4 class="mb-0">Insert New Product</h4>
                    <p class="text-muted small mb-0">Add a new product to your store.</p>
                </div>
            </div>

            <div class="fade-in" style="animation-delay: 0.2s;">
                <form method="POST" enctype="multipart/form-data" class="panel p-4">

                    <div class="row">
                        <div class="col-md-6">
                            <!-- PRODUCT TITLE -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-tag"></i> Product Title
                                </label>
                                <input type="text" name="title" id="productTitle" class="form-control" placeholder="Enter product title" required>
                                <small id="slugPreview" class="text-muted mt-1" style="display: none;"></small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- CATEGORY -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-folder"></i> Category
                                </label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $cats = mysqli_query($conn, "SELECT * FROM categories");
                                    while ($cat = mysqli_fetch_assoc($cats)) {
                                    ?>
                                        <option value="<?php echo $cat['category_id']; ?>">
                                            <?php echo htmlspecialchars($cat['category_title']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- PRICE -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-currency-rupee"></i> Price (₹)
                                </label>
                                <input type="number" name="price" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- DELIVERY -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-truck"></i> Delivery Days
                                </label>
                                <input type="text" name="delivery_days" class="form-control" placeholder="e.g. 2-3 days" required>
                            </div>
                        </div>
                    </div>

                    <!-- SHORT DESC -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-chat-dots"></i> Short Description
                        </label>
                        <input type="text" name="short_desc" class="form-control" placeholder="Brief description of the product" required>
                    </div>

                    <!-- LONG DESC -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-file-text"></i> Long Description
                        </label>
                        <textarea name="long_desc" class="form-control" rows="4" placeholder="Detailed description of the product" required></textarea>
                    </div>

                    <!-- IMAGE -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-images"></i> Product Images
                        </label>
                        <input type="file" name="images[]" multiple accept="image/*" class="form-control" required>
                        <small class="text-muted mt-1 d-block">Upload multiple images (PNG, JPG, AVIF). First image will be the main image.</small>
                        <div id="filePreview" class="mt-2"></div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="d-flex justify-content-center mt-4">
                        <button name="insert_product" class="btn btn-glow btn-lg">
                            <i class="bi bi-plus-circle"></i> Insert Product
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const themeToggles = document.querySelectorAll('.theme-toggle');
    const themeIcons = document.querySelectorAll('.theme-icon');

    const applyTheme = (dark) => {
        document.body.classList.toggle('light-mode', !dark);
        themeIcons.forEach((icon) => {
            icon.className = dark ? 'bi bi-moon-stars theme-icon' : 'bi bi-sun-fill theme-icon';
        });
    };

    const savedTheme = localStorage.getItem('adminTheme');
    const darkMode = savedTheme ? savedTheme === 'dark' : true;
    applyTheme(darkMode);

    themeToggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const isDarkNow = !document.body.classList.contains('light-mode');
            const nextDark = !isDarkNow;
            applyTheme(nextDark);
            localStorage.setItem('adminTheme', nextDark ? 'dark' : 'light');
        });
    });

    // Enhanced file upload functionality
    const fileInput = document.querySelector('input[name="images[]"]');
    const filePreview = document.getElementById('filePreview');

    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect(event) {
        const files = event.target.files;
        displayFilePreview(files);
    }

    function displayFilePreview(files) {
        filePreview.innerHTML = '';
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const fileItem = document.createElement('div');
                fileItem.className = 'file-preview-item';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        fileItem.innerHTML = `
                            <img src="${e.target.result}" alt="${file.name}">
                            <div class="file-info">
                                <div class="file-name">${file.name}</div>
                                <div class="file-size">${(file.size / 1024).toFixed(1)} KB</div>
                            </div>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileItem.innerHTML = `
                        <i class="bi bi-file-earmark-image" style="font-size: 2rem; margin-right: 0.5rem;"></i>
                        <div class="file-info">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${(file.size / 1024).toFixed(1)} KB</div>
                        </div>
                    `;
                }

                filePreview.appendChild(fileItem);
            }
        }
    }

    // Form validation enhancement
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            event.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Add visual feedback for required fields
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });

    // Slug preview functionality
    const titleInput = document.getElementById('productTitle');
    const slugPreview = document.getElementById('slugPreview');

    function generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    titleInput.addEventListener('input', function() {
        const title = this.value.trim();
        if (title.length > 0) {
            const slug = generateSlug(title);
            slugPreview.textContent = 'URL Slug: ' + (slug || 'invalid-title');
            slugPreview.style.display = 'block';
        } else {
            slugPreview.style.display = 'none';
        }
    });

    // Price formatting
    const priceInput = document.querySelector('input[name="price"]');
    priceInput.addEventListener('blur', function() {
        const value = parseFloat(this.value);
        if (!isNaN(value) && value >= 0) {
            this.value = value.toFixed(2);
        }
    });
</script>
</body>

</html>
<?php
if (isset($_POST['insert_product'])) {
    // Function to sanitize input
    function sanitize_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Function to generate URL slug
    function generate_slug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }

    // Function to validate image file
    function validate_image($file) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            return "Invalid file type. Only JPG, PNG, GIF, WEBP, and AVIF are allowed.";
        }

        if ($file['size'] > $max_size) {
            return "File size too large. Maximum 5MB allowed.";
        }

        return true;
    }

    // Sanitize and validate inputs
    $title = sanitize_input($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $short_desc = sanitize_input($_POST['short_desc']);
    $long_desc = sanitize_input($_POST['long_desc']);
    $delivery_days = sanitize_input($_POST['delivery_days']);

    $errors = [];
    $success = false;

    // Validation
    if (empty($title) || strlen($title) < 3) {
        $errors[] = "Product title must be at least 3 characters long.";
    }

    if ($category_id <= 0) {
        $errors[] = "Please select a valid category.";
    }

    if ($price <= 0) {
        $errors[] = "Price must be greater than 0.";
    }

    if (empty($short_desc)) {
        $errors[] = "Short description is required.";
    }

    if (empty($long_desc)) {
        $errors[] = "Long description is required.";
    }

    if (empty($delivery_days)) {
        $errors[] = "Delivery days information is required.";
    }

    // Check if category exists
    $category_check = mysqli_query($conn, "SELECT category_id FROM categories WHERE category_id = $category_id");
    if (mysqli_num_rows($category_check) == 0) {
        $errors[] = "Selected category does not exist.";
    }

    // Check for duplicate product title
    $duplicate_check = mysqli_query($conn, "SELECT product_id FROM products WHERE title = '" . mysqli_real_escape_string($conn, $title) . "'");
    if (mysqli_num_rows($duplicate_check) > 0) {
        $errors[] = "A product with this title already exists.";
    }

    // Validate uploaded files
    if (empty($_FILES['images']['name'][0])) {
        $errors[] = "At least one product image is required.";
    } else {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_file) {
            if (!empty($tmp_file)) {
                $file_info = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];

                $validation = validate_image($file_info);
                if ($validation !== true) {
                    $errors[] = "Image " . ($key + 1) . ": " . $validation;
                }
            }
        }
    }

    // If no errors, proceed with insertion
    if (empty($errors)) {
        // Generate slug
        $slug = generate_slug($title);

        // Ensure unique slug
        $original_slug = $slug;
        $counter = 1;
        while (mysqli_num_rows(mysqli_query($conn, "SELECT product_id FROM products WHERE slug = '$slug'")) > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Get main image
            $main_image = $_FILES['images']['name'][0];
            $tmp_main_image = $_FILES['images']['tmp_name'][0];

            // Generate unique filename to prevent overwrites
            $image_extension = pathinfo($main_image, PATHINFO_EXTENSION);
            $unique_filename = uniqid('product_') . '.' . $image_extension;

            // Move main image
            if (!move_uploaded_file($tmp_main_image, "../uploads/products/$unique_filename")) {
                throw new Exception("Failed to upload main image.");
            }

            // Insert product
            $sql = "INSERT INTO products 
                (category_id, title, slug, short_desc, long_desc, price, image, delivery_days, created_at)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isssssss", $category_id, $title, $slug, $short_desc, $long_desc, $price, $unique_filename, $delivery_days);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to insert product: " . mysqli_error($conn));
            }

            $pid = mysqli_insert_id($conn);

            // Insert additional images
            $image_insert_sql = "INSERT INTO product_images (product_id, image, is_main) VALUES (?, ?, ?)";
            $image_stmt = mysqli_prepare($conn, $image_insert_sql);

            foreach ($_FILES['images']['name'] as $key => $img) {
                if ($key == 0) continue; // Skip main image, already handled

                $tmp_img = $_FILES['images']['tmp_name'][$key];
                if (!empty($img) && !empty($tmp_img)) {
                    $img_extension = pathinfo($img, PATHINFO_EXTENSION);
                    $unique_img_filename = uniqid('product_' . $pid . '_') . '.' . $img_extension;

                    if (move_uploaded_file($tmp_img, "../uploads/products/$unique_img_filename")) {
                        mysqli_stmt_bind_param($image_stmt, "iss", $pid, $unique_img_filename, 0);
                        if (!mysqli_stmt_execute($image_stmt)) {
                            error_log("Failed to insert additional image: " . mysqli_error($conn));
                            // Continue with other images
                        }
                    }
                }
            }

            // Insert main image reference
            mysqli_stmt_bind_param($image_stmt, "iss", $pid, $unique_filename, 1);
            mysqli_stmt_execute($image_stmt);

            // Commit transaction
            mysqli_commit($conn);
            $success = true;

        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // Handle response
    if ($success) {
        echo "<script>
            alert('Product inserted successfully!');
            window.location.href='view_products.php';
        </script>";
        exit();
    } else {
        $error_message = implode("\\n", $errors);
        echo "<script>alert('Errors occurred:\\n$error_message');</script>";
    }
}
?>