<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'database.php';
include 'email_functions.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch site name and logo
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Approve
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("SELECT users.email, withdrawals.amount FROM withdrawals JOIN users ON withdrawals.user_id = users.id WHERE withdrawals.id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    sendEmail($data['email'], "Withdrawal Approved", "
        <p>Your withdrawal of <strong>\${$data['amount']}</strong> has been approved.</p>
    ");
}

// Pagination Setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT COUNT(*) FROM withdrawals");
$total_withdrawals = $total_stmt->fetchColumn();
$total_pages = ceil($total_withdrawals / $limit);

// Fetch paginated data
$stmt = $pdo->prepare("SELECT w.*, u.name, u.email FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.requested_at DESC LIMIT :offset, :limit");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Withdrawals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?> Admin
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container py-5">
    <h2 class="mb-4">Withdrawal Requests</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($withdrawals) > 0): ?>
                <?php foreach ($withdrawals as $w): ?>
                    <tr>
                        <td><?= htmlspecialchars($w['name']) ?></td>
                        <td><?= htmlspecialchars($w['email']) ?></td>
                        <td>$<?= htmlspecialchars($w['amount']) ?></td>
                        <td><?= $w['bnb_address'] ? 'BNB' : 'Bank' ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $w['status'] == 'approved' ? 'success' : 
                                ($w['status'] == 'declined' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($w['status']) ?>
                            </span>
                        </td>
                        <td><?= $w['requested_at'] ?></td>
                        <td>
                            <?php if ($w['status'] == 'pending'): ?>
                                <a href="?approve=<?= $w['id'] ?>&page=<?= $page ?>" class="btn btn-success btn-sm">Approve</a>
                            <?php else: ?>
                                <em>N/A</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No withdrawals found.</td>
                </tr>
            <?php endif ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next &raquo;</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
</body>
</html>
