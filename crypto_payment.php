<?php
session_start();
include 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch NOWPayments API key
$stmt = $pdo->query("SELECT * FROM payment_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$api_key = $settings['nowpayments_api_key'];

// Dynamically get the domain
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$ipn_callback_url = $protocol . "://" . $domain . "/investment/ipn_callback.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $currency = $_POST['currency'];
    $email = $_POST['email'];

    $order_id = uniqid("txn_");

    $postData = [
        "price_amount" => $amount,
        "price_currency" => "usd",
        "pay_currency" => $currency,
        "order_id" => $order_id,
        "order_description" => "Crypto Deposit",
        "ipn_callback_url" => $ipn_callback_url
    ];

    $ch = curl_init('https://api.nowpayments.io/v1/invoice');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key:' . $api_key,
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $responseData = json_decode($response, true);
    curl_close($ch);

    if (isset($responseData['invoice_url'])) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_id, amount, currency, status, created_at, email)
                               VALUES (?, ?, ?, ?, 'pending', NOW(), ?)");
        $stmt->execute([$user_id, $order_id, $amount, $currency, $email]);

        header("Location: " . $responseData['invoice_url']);
        exit();
    } else {
        $message = "❌ Failed to create crypto invoice. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crypto Deposit</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center mb-4">Deposit with Cryptocurrency</h2>

    <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4">
        <form method="POST">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (USD)</label>
                <input type="number" name="amount" class="form-control" required min="1" step="0.01">
            </div>

            <div class="mb-3">
                <label for="currency" class="form-label">Choose Cryptocurrency</label>
                <select name="currency" class="form-select" required>
                    <option value="bnb">BNB (BEP20)</option>
                    <option value="btc">Bitcoin (BTC)</option>
                    <option value="eth">Ethereum (ETH)</option>
                    <option value="usdttrc20">USDT (TRC20)</option>
                    <option value="usdt">USDT (ERC20)</option>
                    <option value="ltc">Litecoin (LTC)</option>
                    <option value="doge">Dogecoin (DOGE)</option>
                    <option value="xrp">Ripple (XRP)</option>
                    <option value="bch">Bitcoin Cash (BCH)</option>
                    <option value="trx">TRON (TRX)</option>
                    <option value="ada">Cardano (ADA)</option>
                    <option value="dash">Dash</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary w-100">Pay with Crypto</button>
        </form>
    </div>
</div>
</body>
</html>
