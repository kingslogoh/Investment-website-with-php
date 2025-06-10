<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'database.php';
include 'functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Distribute matured profits
distributeProfitsOnce($pdo);

// Get user balance
$stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_balance = $stmt->fetchColumn() ?? 0;

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Fetch dashboard values
$total_investment = $pdo->prepare("SELECT SUM(amount) FROM investments WHERE user_id = ? AND status IN ('approved', 'completed')");
$total_investment->execute([$user_id]);
$total_investment = $total_investment->fetchColumn() ?? 0;

$returns_this_month = $pdo->prepare("
    SELECT SUM(profit) FROM investments 
    WHERE user_id = ? AND status = 'completed' AND MONTH(maturity_date) = MONTH(CURRENT_DATE()) AND YEAR(maturity_date) = YEAR(CURRENT_DATE())
");
$returns_this_month->execute([$user_id]);
$returns_this_month = $returns_this_month->fetchColumn() ?? 0;

$pending_withdrawals = $pdo->prepare("SELECT SUM(amount) FROM withdrawals WHERE user_id = ? AND status = 'pending'");
$pending_withdrawals->execute([$user_id]);
$pending_withdrawals = $pending_withdrawals->fetchColumn() ?? 0;

// Fetch active investments
$active_investments_stmt = $pdo->prepare("SELECT plan_id, amount FROM investments WHERE user_id = ? AND status = 'approved'");
$active_investments_stmt->execute([$user_id]);
$active_investments = $active_investments_stmt->fetchAll(PDO::FETCH_ASSOC);
include"header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Member Area - <?php echo htmlspecialchars($site_name); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Member Area Stylesheet -->
    <link href="css/member-area.css" rel="stylesheet">
</head>
<body>

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

<!-- Sidebar Start -->
<div class="sidebar bg-dark text-white p-3">
    <h2 class="text-center mb-4">Member Area</h2>
    <ul class="nav flex-column">
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
        <li class="nav-item">
            <a href="withdrawal_history.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i>Withdrawal history</a>
        </li>
        
         <li class="nav-item">
            <a href="investment_history.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i>Investment_history</a>
        </li>

         <li class="nav-item">
            <a href="deposit_history.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i>Deposit_history</a>
        </li>
    </ul>
</div>
<!-- Sidebar End -->

<!-- Main Content Start -->
<div class="main-content">


    <!-- Dashboard Header Start -->
    <div class="container-fluid bg-light py-4">
        <h2 class="text-primary">Your Investment Dashboard</h2>
        <p class="mb-0">Overview of your current investments, earnings, and recent activities.</p>
        <p>Your balance: <strong>$<?php echo number_format($user_balance, 2); ?></strong></p>
    </div>
    <!-- Dashboard Header End -->

   <!-- Dashboard Stats Start -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card bg-primary text-white text-center py-4">
                <i class="fas fa-wallet fa-3x mb-3"></i>
                <h4>Total Investment</h4>
                <p class="fs-5">$<?php echo number_format($total_investment, 2); ?></p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white text-center py-4">
                <i class="fas fa-arrow-up fa-3x mb-3"></i>
                <h4>Returns This Month</h4>
                <p class="fs-5">$<?php echo number_format($returns_this_month, 2); ?></p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white text-center py-4">
                <i class="fas fa-credit-card fa-3x mb-3"></i>
                <h4>Pending Withdrawals</h4>
                <p class="fs-5">$<?php echo number_format($pending_withdrawals, 2); ?></p>
            </div>
        </div>
       <div class="col-md-3 mb-4">
    <div class="card bg-info text-white text-center py-4">
        <i class="fas fa-chart-line fa-3x mb-3"></i>
        <h4>Active Investment</h4>
        <?php if (count($active_investments) > 0): ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($active_investments as $investment): ?>
                    <li class="fs-6">
                        Plan <?php echo htmlspecialchars($investment['plan_id']); ?>:
                        $<?php echo number_format($investment['amount'], 2); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="fs-5">None</p>
        <?php endif; ?>
    </div>
</div>

    </div>
</div>
<!-- Dashboard Stats End -->

    <!-- Investment Tools Start -->
   <!-- <div class="container mt-4">
        <h4 class="mb-4">Investment Tools</h4>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card p-4">
                    <h5>Investment Calculator</h5>
                    <p>Estimate your future returns based on various parameters.</p>
                    <a href="calculator.php" class="btn btn-primary">Try Now</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-4">
                    <h5>Risk Assessment</h5>
                    <p>Check your investment risk and optimize your portfolio.</p>
                    <a href="risk-assessment.php" class="btn btn-primary">Start Assessment</a>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card p-4">
                    <h5>Market News</h5>
                    <p>Stay updated with the latest market trends and tips.</p>
                    <a href="market-news.php" class="btn btn-primary">View News</a>
                </div>
            </div>
        </div>
    </div>-->
    <!-- Investment Tools End -->

    <!-- Footer Start -->
  <?php include'footer.php';?>
    <!-- Footer End -->
</div>
<!-- Main Content End -->

 

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Member JS -->
<script src="js/member-area.js"></script>
</body>
</html>
