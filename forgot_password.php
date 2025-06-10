<?php
session_start();
include 'database.php';

// Fetch site info
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Save token and expiry
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->execute([$token, $expiry, $email]);

        $domain = $_SERVER['HTTP_HOST'];
        $reset_link = "https://$domain/investment/reset_password.php?token=$token";

        $subject = "Reset Your Password - $site_name";
        $from_email = "no-reply@$domain";
        $headers = "From: $site_name <$from_email>\r\n";
        $headers .= "Reply-To: $from_email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $body = "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>Click the link below to reset your password:</p>
            <p><a href='$reset_link'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
        </body>
        </html>
        ";

        mail($email, $subject, $body, $headers);

        $message = "<div class='alert alert-success'>Check your email for a password reset link.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Email not found.</div>";
    }
}

include 'header.php';
?>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
        <h3 class="text-center">Forgot Password</h3>
        <?php echo $message; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
        </form>
        <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
    </div>
</div>

<?php include 'footer.php'; ?>
