<?php
include 'database.php';
session_start();
include 'header.php';

// Enable PDO error reporting
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO payment_settings 
        (id, paypal_email, bnb_wallet, flutterwave_secret_key, flutterwave_public_key, bscscan_api_key)
        VALUES (1, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            paypal_email = VALUES(paypal_email),
            bnb_wallet = VALUES(bnb_wallet),
            flutterwave_secret_key = VALUES(flutterwave_secret_key),
            flutterwave_public_key = VALUES(flutterwave_public_key),
            bscscan_api_key = VALUES(bscscan_api_key)");

    $stmt->execute([
        $_POST['paypal_email'],
        $_POST['bnb_wallet'],
        $_POST['flutterwave_secret_key'],
        $_POST['flutterwave_public_key'],
        $_POST['bscscan_api_key']
    ]);

    $message = "✅ Payment settings updated successfully.";
}

// Fetch current payment settings
$stmt = $pdo->query("SELECT * FROM payment_settings WHERE id = 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<!-- Content -->
<div class="container mt-5">
    <h2 class="text-center mb-4">🔧 Edit Payment Settings</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST">
            <div class="mb-3">
                <label for="paypal_email" class="form-label">PayPal Email</label>
                <input type="email" class="form-control" id="paypal_email" name="paypal_email" value="<?= htmlspecialchars($settings['paypal_email'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="bnb_wallet" class="form-label">BNB Wallet Address</label>
                <input type="text" class="form-control" id="bnb_wallet" name="bnb_wallet" value="<?= htmlspecialchars($settings['bnb_wallet'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="flutterwave_secret_key" class="form-label">Flutterwave Secret Key</label>
                <input type="text" class="form-control" id="flutterwave_secret_key" name="flutterwave_secret_key" value="<?= htmlspecialchars($settings['flutterwave_secret_key'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="flutterwave_public_key" class="form-label">Flutterwave Public Key</label>
                <input type="text" class="form-control" id="flutterwave_public_key" name="flutterwave_public_key" value="<?= htmlspecialchars($settings['flutterwave_public_key'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="bscscan_api_key" class="form-label">BscScan API Key</label>
                <input type="text" class="form-control" id="bscscan_api_key" name="bscscan_api_key" value="<?= htmlspecialchars($settings['bscscan_api_key'] ?? '') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">💾 Save Settings</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
