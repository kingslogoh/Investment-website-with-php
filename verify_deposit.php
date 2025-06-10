<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["message" => "User session expired. Please log in again."]);
    exit;
}

$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents("php://input"), true);
$transaction_id = $data['transaction_id'] ?? null;
$amount = $data['amount'] ?? null;

if (!$transaction_id || !$amount) {
    echo json_encode(["message" => "Invalid request. Transaction ID or amount missing."]);
    exit;
}

// ✅ Get Flutterwave secret key
$stmt = $pdo->prepare("SELECT flutterwave_secret_key FROM payment_settings WHERE id = 1 LIMIT 1");
$stmt->execute();
$key_data = $stmt->fetch(PDO::FETCH_ASSOC);
$secret_key = $key_data['flutterwave_secret_key'] ?? die(json_encode(["message" => "Flutterwave secret key not found."]));

// ✅ Verify transaction with Flutterwave
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.flutterwave.com/v3/transactions/$transaction_id/verify");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $secret_key"]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!$response || !isset($response['status']) || $response['status'] !== "success") {
    echo json_encode(["message" => "Flutterwave verification failed."]);
    exit;
}

$confirmed_amount = $response['data']['amount'];
$currency = $response['data']['currency'];

if (strtoupper($currency) !== 'USD' || floatval($amount) != floatval($confirmed_amount)) {
    echo json_encode(["message" => "Amount mismatch or invalid currency."]);
    exit;
}

$transaction_email = $response['data']['customer']['email'] ?? '';

// ✅ Check for duplicate transaction
$stmt = $pdo->prepare("SELECT id FROM transactions WHERE transaction_id = ?");
$stmt->execute([$transaction_id]);
if ($stmt->fetch()) {
    echo json_encode(["message" => "Transaction already processed."]);
    exit;
}

// ✅ Update balance
$stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $stmt = $pdo->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?");
    $stmt->execute([$amount, $user_id]);
} else {
    $stmt = $pdo->prepare("INSERT INTO user_balance (user_id, balance) VALUES (?, ?)");
    $stmt->execute([$user_id, $amount]);
}

// ✅ Record transaction with email
$stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_id, amount, currency, status, email) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $transaction_id, $amount, $currency, 'successful', $transaction_email]);

echo json_encode(["message" => "Deposit successful!"]);
