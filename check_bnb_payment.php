<?php
session_start();
header('Content-Type: application/json');
include 'database.php';

$user_id = $_SESSION['user_id'] ?? null;
$txn_id = $_GET['txn'] ?? null;

if (!$user_id || !$txn_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session or transaction ID']);
    exit();
}

// Fetch pending transaction for the user
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND transaction_id = ? AND status = 'pending'");
$stmt->execute([$user_id, $txn_id]);
$txn = $stmt->fetch();

if (!$txn) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit();
}

$expected_amount = $txn['amount'];

// Get system BNB address and BscScan API key from database
$settings = $pdo->query("SELECT bnb_wallet, bscscan_api FROM payment_settings LIMIT 1")->fetch();
$bnb_address = $settings['bnb_wallet'];
$bscscan_api = $settings['bscscan_api'];

if (!$bnb_address || !$bscscan_api) {
    echo json_encode(['status' => 'error', 'message' => 'System not configured properly']);
    exit();
}

// Use BscScan Mainnet API endpoint
$url = "https://api.bscscan.com/api?module=account&action=txlist&address=$bnb_address&sort=desc&apikey=$bscscan_api";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data['status'] !== "1" || empty($data['result'])) {
    echo json_encode(['status' => 'pending']);
    exit();
}

// Loop through transactions and look for exact amount match
$matched = false;
foreach ($data['result'] as $tx) {
    if (strtolower($tx['to']) === strtolower($bnb_address) && $tx['isError'] === "0") {
        // Convert value from Wei to BNB
        $received_amount = bcdiv($tx['value'], bcpow(10, 18), 6);

        // Match exact unique amount
        if ($received_amount == $expected_amount) {
            $matched = true;
            $txn_hash = $tx['hash'];
            break;
        }
    }
}

if ($matched) {
    // Get live BNB to USD rate from CoinGecko
    $rate_json = file_get_contents("https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=usd");
    $rate_data = json_decode($rate_json, true);
    $bnb_price = $rate_data['binancecoin']['usd'] ?? 0;

    if ($bnb_price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to fetch BNB price']);
        exit();
    }

    $usd_value = round($expected_amount * $bnb_price, 2); // Round for balance

    // Update transaction
    $pdo->prepare("UPDATE transactions SET status = 'completed', txn_hash = ?, currency = 'BNB' WHERE id = ?")
        ->execute([$txn_hash, $txn['id']]);

    // Update user balance
    $pdo->prepare("UPDATE user_balance SET balance = balance + ?, updated_at = NOW() WHERE user_id = ?")
        ->execute([$usd_value, $user_id]);

    echo json_encode([
        'status' => 'success',
        'amount' => $expected_amount,
        'usd_value' => $usd_value,
        'hash' => $txn_hash
    ]);
} else {
    echo json_encode(['status' => 'pending']);
}
