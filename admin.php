<?php
 
session_start();
include'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch dashboard stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalInvestments = $pdo->query("SELECT SUM(amount) FROM investments WHERE status = 'approved'")->fetchColumn();
$totalWithdrawals = $pdo->query("SELECT SUM(amount) FROM withdrawals WHERE status = 'approved'")->fetchColumn();
$pendingActions = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM investments WHERE status = 'pending') +
        (SELECT COUNT(*) FROM withdrawals WHERE status = 'pending')
")->fetchColumn();

// Fetch site name and logo from database
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - Investments</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Admin Panel Stylesheet -->
    <link href="css/admin-style.css" rel="stylesheet">
</head>
<body>
   <!-- Navbar Start -->
   <nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?> Admin
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>
    <!-- Navbar End -->
<!-- Sidebar Start -->
<div class="sidebar bg-dark text-white p-3">
    <h2 class="text-center mb-4">Admin Panel</h2>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="admin.php" class="nav-link text-white"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        <li class="nav-item">
            <a href="total_users.php" class="nav-link text-white"><i class="fas fa-users me-2"></i> Users</a>
        </li>
        <li class="nav-item">
            <a href="investments_list.php" class="nav-link text-white"><i class="fas fa-money-bill-wave me-2"></i> Investments</a>
        </li>
        <li class="nav-item">
            <a href="admin_withdrawals.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i> Withdrawals</a>
        </li>
        <li class="nav-item">
            <a href="admin_deposits.php" class="nav-link text-white"><i class="fas fa-credit-card me-2"></i> All Deposits</a>
        </li>
        <li class="nav-item">
            <a href="settings.php" class="nav-link text-white"><i class="fas fa-cogs me-2"></i> Settings</a>
        </li>
    </ul>
</div>
<!-- Sidebar End -->

<!-- Main Content Start -->
<div class="main-content">
  

    <!-- Dashboard Header Start -->
    <div class="container-fluid bg-light py-4">
        <h2 class="text-primary">Dashboard</h2>
        <p class="mb-0">Overview of your website's stats and activity.</p>
    </div>
    <!-- Dashboard Header End -->

    <!-- Dashboard Stats Start -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3 mb-4">
    <div class="card bg-primary text-white text-center py-4">
        <i class="fas fa-users fa-3x mb-3"></i>
        <h4>Total Users</h4>
        <p class="fs-5"><?php echo number_format($totalUsers); ?></p>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card bg-success text-white text-center py-4">
        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
        <h4>Total Investments</h4>
        <p class="fs-5">$<?php echo number_format($totalInvestments ?? 0, 2); ?></p>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card bg-warning text-white text-center py-4">
        <i class="fas fa-credit-card fa-3x mb-3"></i>
        <h4>Total Withdrawals</h4>
        <p class="fs-5">$<?php echo number_format($totalWithdrawals ?? 0, 2); ?></p>
    </div>
</div>
<div class="col-md-3 mb-4">
    <div class="card bg-danger text-white text-center py-4">
        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
        <h4>Pending Actions</h4>
        <p class="fs-5"><?php echo number_format($pendingActions); ?></p>
    </div>
</div>
        </div>
    </div>
    <!-- Dashboard Stats End -->

    <!-- Footer Start -->
    <div class="container-fluid footer bg-dark text-white py-4 mt-5">
        <div class="text-center">
            <p>&copy; 2025 Investments. All rights reserved.</p>
        </div>
    </div>
    <!-- Footer End -->
</div>
<!-- Main Content End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

<!-- JavaScript Libraries -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Admin JS -->
<script src="js/admin.js"></script>
</body>
</html>
