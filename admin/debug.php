<?php
// Include database config
include('C:/Users/nagar/Downloads/ecommerce/includes/db.php');

// Test database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "<h1>Database Debug</h1>";

// Test each table
$tables = ['products', 'users', 'orders', 'contact_messages'];

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "<p>$table: " . $row['count'] . " records</p>";
    } else {
        echo "<p>$table: Error - " . mysqli_error($conn) . "</p>";
    }
}

// Show sample data from each table
echo "<h2>Sample Data</h2>";

echo "<h3>Products (first 3)</h3>";
$result = mysqli_query($conn, "SELECT product_id, title, price FROM products LIMIT 3");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>ID: {$row['product_id']}, Title: {$row['title']}, Price: {$row['price']}</p>";
    }
} else {
    echo "<p>No products found or query failed</p>";
}

echo "<h3>Users (first 3)</h3>";
$result = mysqli_query($conn, "SELECT user_id, name, email FROM users LIMIT 3");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>ID: {$row['user_id']}, Name: {$row['name']}, Email: {$row['email']}</p>";
    }
} else {
    echo "<p>No users found or query failed</p>";
}

echo "<h3>Orders (first 3)</h3>";
$result = mysqli_query($conn, "SELECT order_id, user_id, total_amount FROM orders LIMIT 3");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>ID: {$row['order_id']}, User: {$row['user_id']}, Total: {$row['total_amount']}</p>";
    }
} else {
    echo "<p>No orders found or query failed</p>";
}

echo "<h3>Contact Messages (first 3)</h3>";
$result = mysqli_query($conn, "SELECT id, name, email, subject FROM contact_messages LIMIT 3");
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p>ID: {$row['id']}, Name: {$row['name']}, Email: {$row['email']}, Subject: {$row['subject']}</p>";
    }
} else {
    echo "<p>No messages found or query failed</p>";
}
?>