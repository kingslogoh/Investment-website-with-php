<?php
session_start();
include 'database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

include 'header.php';

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM investments WHERE user_id = ?");
$countStmt->execute([$user_id]);
$total_investments = $countStmt->fetchColumn();
$total_pages = ceil($total_investments / $limit);

// Fetch paginated results
$stmt = $pdo->prepare("SELECT * FROM investments WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
  <!-- Navbar Start -->
   <nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>
    <!-- Navbar End -->
<div class="container py-5">
    <h2 class="mb-4">Investment History</h2>

    <?php if (empty($investments)): ?>
        <div class="alert alert-info">You have not made any investments yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Amount</th>
                        <th>Profit</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Maturity Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($investments as $inv): ?>
                        <tr>
                            <td>#<?php echo $inv['id']; ?></td>
                            <td>$<?php echo number_format($inv['amount'], 2); ?></td>
                            <td>$<?php echo number_format($inv['profit'], 2); ?></td>
                            <td><?php echo htmlspecialchars($inv['payment_method']); ?></td>
                            <td>
                                <?php
                                    $badge = match($inv['status']) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'completed' => 'success',
                                        'declined' => 'danger',
                                        default => 'secondary',
                                    };
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($inv['status']); ?></span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($inv['created_at'])); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($inv['maturity_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
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

<?php include 'footer.php'; ?>
