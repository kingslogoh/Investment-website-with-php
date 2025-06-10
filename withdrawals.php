<?php
session_start();
include 'database.php';
include 'email_functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];

    $stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $balance = $stmt->fetchColumn();

    if ($balance >= $amount && $amount >= 0.5) {
        $pdo->beginTransaction();

        // Create withdrawal
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount, status, requested_at, bank_name, account_name, account_number, bnb_address) 
            VALUES (?, ?, 'pending', NOW(), ?, ?, ?, ?)");

        $bank = $_POST['bank_name'] ?? null;
        $acc_name = $_POST['account_name'] ?? null;
        $acc_num = $_POST['account_number'] ?? null;
        $bnb = $_POST['bnb_address'] ?? null;

        $stmt->execute([$user_id, $amount, $bank, $acc_name, $acc_num, $bnb]);

        // Deduct balance temporarily
        $stmt = $pdo->prepare("UPDATE user_balance SET balance = balance - ? WHERE user_id = ?");
        $stmt->execute([$amount, $user_id]);

        $pdo->commit();

        // Notify admin
        sendEmail('admin@' . ($_SERVER['HTTP_HOST'] ?? 'example.com'), 'New Withdrawal Request', "
            <p>User ID: $user_id has requested a withdrawal of <strong>$$amount</strong>.</p>
        ");

        $message = "Withdrawal request sent!";
    } else {
        $message = "Insufficient balance or amount too low.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdraw Funds</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .sidebar {
            min-height: 100vh;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?> 
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Start -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column p-3">
                    <li class="nav-item">
                        <a href="member.php" class="nav-link text-white"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a href="deposit2.php" class="nav-link text-white"><i class="fas fa-briefcase me-2"></i>Fund Account</a>
                    </li>
                    <li class="nav-item">
                        <a href="investment.php" class="nav-link text-white"><i class="fas fa-money-bill-wave me-2"></i> Investments</a>
                    </li>
                    <li class="nav-item">
                        <a href="withdrawals.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i> Withdrawals</a>
                    </li>
                   
                </ul>
            </div>
        </nav>
        <!-- Sidebar End -->

        <!-- Main Content Start -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="container">
                <h2 class="mb-4">Request Withdrawal</h2>

                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" class="bg-white p-4 rounded shadow">
                    <div class="mb-3">
                        <label>Amount ($)</label>
                        <input type="number" name="amount" step="0.01" min="0.5" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Payment Method</label>
                        <select name="method" class="form-select" required>
                            <option value="bank">Bank Transfer</option>
                            <option value="bnb">BNB Wallet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Bank Name</label>
                        <input type="text" name="bank_name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Account Name</label>
                        <input type="text" name="account_name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Account Number</label>
                        <input type="text" name="account_number" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>BNB Wallet Address</label>
                        <input type="text" name="bnb_address" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Submit Withdrawal</button>
                </form>
            </div>
        </main>
        <!-- Main Content End -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
