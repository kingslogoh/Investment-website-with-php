<?php
session_start();
include 'database.php';

$message = "";
if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Check if the code exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_code = ? AND verified = 0");
    $stmt->execute([$verification_code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mark user as verified
        $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_code = NULL WHERE verification_code = ?");
        $stmt->execute([$verification_code]);
        $message = "<div class='alert alert-success'>Your account has been verified. <a href='login.php'>Login here</a>.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Invalid or expired verification link.</div>";
    }
} else {
    $message = "<div class='alert alert-warning'>No verification code provided.</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
        <h3 class="text-center">Email Verification</h3>
        <?php echo $message; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
