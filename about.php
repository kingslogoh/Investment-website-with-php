<?php
include 'database.php';

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

// Fetch about content from the database
$stmt = $pdo->query("SELECT about_text, about_text2, global_customers, years_experience, team_members, about_image FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);

$about_text = $site['about_text'] ?? '';
$about_text2 = $site['about_text2'] ?? '';
$global_customers = $site['global_customers'] ?? 0;
$years_experience = $site['years_experience'] ?? 0;
$team_members = $site['team_members'] ?? 0;
$about_image = $site['about_image'] ?? '';

// Fetch individual carousel images
$homepage1 = $pdo->prepare("SELECT * FROM carousel_images WHERE image_name = ?");
$homepage2 = $pdo->prepare("SELECT * FROM carousel_images WHERE image_name = ?");
$homepage3 = $pdo->prepare("SELECT * FROM carousel_images WHERE image_name = ?");

$homepage1->execute(['homepage1']);
$homepage2->execute(['homepage2']);
$homepage3->execute(['homepage3']);

$image1 = $homepage1->fetch(PDO::FETCH_ASSOC);
$image2 = $homepage2->fetch(PDO::FETCH_ASSOC);
$image3 = $homepage3->fetch(PDO::FETCH_ASSOC);


?>
<?php include'header.php';?>


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
                            <button class="btn btn-primary btn-md-square mx-2" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
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
      <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">About Us</h4>
        <ol class="breadcrumb justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item active text-primary">Team</li>
        </ol>    
    </div>
</div>
<!-- Header End -->

        

        <!-- About Start -->
        <div class="container-fluid about bg-light py-5">
            <div class="container py-5">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6 col-xl-5 wow fadeInLeft" data-wow-delay="0.1s">
                        <div class="about-img">
                         <?php if (!empty($about_image)) : ?>
                <img src="<?= htmlspecialchars($about_image); ?>" alt="About Us" class="img-fluid rounded shadow">
            <?php endif; ?>
                           <!-- <img src="img/about-3.png" class="img-fluid w-100 rounded-top bg-white" alt="Image">
                            <img src="img/about-2.jpg" class="img-fluid w-100 rounded-bottom" alt="Image">-->
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-7 wow fadeInRight" data-wow-delay="0.3s">
                        <h4 class="text-primary">About Us</h4>
                        <h1 class="display-5 mb-4"><?= nl2br(htmlspecialchars($about_text)); ?></h1>
                        <p class="text ps-4 mb-4"><?= nl2br(htmlspecialchars($about_text2)); ?></p>
                         
                        <div class="row g-4 justify-content-between mb-5">
                          
                            <div class="col-xl-7 mb-5">
                                <div class="about-customer d-flex position-relative">
                                    <img src="img/customer-img-1.jpg" class="img-fluid btn-xl-square position-absolute" style="left: 0; top: 0;"  alt="Image">
                                    <img src="img/customer-img-2.jpg" class="img-fluid btn-xl-square position-absolute" style="left: 45px; top: 0;" alt="Image">
                                    <img src="img/customer-img-3.jpg" class="img-fluid btn-xl-square position-absolute" style="left: 90px; top: 0;" alt="Image">
                                    <img src="img/customer-img-1.jpg" class="img-fluid btn-xl-square position-absolute" style="left: 135px; top: 0;" alt="Image">
                                    <div class="position-absolute text-dark" style="left: 220px; top: 10px;">
                                        <p class="mb-0"><?= htmlspecialchars($global_customers); ?>+ </p>
                                        <p class="mb-0">Global Customers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 text-center align-items-center justify-content-center">
                           
                            <div class="col-sm-4">
                                <div class="bg-dark rounded p-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                         
                                        <h4 class="text-white fs-1 mb-0" style="font-weight: 600; font-size: 25px;"> <?= htmlspecialchars($years_experience); ?>+</h4>
                                    </div>
                                    <div class="w-100 d-flex align-items-center justify-content-center">
                                        <p class="mb-0">Years Of Experience</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-primary rounded p-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                         
                                        <h4 class="text-dark fs-1 mb-0" style="font-weight: 600; font-size: 25px;"> <?= htmlspecialchars($team_members); ?>+</h4>
                                    </div>
                                    <div class="w-100 d-flex align-items-center justify-content-center">
                                        <p class="text-white mb-0">Team Members</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- About End -->


        <!-- Team Start -->
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
        <!-- Team End -->

        <?php include'footer.php';?>
