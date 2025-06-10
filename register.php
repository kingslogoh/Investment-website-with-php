<?php
 error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include'database.php';

// Fetch site name and logo from database
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $verification_code = md5(uniqid($email, true));

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = "<div class='alert alert-danger'>Email is already registered. Try <a href='login.php'>logging in</a>.</div>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code, verified) VALUES (?, ?, ?, ?, 0)");
        if ($stmt->execute([$fullname, $email, $hashed_password, $verification_code])) {
            // Send verification email
            $domain = $_SERVER['HTTP_HOST'];
$verification_link = "https://$domain/investment/verify.php?code=$verification_code";
$from_email = "no-reply@$domain";

$subject = "Verify Your Email - $site_name";
$headers = "From: $site_name <$from_email>\r\n";
$headers .= "Reply-To: $from_email\r\n";
$headers .= "Return-Path: $from_email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

$message_body = "
<html>
<head>
  <meta charset='UTF-8'>
  <title>Email Verification</title>
</head>
<body style='font-family:Arial, sans-serif; background-color:#f4f4f4; padding:20px;'>
  <div style='max-width:600px; margin:auto; background:#ffffff; padding:20px; border-radius:10px;'>
    <h2 style='color:#333;'>Welcome to $site_name!</h2>
    <p style='color:#555;'>Click the link below to verify your email and activate your account:</p>
    <p><a href='$verification_link' style='background:#007bff; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px;'>Verify Email</a></p>
    <p style='color:#999;'>If you didn't sign up, you can safely ignore this email.</p>
  </div>
</body>
</html>
";

mail($email, $subject, $message_body, $headers);


            $message = "<div class='alert alert-success'>Account created successfully. Check your email to verify your account.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error creating account. Please try again.</div>";
        }
    }
}
 include 'header.php';

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

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
        
        <h3 class="text-center">Create an Account</h3>
        <?php if (!empty($message)) { echo $message; } ?>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>
        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
 <?php include 'footer.php' ?>
