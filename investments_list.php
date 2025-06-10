<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Pagination logic
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total = $pdo->query("SELECT COUNT(*) FROM investments")->fetchColumn();
$total_pages = ceil($total / $limit);

// Fetch investments
$stmt = $pdo->prepare("SELECT * FROM investments ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investments List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="admin.php">
            <img src="<?= htmlspecialchars($site_logo); ?>" alt="Logo" width="40" height="40" class="me-2">
            <span class="fw-bold"><?= htmlspecialchars($site_name); ?> Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- Navbar End -->

<div class="container my-5">
    <h2 class="mb-4 text-center text-md-start">Investments List</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>User ID</th>
                    <th>Amount</th>
                    <th>Plan ID</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>Maturity Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($investments as $index => $investment): ?>
                    <tr class="text-center">
                        <td><?= $offset + $index + 1; ?></td>
                        <td><?= htmlspecialchars($investment['user_id']); ?></td>
                        <td>$<?= number_format($investment['amount'], 2); ?></td>
                        <td><?= htmlspecialchars($investment['plan_id']); ?></td>
                        <td>
                            <?php if ($investment['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif ($investment['status'] == 'completed'): ?>
                                <span class="badge bg-primary">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($investment['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= date("Y-m-d", strtotime($investment['created_at'])); ?></td>
                        <td><?= date("Y-m-d", strtotime($investment['maturity_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav aria-label="Investment pagination">
        <ul class="pagination justify-content-center flex-wrap">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
