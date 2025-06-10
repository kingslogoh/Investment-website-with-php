<?php
session_start();
require 'database.php'; // adjust path to your DB connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Total number of records
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$total_stmt->execute([$user_id]);
$total_deposits = $total_stmt->fetchColumn();
$total_pages = ceil($total_deposits / $limit);

// Fetch paginated records
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$deposits = $stmt->fetchAll();

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
    <title>My Deposit History</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: center;
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.9em;
            font-weight: bold;
            display: inline-block;
        }
        .pending {
            background-color: #ffedb5;
            color: #b58900;
        }
        .approved {
            background-color: #d4edda;
            color: #2e7d32;
        }
        .declined {
            background-color: #f8d7da;
            color: #c0392b;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            display: inline-block;
            margin: 0 4px;
            padding: 8px 14px;
            color: #007bff;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
        }
        .pagination a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination a:hover {
            background-color: #e2e6ea;
        }
        @media screen and (max-width: 600px) {
            .container {
                padding: 20px;
            }
            th, td {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>My Deposit History</h2>
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Amount</th>
            <th>Currency</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php if (count($deposits) > 0): ?>
            <?php foreach ($deposits as $deposit): ?>
                <tr>
                    <td><?= htmlspecialchars($deposit['transaction_id']) ?></td>
                    <td>$<?= number_format($deposit['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($deposit['currency']) ?></td>
                    <td>
                        <span class="status <?= strtolower($deposit['status']) ?>">
                            <?= ucfirst($deposit['status']) ?>
                        </span>
                    </td>
                    <td><?= date("M d, Y h:i A", strtotime($deposit['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5">No deposits found.</td></tr>
        <?php endif; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
