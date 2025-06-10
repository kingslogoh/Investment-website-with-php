<?php
include 'database.php';
include 'header.php';

$sql = "SELECT * FROM team ORDER BY id DESC";
$stmt = $pdo->query($sql);
$team = $stmt->fetchAll();

// Fetch current settings
$stmt = $pdo->query("SELECT id, site_name, site_logo, site_description FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);

$site_id = $site['id'] ?? null;
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
$site_description = $site['site_description'] ?? '';



try {
    // Fetch the first image from the database
    $stmt = $pdo->query("SELECT * FROM carousel_images ORDER BY id ASC LIMIT 1");
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>



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

              <!-- Header Start -->
<div class="container-fluid bg-breadcrumb" style="background: url('uploads/c/<?= htmlspecialchars($image['image_path']); ?>') center/cover no-repeat;">
    <div class="bg-breadcrumb-single"></div>
    <div class="container text-center py-5" style="max-width: 900px;">
       <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Our Team</h4>
        <ol class="breadcrumb justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-primary">Team</li>
        </ol>    
    </div>
</div>
<!-- Header End -->

        

        <div class="container py-5">
   <h2>Our <?php echo htmlspecialchars($site_name); ?> Company Dedicated Team Member</h2>
    <div class="row">
        <?php foreach ($team as $member): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="./<?= htmlspecialchars($member['image']); ?>" class="card-img-top" alt="Team Member Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($member['name']); ?></h5>
                        <p class="card-text"><?= htmlspecialchars($member['position']); ?></p>
                        <div class="social-links">
                            <?php if ($member['facebook']): ?>
                                <a href="<?= htmlspecialchars($member['facebook']); ?>" target="_blank">Facebook</a>
                            <?php endif; ?>
                            <?php if ($member['twitter']): ?>
                                <a href="<?= htmlspecialchars($member['twitter']); ?>" target="_blank">Twitter</a>
                            <?php endif; ?>
                            <?php if ($member['instagram']): ?>
                                <a href="<?= htmlspecialchars($member['instagram']); ?>" target="_blank">Instagram</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
