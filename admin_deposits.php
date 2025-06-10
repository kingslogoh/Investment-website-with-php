<?php
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search_query = "";
$params = [];

// Build search condition
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
    $search_sql = "WHERE t.transaction_id LIKE ?";
    $params[] = '%' . $search_query . '%';
} else {
    $search_sql = "";
}

// Total results for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t JOIN users u ON t.user_id = u.id $search_sql");
$count_stmt->execute($params);
$total_results = $count_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

// Fetch deposit records
$query = "
    SELECT t.id, t.user_id, u.name, t.transaction_id, t.amount, t.currency, t.status, t.created_at
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    $search_sql
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'];
    $new_status = $_POST['status'];

    $update = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
    $update->execute([$new_status, $id]);

    header("Location: admin_deposits.php?updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - View Deposits</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .container { margin-top: 40px; }
        .status-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .search-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .pagination a {
            margin: 0 2px;
            padding: 8px 12px;
            border-radius: 4px;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?> Admin
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">All Deposits</h2>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Status updated successfully.</div>
    <?php endif; ?>

    <!-- Search Bar -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" class="form-control" placeholder="Search by Transaction ID" value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <?php if (!empty($search_query)): ?>
            <a href="admin_deposits.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($deposits) === 0): ?>
                    <tr><td colspan="7" class="text-center">No results found.</td></tr>
                <?php else: ?>
                    <?php foreach ($deposits as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['transaction_id']) ?></td>
                            <td><?= number_format($row['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($row['currency']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $row['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                                        <option value="declined" <?= $row['status'] === 'declined' ? 'selected' : '' ?>>Declined</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation example" class="mt-4">
            <ul class="pagination justify-content-center flex-wrap">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Prev</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next &raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
