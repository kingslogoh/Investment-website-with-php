<?php
session_start();
require 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total withdrawals
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM withdrawals WHERE user_id = ?");
$totalStmt->execute([$user_id]);
$total_withdrawals = $totalStmt->fetchColumn();
$total_pages = ceil($total_withdrawals / $limit);

// Fetch withdrawals with LIMIT
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY requested_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Optional: Fetch site name and logo
$site = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal History - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container mt-5">
    <h3 class="mb-4">Withdrawal History</h3>
    <?php if (empty($withdrawals)): ?>
        <div class="alert alert-info">You have no withdrawal records.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Bank Name</th>
                    <th>Account Name</th>
                    <th>Account Number</th>
                    <th>BNB Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($withdrawals as $withdrawal): ?>
                    <tr>
                        <td>$<?php echo number_format($withdrawal['amount'], 2); ?></td>
                        <td>
                            <?php
                            $status = $withdrawal['status'];
                            $badge = $status == 'approved' ? 'success' :
                                     ($status == 'declined' ? 'danger' : 'warning');
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                        </td>
                        <td><?php echo date("Y-m-d H:i", strtotime($withdrawal['requested_at'])); ?></td>
                        <td><?php echo htmlspecialchars($withdrawal['bank_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($withdrawal['account_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($withdrawal['account_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($withdrawal['bnb_address'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
