<?php
session_start();
include 'database.php';

$token = $_GET['token'] ?? '';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);

    // Check token validity
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry >= NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update password and clear token
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->execute([$password, $user["id"]]);
        $message = "<div class='alert alert-success'>Password has been reset. <a href='login.php'>Login</a></div>";
    } else {
        $message = "<div class='alert alert-danger'>Invalid or expired token.</div>";
    }
}

include 'header.php';
?>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card p-4 shadow-lg" style="max-width: 400px; width: 100%;">
        <h3 class="text-center">Reset Password</h3>
        <?php echo $message; ?>
        <?php if ($token && empty($message)) { ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="New Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
        </form>
        <?php } ?>
    </div>
</div>

<?php include 'footer.php'; ?>
