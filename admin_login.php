<?php
session_start();
include 'database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($_POST['password'], $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: admin.php");
        exit();
    } else {
        $message = "❌ Invalid login credentials.";
    }
}
include 'header.php';
?>

<div class="container mt-5">
    <h2>🔐 Admin Login</h2>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
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
        <button class="btn btn-success">Login</button>
    </form>
</div>

<?php include 'footer.php'; ?>
