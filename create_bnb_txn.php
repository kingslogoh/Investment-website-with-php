<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usd_amount = floatval($_POST['amount']);

    if ($usd_amount <= 0) {
        echo "Invalid amount.";
        exit();
    }

    // Fetch current BNB/USD price from CoinGecko
    $api_url = "https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=usd";
    $json = file_get_contents($api_url);
    $data = json_decode($json, true);

    if (!isset($data['binancecoin']['usd'])) {
        echo "Unable to fetch BNB price.";
        exit();
    }

    $bnb_price_usd = $data['binancecoin']['usd'];

    // Convert USD to BNB
    $bnb_amount = $usd_amount / $bnb_price_usd;

    // Generate unique decimal using user_id
    $unique_decimal = $user_id / 1000000; // e.g. 37 becomes 0.000037
    $bnb_amount += $unique_decimal;
    $bnb_amount = round($bnb_amount, 6); // round to 6 decimals for display/payment

    // Create unique transaction ID
    $txn_id = strtoupper(uniqid("BNB_"));

    // Store in database
    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_id, amount, currency, status, created_at) VALUES (?, ?, ?, 'BNB', 'pending', NOW())");
    $stmt->execute([$user_id, $txn_id, $bnb_amount]);

    // Redirect to payment page
    header("Location: bnb_payment.php?txn=" . urlencode($txn_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select BNB Amount</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="mb-4 text-center">💰 Enter Amount to Deposit (USD)</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount in USD</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="1" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Proceed to BNB Payment</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <a href="member.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</div>
</body>
</html>
