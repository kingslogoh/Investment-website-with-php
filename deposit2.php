<?php
session_start();
include 'database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$settings = $pdo->query("SELECT * FROM payment_settings WHERE id = 1")->fetch();

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <h2 class="text-center mb-4">💰 Choose Your Deposit Method</h2>
    <div class="row justify-content-center">

        <!-- PayPal -->
        <h5 class="mb-3">Pay with PayPal</h5>
         <form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-form">
    <input type="hidden" name="business" value="<?= htmlspecialchars($settings['paypal_email']) ?>"> <!-- Your real PayPal email -->
    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="item_name" value="Meta Love Deposit">
    <input type="hidden" name="currency_code" value="USD">
    <input type="hidden" name="amount" id="paypal-amount" value="10.00">
    <input type="hidden" name="notify_url" value="paypal_ipn.php"> <!-- IPN listener -->
    <input type="hidden" name="return" value="thank_you.php"> <!-- Success page -->
    <input type="hidden" name="cancel_return" value="payment_cancelled.php"> <!-- Cancel page -->
    <input type="hidden" name="custom" value="<?= $user_id ?>"> <!-- Pass user ID -->

    <div class="mb-3">
        <label for="paypal-amount-input" class="form-label">Amount (USD)</label>
        <input type="number" step="0.01" min="1" class="form-control" id="paypal-amount-input" value="10.00">
    </div>

    <button class="btn btn-primary w-100" type="submit">Proceed to PayPal</button>
</form>
         

        <!-- Flutterwave -->
        <div class="col-md-5 deposit-method">
            <div class="card p-4 shadow-sm">
                <h5 class="mb-3">Pay with Flutterwave</h5>
                <p>Secure payment using cards, bank, or mobile money via Flutterwave.</p>
                <a href="deposit.php" class="btn btn-warning w-100">Proceed to Flutterwave</a>
            </div>
        </div>

        <!-- BNB -->
        <div class="col-md-5 deposit-method">
            <div class="card p-4 shadow-sm">
                <h5 class="mb-3">Deposit with BNB</h5>
                <form action="create_bnb_txn.php" method="post">
    <div class="mb-3">
        <label for="bnb-amount" class="form-label">Enter Amount (USD Equivalent in BNB)</label>
        <input type="number" step="0.0001" min="0.01" name="amount" id="bnb-amount" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success w-100 mt-3">Proceed to BNB Payment</button>
 

   </div>
        </div>

    </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script>
    // Update PayPal amount dynamically
    document.getElementById('paypal-amount-input').addEventListener('input', function () {
        document.getElementById('paypal-amount').value = this.value;
    });

    function copyToClipboard(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert("Copied to clipboard: " + text);
        });
    }
</script>

<style>
    .card {
        border-radius: 10px;
    }
    .deposit-method {
        margin-bottom: 30px;
    }
</style>
