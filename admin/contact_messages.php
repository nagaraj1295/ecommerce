<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure messages table exists & has status column
mysqli_query(
    $conn,
    "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(191) NOT NULL,
        email VARCHAR(191) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(32) NOT NULL DEFAULT 'New',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

mysqli_query(
    $conn,
    "ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS status VARCHAR(32) NOT NULL DEFAULT 'New'"
);

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'delete') {
        mysqli_query($conn, "DELETE FROM contact_messages WHERE id='$id'");
    }

    if ($_GET['action'] === 'resolve') {
        mysqli_query($conn, "UPDATE contact_messages SET status='Resolved' WHERE id='$id'");
    }

    header('Location: contact_messages.php');
    exit();
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$totalMessagesResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages");
$totalMessagesRow = mysqli_fetch_assoc($totalMessagesResult);
$totalMessages = (int) $totalMessagesRow['total'];
$totalPages = (int) ceil($totalMessages / $perPage);

// Fetch paginated messages
$messages = mysqli_query(
    $conn,
    "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT $perPage OFFSET $offset"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Contact Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 bg-dark text-white" style="height:100vh;">
            <h5 class="text-center py-3">Admin Panel</h5>
            <a href="dashboard.php" class="d-block text-white px-3 py-2">Dashboard</a>
            <a href="insert_product.php" class="d-block text-white px-3 py-2">Insert Product</a>
            <a href="view_products.php" class="d-block text-white px-3 py-2">View Products</a>
            <a href="users.php" class="d-block text-white px-3 py-2">List Users</a>
            <a href="orders.php" class="d-block text-white px-3 py-2">Order Details</a>
            <a href="payments.php" class="d-block text-white px-3 py-2">Payment Details</a>
            <a href="profile.php" class="d-block text-white px-3 py-2">My Profile</a>
            <a href="logout.php" class="d-block text-danger px-3 py-2">Logout</a>
        </div>

        <!-- MAIN -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Contact Messages</h4>
                <a href="dashboard.php" class="btn btn-outline-secondary">← Back to Dashboard</a>
            </div>

            <?php if (mysqli_num_rows($messages) === 0) : ?>
                <div class="alert alert-info">No contact messages yet.</div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Received</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $idx = 1; ?>
                            <?php while ($msg = mysqli_fetch_assoc($messages)) : ?>
                                <tr>
                                    <td><?php echo $idx++; ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                    <td>
                                        <?php if ($msg['status'] === 'Resolved') : ?>
                                            <span class="badge bg-success">Resolved</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                                    <td class="text-center">
                                        <a href="mailto:<?php echo urlencode($msg['email']); ?>?subject=<?php echo urlencode('Re: ' . $msg['subject']); ?>" class="btn btn-sm btn-primary mb-1">Reply</a>
                                        <?php if ($msg['status'] !== 'Resolved') : ?>
                                            <a href="contact_messages.php?action=resolve&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-success mb-1">Mark Resolved</a>
                                        <?php endif; ?>
                                        <a href="contact_messages.php?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
                                    </td>
                                </tr>
                                    <td colspan="7">
                                        <strong>Message:</strong>
                                        <div class="border p-2 mt-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1) : ?>
                    <nav aria-label="Contact messages pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="contact_messages.php?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>
        </div>

    </div>
</div>

</body>

</html>
