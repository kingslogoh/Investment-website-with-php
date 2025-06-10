<?php
session_start();
include 'database.php';

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Fetch all investment plans
$stmt = $pdo->query("SELECT * FROM investment_plans ORDER BY id ASC");
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
include("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Investment Plans - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/member-area.css" rel="stylesheet">
    <style>
        .plan-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 25px;
            transition: 0.3s;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .plan-card:hover {
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transform: scale(1.02);
        }
        .plan-title {
            font-size: 1.4rem;
            font-weight: bold;
        }
        .plan-roi {
            font-size: 1.2rem;
            color: #28a745;
        }
    </style>
</head>
<body>

 <!-- Spinner Start -->
       <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


       


        <!-- Navbar & Hero Start -->
        <div class="container-fluid sticky-top px-0">
            <div class="position-absolute bg-dark" style="left: 0; top: 0; width: 100%; height: 100%;">
            </div>
            <div class="container px-0">
                <nav class="navbar navbar-expand-lg navbar-dark bg-white py-3 px-4">
                    <a href="index.php" class="navbar-brand p-0">
                               <h3 class="text-primary m-0"><img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" width="100">
 <?php echo htmlspecialchars($site_name); ?></h3>
                        <!-- <img src="img/logo.png" alt="Logo"> -->
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                        <span class="fa fa-bars"></span>
                    </button>
              
                    <div class="collapse navbar-collapse" id="navbarCollapse">
                        <div class="navbar-nav ms-auto py-0">
                            <a href="index.php" class="nav-item nav-link active">Home</a>
                            <a href="plans.php" class="nav-item nav-link">Plans</a>
                            <a href="login.php" class="nav-item nav-link">Login</a>
                            <a href="register.php" class="nav-item nav-link">Register</a>
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">More</a>
                                <div class="dropdown-menu m-0">
                            <a href="about.php" class="nav-item nav-link">About</a>
                                    <a href="team.php" class="dropdown-item">Our Team</a>
                                    <a href="testimonial.php" class="dropdown-item">Testimonial</a>
                                    <a href="faqs.php" class="dropdown-item">FAQs</a>
                                   <!-- <a href="404.php" class="dropdown-item">404 Page</a>-->
                                </div>
                            </div>
                            <a href="contact.php" class="nav-item nav-link">Contact</a>
                        </div>
                        <div class="d-flex align-items-center flex-nowrap pt-xl-0">
                           <!-- <button class="btn btn-primary btn-md-square mx-2" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>-->
                            <a href="register.php" class="btn btn-primary rounded-pill text-white py-2 px-4 ms-2 flex-wrap flex-sm-shrink-0">Start Investing</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <!-- Navbar & Hero End -->

<!-- Page Content -->
<div class="container py-5">
    <h2 class="text-center text-primary mb-4">Our Investment Plans</h2>
    <div class="row g-4">
        <?php foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="plan-card">
                    <div class="plan-title"><?php echo htmlspecialchars($plan['name']); ?></div>
                    <ul class="list-unstyled mt-3 mb-3">
                        <li><strong>Duration:</strong> <?php echo $plan['duration_days']; ?> days</li>
                        <li><strong>Interest Rate:</strong> <span class="plan-roi"><?php echo $plan['interest_rate']; ?>%</span></li>
                        <li><strong>Min Investment:</strong> $<?php echo number_format($plan['min_amount'], 2); ?></li>
                        <li><strong>Max Investment:</strong> $<?php echo number_format($plan['max_amount'], 2); ?></li>
                    </ul>
                    <a href="investment.php" class="btn btn-primary w-100">Invest Now</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
