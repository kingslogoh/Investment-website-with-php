<?php
session_start();
include 'database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = "⚠️ Admin already exists!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO admins (email, password) VALUES (?, ?)");
        $stmt->execute([$email, $password]);
        $message = "✅ Admin registered successfully!";
    }
}
include 'header.php';
?>

<div class="container mt-5">
    <h2>🛡️ Admin Registration</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Register</button>
        <a href="admin_login.php" class="btn btn-link">Already have an account? Login</a>
    </form>
</div>

<?php include 'footer.php'; ?>
