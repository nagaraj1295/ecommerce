<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Test database connection
if ($conn->connect_error) {
    // Show error message instead of dying
    $db_error = "Database connection failed: " . $conn->connect_error;
    $products = $users = $orders = $messages = 0;
} else {
    $db_error = null;
}

// AJAX request for data refresh
if (isset($_GET['ajax'])) {
    // Check database connection
    if ($conn->connect_error) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Database connection failed: ' . $conn->connect_error,
            'stats' => ['products' => 0, 'users' => 0, 'orders' => 0, 'messages' => 0],
            'recentOrders' => [],
            'topProducts' => [],
            'revenue' => ['labels' => ['Error'], 'data' => [0]]
        ]);
        exit();
    }
    $period = isset($_GET['period']) ? $_GET['period'] : '7days';
    $dateCondition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";

    switch ($period) {
        case '30days':
            $dateCondition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
            break;
        case 'month':
            $dateCondition = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
            break;
        case 'all':
            $dateCondition = "1=1";
            break;
    }

    // Set period label
    $periodLabel = 'Last 7 days';
    if ($period === '30days') $periodLabel = 'Last 30 days';
    elseif ($period === 'month') $periodLabel = 'This month';
    elseif ($period === 'all') $periodLabel = 'All time';


    // Fetch stats
    $products_query = mysqli_query($conn, "SELECT * FROM products");
    $products = $products_query ? mysqli_num_rows($products_query) : 0;

    $users_query = mysqli_query($conn, "SELECT * FROM users");
    $users = $users_query ? mysqli_num_rows($users_query) : 0;

    $orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE $dateCondition");
    $orders = $orders_query ? mysqli_num_rows($orders_query) : 0;

    $messages_query = mysqli_query($conn, "SELECT * FROM contact_messages");
    $messages = $messages_query ? mysqli_num_rows($messages_query) : 0;

    // Recent orders
    $recentOrdersData = [];
    $recentOrdersQuery = mysqli_query(
        $conn,
        "SELECT o.order_id, o.total_amount, o.order_status, o.created_at, u.name AS user_name
         FROM orders o
         LEFT JOIN users u ON o.user_id = u.user_id
         WHERE $dateCondition
         ORDER BY o.created_at DESC
         LIMIT 5"
    );

    if ($recentOrdersQuery) {
        while ($row = mysqli_fetch_assoc($recentOrdersQuery)) {
            $recentOrdersData[] = $row;
        }
    }

    // Top products
    $topProductsData = [];
    $topProductsQuery = mysqli_query(
        $conn,
        "SELECT p.title, SUM(oi.quantity) AS sold_qty
         FROM order_items oi
         JOIN products p ON oi.product_id = p.product_id
         JOIN orders o ON oi.order_id = o.order_id
         WHERE $dateCondition
         GROUP BY oi.product_id
         ORDER BY sold_qty DESC
         LIMIT 5"
    );

    if ($topProductsQuery) {
        while ($row = mysqli_fetch_assoc($topProductsQuery)) {
            $topProductsData[] = $row;
        }
    }

    // Revenue
    $revenueData = [];
    $revenueResult = mysqli_query(
        $conn,
        "SELECT DATE(created_at) AS day, SUM(total_amount) AS total
         FROM orders
         WHERE $dateCondition
         GROUP BY DATE(created_at)
         ORDER BY day ASC"
    );

    if ($revenueResult) {
        while ($row = mysqli_fetch_assoc($revenueResult)) {
            $revenueData[$row['day']] = (float) $row['total'];
        }
    }

    if ($period === '7days') {
        $revenueLabels = [];
        $revenueTotals = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $revenueLabels[] = date('D', strtotime($day));
            $revenueTotals[] = isset($revenueData[$day]) ? $revenueData[$day] : 0;
        }
    } else {
        // For other periods, show daily breakdown
        $revenueLabels = [];
        $revenueTotals = [];

        if ($period === '30days') {
            $days = 30;
        } elseif ($period === 'month') {
            $days = date('t'); // Days in current month
        } else { // 'all'
            // For all time, group by month instead of day
            $monthlyData = [];
            foreach ($revenueData as $date => $amount) {
                $monthKey = date('Y-m', strtotime($date));
                if (!isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey] = 0;
                }
                $monthlyData[$monthKey] += $amount;
            }

            // Sort by month
            ksort($monthlyData);

            // Take last 12 months or all if less
            $monthlyData = array_slice($monthlyData, -12, 12, true);

            foreach ($monthlyData as $month => $amount) {
                $revenueLabels[] = date('M Y', strtotime($month . '-01'));
                $revenueTotals[] = $amount;
            }
        }

        if ($period !== 'all') {
            for ($i = $days - 1; $i >= 0; $i--) {
                if ($period === 'month') {
                    $day = date('Y-m-d', strtotime("first day of this month +{$i} days"));
                } else {
                    $day = date('Y-m-d', strtotime("-{$i} days"));
                }
                $revenueLabels[] = date('M j', strtotime($day));
                $revenueTotals[] = isset($revenueData[$day]) ? $revenueData[$day] : 0;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'stats' => [
            'products' => $products,
            'users' => $users,
            'orders' => $orders,
            'messages' => $messages
        ],
        'recentOrders' => $recentOrdersData,
        'topProducts' => $topProductsData,
        'revenue' => [
            'labels' => $revenueLabels,
            'data' => $revenueTotals
        ]
    ]);
    exit();
}

// Ensure contact messages table exists (created when user submits contact form)
mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        email VARCHAR(191) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$period = isset($_GET['period']) ? $_GET['period'] : '7days';
$periodLabel = 'Last 7 days';
switch ($period) {
    case '30days':
        $periodLabel = 'Last 30 days';
        break;
    case 'month':
        $periodLabel = 'This month';
        break;
    case 'all':
        $periodLabel = 'All time';
        break;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
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

        .stat-title {
            font-size: 0.95rem;
            letter-spacing: 0.03em;
            color: var(--muted);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.02em;
        }

        .fade-in {
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .badge-new {
            background: rgba(61, 220, 151, 0.18);
            color: var(--accent);
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .badge-resolved {
            background: rgba(30, 143, 234, 0.18);
            color: var(--accent2);
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .table thead th {
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--muted);
        }

        .table tbody tr {
            background: rgba(255, 255, 255, 0.03);
        }

        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border: none;
            color: #0f172a;
        }

        .pagination .page-link {
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--muted);
            background: rgba(255, 255, 255, 0.06);
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

        .header-group {
            gap: 1.25rem;
        }

        .header-group h4 {
            letter-spacing: 0.05em;
        }

        .header-meta {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 0.65rem 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .header-meta img {
            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.22);
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

        body.light-mode .table tbody tr {
            background: rgba(15, 23, 42, 0.04);
        }

        body.light-mode .table tbody tr:hover {
            background: rgba(15, 23, 42, 0.07);
        }

        body.light-mode .btn-glow {
            box-shadow: 0 12px 24px rgba(61, 220, 151, 0.18);
        }

        body.light-mode .panel:hover {
            transform: translateY(-4px);
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
            <a href="insert_product.php"><i class="bi bi-plus-circle"></i> Insert Product</a>
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
                    <a href="insert_product.php" class="list-group-item list-group-item-action">Insert Product</a>
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
                    <h4 class="mb-0">Dashboard</h4>
                    <p class="text-muted small mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>.</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-light btn-sm theme-toggle" title="Toggle light/dark">
                        <i class="bi bi-moon-stars theme-icon"></i>
                    </button>

                    <div class="header-meta">
                        <img src="assets/images/<?php echo $_SESSION['admin_pic']; ?>" alt="Admin">
                        <div>
                            <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                            <div class="small text-muted">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INSIGHTS -->
            <div class="row g-2 mb-3 fade-in" style="animation-delay: 0.28s;">
                <!-- Filter Controls -->
                <div class="col-12">
                    <div class="panel p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Dashboard Insights</h6>
                            <small class="text-muted">Showing data for: <strong><?php echo $periodLabel; ?></strong></small>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <label for="periodSelect" class="form-label mb-0 small">Filter:</label>
                            <select id="periodSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>Last 7 days</option>
                                <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Last 30 days</option>
                                <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>This month</option>
                                <option value="all" <?php echo $period === 'all' ? 'selected' : ''; ?>>All time</option>
                            </select>
                            <button id="refreshBtn" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATS -->
            <div class="row text-center">

                <?php if ($db_error): ?>
                <div class="col-12 mb-4">
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Database Error:</strong> <?php echo htmlspecialchars($db_error); ?>
                        <br><small>Please check your database configuration in includes/db.php</small>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Add error checking for database queries
                $products_query = mysqli_query($conn, "SELECT * FROM products");
                $products = $products_query ? mysqli_num_rows($products_query) : 0;

                $users_query = mysqli_query($conn, "SELECT * FROM users");
                $users = $users_query ? mysqli_num_rows($users_query) : 0;

                $orders_query = mysqli_query($conn, "SELECT * FROM orders");
                $orders = $orders_query ? mysqli_num_rows($orders_query) : 0;

                $messages_query = mysqli_query($conn, "SELECT * FROM contact_messages");
                $messages = $messages_query ? mysqli_num_rows($messages_query) : 0;

                // Debug: Show any database errors
                if (!$products_query) {
                    echo "<!-- Products query error: " . mysqli_error($conn) . " -->";
                }
                if (!$users_query) {
                    echo "<!-- Users query error: " . mysqli_error($conn) . " -->";
                }
                if (!$orders_query) {
                    echo "<!-- Orders query error: " . mysqli_error($conn) . " -->";
                }
                if (!$messages_query) {
                    echo "<!-- Messages query error: " . mysqli_error($conn) . " -->";
                }
                ?>

                <div class="col-md-3 fade-in" style="animation-delay: 0.05s;">
                    <div class="panel p-4">
                        <a href="view_products.php" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-title">Total Products</div>
                                    <h3 class="stat-value" data-count="<?php echo $products; ?>">0</h3>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-new">Live</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 fade-in" style="animation-delay: 0.12s;">
                    <div class="panel p-4">
                        <a href="users.php" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-title">Total Users</div>
                                    <h3 class="stat-value" data-count="<?php echo $users; ?>">0</h3>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-new">Active</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 fade-in" style="animation-delay: 0.18s;">
                    <div class="panel p-4">
                        <a href="orders.php" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-title">Total Orders</div>
                                    <h3 class="stat-value" data-count="<?php echo $orders; ?>">0</h3>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-new">Recent</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="col-md-3 fade-in" style="animation-delay: 0.25s;">
                    <div class="panel p-4">
                        <a href="#messages" class="text-decoration-none">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-title">Messages</div>
                                    <h3 class="stat-value" data-count="<?php echo $messages; ?>">0</h3>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-new">New</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>

            <!-- INSIGHTS -->
            <?php
            $period = isset($_GET['period']) ? $_GET['period'] : '7days';
            $periodLabel = 'Last 7 days';
            $dateCondition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)";

            switch ($period) {
                case '30days':
                    $periodLabel = 'Last 30 days';
                    $dateCondition = "created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)";
                    break;
                case 'month':
                    $periodLabel = 'This month';
                    $dateCondition = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                    break;
                case 'all':
                    $periodLabel = 'All time';
                    $dateCondition = "1=1"; // No condition
                    break;
            }

            // Recent orders - try with fallback
            $recentOrdersQuery = "SELECT o.order_id, o.total_amount, o.order_status, o.created_at, u.name AS user_name
                                 FROM orders o
                                 LEFT JOIN users u ON o.user_id = u.user_id
                                 WHERE $dateCondition
                                 ORDER BY o.created_at DESC
                                 LIMIT 5";

            $recentOrders = mysqli_query($conn, $recentOrdersQuery);

            if (!$recentOrders) {
                error_log("Recent orders query failed: " . mysqli_error($conn) . " | Query: " . $recentOrdersQuery);
                // Fallback: try without date condition
                $recentOrders = mysqli_query($conn, "SELECT o.order_id, o.total_amount, o.order_status, o.created_at, u.name AS user_name
                                                    FROM orders o
                                                    LEFT JOIN users u ON o.user_id = u.user_id
                                                    ORDER BY o.created_at DESC
                                                    LIMIT 5");
            }

            // Top products by quantity sold - try with fallback
            $topProductsQuery = "SELECT p.title, SUM(oi.quantity) AS sold_qty
                               FROM order_items oi
                               JOIN products p ON oi.product_id = p.product_id
                               JOIN orders o ON oi.order_id = o.order_id
                               WHERE $dateCondition
                               GROUP BY oi.product_id
                               ORDER BY sold_qty DESC
                               LIMIT 5";

            $topProducts = mysqli_query($conn, $topProductsQuery);

            if (!$topProducts) {
                error_log("Top products query failed: " . mysqli_error($conn) . " | Query: " . $topProductsQuery);
                // Fallback: try without date condition
                $topProducts = mysqli_query($conn, "SELECT p.title, SUM(oi.quantity) AS sold_qty
                                                   FROM order_items oi
                                                   JOIN products p ON oi.product_id = p.product_id
                                                   JOIN orders o ON oi.order_id = o.order_id
                                                   GROUP BY oi.product_id
                                                   ORDER BY sold_qty DESC
                                                   LIMIT 5");
            }

            // Revenue for selected period
            $revenueData = [];
            $revenueResult = mysqli_query(
                $conn,
                "SELECT DATE(created_at) AS day, SUM(total_amount) AS total
                 FROM orders
                 WHERE $dateCondition
                 GROUP BY DATE(created_at)
                 ORDER BY day ASC"
            );

            if ($revenueResult) {
                while ($row = mysqli_fetch_assoc($revenueResult)) {
                    $revenueData[$row['day']] = (float) $row['total'];
                }
            }

            // Prepare revenue data for chart
            if ($period === '7days') {
                $revenueLabels = [];
                $revenueTotals = [];
                for ($i = 6; $i >= 0; $i--) {
                    $day = date('Y-m-d', strtotime("-{$i} days"));
                    $revenueLabels[] = date('D', strtotime($day));
                    $revenueTotals[] = isset($revenueData[$day]) ? $revenueData[$day] : 0;
                }
            } else {
                // For other periods, show daily breakdown
                $revenueLabels = [];
                $revenueTotals = [];

                if ($period === '30days') {
                    $days = 30;
                } elseif ($period === 'month') {
                    $days = date('t'); // Days in current month
                } else { // 'all'
                    // For all time, group by month instead of day
                    $monthlyData = [];
                    foreach ($revenueData as $date => $amount) {
                        $monthKey = date('Y-m', strtotime($date));
                        if (!isset($monthlyData[$monthKey])) {
                            $monthlyData[$monthKey] = 0;
                        }
                        $monthlyData[$monthKey] += $amount;
                    }

                    // Sort by month
                    ksort($monthlyData);

                    // Take last 12 months or all if less
                    $monthlyData = array_slice($monthlyData, -12, 12, true);

                    foreach ($monthlyData as $month => $amount) {
                        $revenueLabels[] = date('M Y', strtotime($month . '-01'));
                        $revenueTotals[] = $amount;
                    }
                }

                if ($period !== 'all') {
                    for ($i = $days - 1; $i >= 0; $i--) {
                        if ($period === 'month') {
                            $day = date('Y-m-d', strtotime("first day of this month +{$i} days"));
                        } else {
                            $day = date('Y-m-d', strtotime("-{$i} days"));
                        }
                        $revenueLabels[] = date('M j', strtotime($day));
                        $revenueTotals[] = isset($revenueData[$day]) ? $revenueData[$day] : 0;
                    }
                }
            }
            ?>

            <!-- CONTACT MESSAGES -->
            <div id="messages" class="mt-5 fade-in" style="animation-delay: 0.3s;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Latest Contact Messages</h5>
                        <p class="small text-muted mb-0">Recent submissions from your site contact form.</p>
                    </div>
                    <a href="contact_messages.php" class="btn btn-sm btn-glow">View All</a>
                </div>

                <?php
                $messageResult = mysqli_query(
                    $conn,
                    "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 8"
                );

                if (mysqli_num_rows($messageResult) == 0) {
                    echo "<div class='alert alert-info'>No messages yet.</div>";
                } else {
                    echo "<div class='table-responsive'>";
                    echo "<table class='table table-bordered align-middle'>";
                    echo "<thead class='table-light'><tr><th>#</th><th>Name</th><th>Email</th><th>Subject</th><th>Received</th></tr></thead>";
                    echo "<tbody>";

                    $idx = 1;
                    while ($msg = mysqli_fetch_assoc($messageResult)) {
                        $received = date('Y-m-d H:i', strtotime($msg['created_at']));
                        echo "<tr>";
                        echo "<td>{$idx}</td>";
                        echo "<td>" . htmlspecialchars($msg['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($msg['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($msg['subject']) . "</td>";
                        echo "<td>{$received}</td>";
                        echo "</tr>";
                        $idx++;
                    }

                    echo "</tbody></table></div>";
                }
                ?>
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

    // Animated counter for the stat cards
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.stat-value[data-count]');

        counters.forEach((counter) => {
            const target = parseInt(counter.dataset.count, 10) || 0;
            let current = 0;
            const step = Math.max(1, Math.floor(target / 35));

            const tick = () => {
                current += step;
                if (current > target) current = target;
                counter.textContent = current;

                if (current < target) {
                    requestAnimationFrame(tick);
                }
            };

            tick();
        });

        // Filter change
        document.getElementById('periodSelect').addEventListener('change', (e) => {
            const period = e.target.value;
            window.location.href = `dashboard.php?period=${period}`;
        });

        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', () => {
            const period = document.getElementById('periodSelect').value;
            fetch(`dashboard.php?ajax=1&period=${period}`)
                .then(response => response.json())
                .then(data => {
                    // Update stats
                    updateStat('products', data.stats.products);
                    updateStat('users', data.stats.users);
                    updateStat('orders', data.stats.orders);
                    updateStat('messages', data.stats.messages);

                    // Update recent orders
                    updateRecentOrders(data.recentOrders);

                    // Update top products
                    updateTopProducts(data.topProducts);

                    // Update revenue table
                    updateRevenueTable(data.revenue.labels, data.revenue.data);

                    // Show success feedback
                    showRefreshFeedback();
                })
                .catch(error => console.error('Refresh failed:', error));
        });
    });

    function updateStat(type, value) {
        const statValues = document.querySelectorAll('.stat-value[data-count]');
        let index = -1;
        if (type === 'products') index = 0;
        else if (type === 'users') index = 1;
        else if (type === 'orders') index = 2;
        else if (type === 'messages') index = 3;
        if (index !== -1 && statValues[index]) {
            statValues[index].dataset.count = value;
            statValues[index].textContent = value;
        }
    }

    function updateRecentOrders(orders) {
        const container = document.querySelector('.col-lg-4 .list-group');
        if (!container) return;

        container.innerHTML = '';
        if (orders.length === 0) {
            container.innerHTML = '<div class="text-muted">No recent orders.</div>';
            return;
        }

        orders.forEach(order => {
            const li = document.createElement('li');
            li.className = 'list-group-item bg-transparent border-0 px-0 py-2';
            li.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>#${order.order_id}</strong>
                        <div class="small text-muted">${order.user_name || 'Guest'}</div>
                    </div>
                    <span class="badge bg-secondary">${order.order_status}</span>
                </div>
                <div class="small text-muted">₹${parseFloat(order.total_amount).toFixed(2)} • ${new Date(order.created_at).toLocaleDateString()}</div>
            `;
            container.appendChild(li);
        });
    }

    function updateTopProducts(products) {
        const container = document.querySelector('.col-lg-4 .list-group-numbered');
        if (!container) return;

        container.innerHTML = '';
        if (products.length === 0) {
            container.innerHTML = '<div class="text-muted">No product sales yet.</div>';
            return;
        }

        products.forEach(product => {
            const li = document.createElement('li');
            li.className = 'list-group-item bg-transparent border-0 px-0 py-2';
            li.innerHTML = `
                <div class="d-flex justify-content-between">
                    <span>${product.title}</span>
                    <span class="text-muted">${product.sold_qty} sold</span>
                </div>
            `;
            container.appendChild(li);
        });
    }

    function updateRevenueTable(labels, data) {
        try {
            const container = document.querySelector('.col-lg-4 .table-responsive');
            if (!container) return;

            const tbody = container.querySelector('tbody');
            if (!tbody) return;

            // Clear existing rows except the total row
            const rows = tbody.querySelectorAll('tr');
            for (let i = 0; i < rows.length - 1; i++) {
                rows[i].remove();
            }

            if (data.length === 0 || data.every(val => val == 0)) {
                container.innerHTML = '<div class="text-muted text-center py-4">No revenue data available.</div>';
                return;
            }

            // Add new data rows
            let totalRevenue = 0;
            for (let i = 0; i < labels.length; i++) {
                totalRevenue += parseFloat(data[i]) || 0;
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="small">${labels[i]}</td>
                    <td class="text-end small">₹${(parseFloat(data[i]) || 0).toFixed(2)}</td>
                `;
                tbody.insertBefore(row, tbody.lastElementChild);
            }

            // Update total row
            const totalRow = tbody.lastElementChild;
            if (totalRow) {
                totalRow.querySelector('td:last-child strong').textContent = `₹${totalRevenue.toFixed(2)}`;
            }

        } catch (error) {
            console.error('Error updating revenue table:', error);
        }
    }

    function showRefreshFeedback() {
        const btn = document.getElementById('refreshBtn');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Refreshed';
        btn.classList.add('btn-success');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('btn-success');
        }, 2000);
    }
</script>
</body>
</html>
