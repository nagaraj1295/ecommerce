<?php
// Include database config
include('C:/Users/nagar/Downloads/ecommerce/includes/db.php');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Setup</h1>";

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS ecommerce";
if ($conn->query($sql) === TRUE) {
    echo "<p>Database 'ecommerce' created or already exists.</p>";
} else {
    echo "<p>Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db("ecommerce");

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255),
        category VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total_amount DECIMAL(10,2) NOT NULL,
        order_status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )",

    "CREATE TABLE IF NOT EXISTS order_items (
        item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id)
    )",

    "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        pic VARCHAR(255) DEFAULT 'default.jpg',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table_sql) {
    if ($conn->query($table_sql) === TRUE) {
        echo "<p>Table created successfully.</p>";
    } else {
        echo "<p>Error creating table: " . $conn->error . "</p>";
    }
}

// Insert sample data
echo "<h2>Inserting Sample Data</h2>";

// Sample admin
$admin_sql = "INSERT IGNORE INTO admins (name, email, password, pic) VALUES
    ('Admin User', 'admin@example.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'default.jpg')";

if ($conn->query($admin_sql) === TRUE) {
    echo "<p>Sample admin inserted.</p>";
}

// Sample products
$products_sql = "INSERT IGNORE INTO products (title, description, price, category) VALUES
    ('Apple', 'Fresh red apple', 2.50, 'Fruits'),
    ('Banana', 'Organic banana', 1.20, 'Fruits'),
    ('Orange', 'Juicy orange', 1.80, 'Fruits'),
    ('Carrot', 'Fresh carrot', 0.80, 'Vegetables'),
    ('Broccoli', 'Green broccoli', 2.20, 'Vegetables')";

if ($conn->query($products_sql) === TRUE) {
    echo "<p>Sample products inserted.</p>";
}

// Sample users
$users_sql = "INSERT IGNORE INTO users (name, email, password) VALUES
    ('John Doe', 'john@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "'),
    ('Jane Smith', 'jane@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "')";

if ($conn->query($users_sql) === TRUE) {
    echo "<p>Sample users inserted.</p>";
}

// Sample orders
$orders_sql = "INSERT IGNORE INTO orders (user_id, total_amount, order_status) VALUES
    (1, 15.50, 'completed'),
    (2, 8.70, 'pending'),
    (1, 12.30, 'shipped')";

if ($conn->query($orders_sql) === TRUE) {
    echo "<p>Sample orders inserted.</p>";
}

// Sample contact messages
$messages_sql = "INSERT IGNORE INTO contact_messages (name, email, subject, message) VALUES
    ('Customer Support', 'support@example.com', 'General Inquiry', 'I have a question about my order.'),
    ('Feedback', 'feedback@example.com', 'Website Feedback', 'Great website design!')";

if ($conn->query($messages_sql) === TRUE) {
    echo "<p>Sample contact messages inserted.</p>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p>You can now access the <a href='dashboard.php'>admin dashboard</a>.</p>";
echo "<p>Default admin login: admin@example.com / admin123</p>";

$conn->close();
?>