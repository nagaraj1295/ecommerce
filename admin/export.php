<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
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

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_' . $period . '.csv"');

$output = fopen('php://output', 'w');

if ($type === 'orders') {
    fputcsv($output, ['Order ID', 'Customer', 'Total Amount', 'Status', 'Date']);

    $result = mysqli_query(
        $conn,
        "SELECT o.order_id, u.name AS user_name, o.total_amount, o.order_status, o.created_at
         FROM orders o
         LEFT JOIN users u ON o.user_id = u.user_id
         WHERE $dateCondition
         ORDER BY o.created_at DESC"
    );

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['order_id'],
                $row['user_name'] ?: 'Guest',
                $row['total_amount'],
                $row['order_status'],
                $row['created_at']
            ]);
        }
    }
} elseif ($type === 'products') {
    fputcsv($output, ['Product', 'Sold Quantity']);

    $result = mysqli_query(
        $conn,
        "SELECT p.title, SUM(oi.quantity) AS sold_qty
         FROM order_items oi
         JOIN products p ON oi.product_id = p.product_id
         JOIN orders o ON oi.order_id = o.order_id
         WHERE $dateCondition
         GROUP BY oi.product_id
         ORDER BY sold_qty DESC"
    );

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [$row['title'], $row['sold_qty']]);
        }
    }
} elseif ($type === 'revenue') {
    fputcsv($output, ['Date', 'Revenue']);

    $result = mysqli_query(
        $conn,
        "SELECT DATE(created_at) AS day, SUM(total_amount) AS total
         FROM orders
         WHERE $dateCondition
         GROUP BY DATE(created_at)
         ORDER BY day ASC"
    );

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [$row['day'], $row['total']]);
        }
    }
}

fclose($output);
exit();
?>