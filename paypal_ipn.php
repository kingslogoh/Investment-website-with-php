<?php
include 'database.php'; // Your PDO connection

// Create logs folder if it doesn't exist
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
}

// STEP 1: Read raw POST data
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = [];

foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Prepare validation string
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Log raw data before contacting PayPal
file_put_contents('logs/paypal_ipn.log', "[" . date('Y-m-d H:i:s') . "] Raw POST:\n" . print_r($myPost, true) . "\n", FILE_APPEND);

// STEP 2: Validate IPN with PayPal
$ch = curl_init('https://ipnpb.paypal.com/cgi-bin/webscr'); // Live endpoint
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Log PayPal response
file_put_contents('logs/paypal_ipn.log', "[" . date('Y-m-d H:i:s') . "] PayPal Response: $response (HTTP $http_code)\n\n", FILE_APPEND);

if (strcmp($response, "VERIFIED") === 0) {
    // VERIFIED Payment
    $user_id = $_POST['custom'];
    $txn_id = $_POST['txn_id'];
    $payment_status = $_POST['payment_status'];
    $amount = $_POST['mc_gross'];
    $currency = $_POST['mc_currency'];
    $payer_email = $_POST['payer_email'];

    // Check if txn_id already used
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
    $stmt->execute([$txn_id]);
    $exists = $stmt->fetchColumn();

    if ($payment_status === "Completed" && $currency === "USD" && !$exists) {
        // Insert transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_id, amount, currency, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $txn_id, $amount, $currency, 'completed']);

        // Update balance
        $stmt = $pdo->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?");
        $stmt->execute([$amount, $user_id]);

        file_put_contents('logs/paypal_ipn.log', "[" . date('Y-m-d H:i:s') . "] SUCCESS: Credited User ID $user_id with \$$amount (Txn ID: $txn_id)\n\n", FILE_APPEND);
    } else {
        file_put_contents('logs/paypal_ipn.log', "[" . date('Y-m-d H:i:s') . "] WARNING: Payment rejected or already processed. Txn ID: $txn_id, Status: $payment_status\n\n", FILE_APPEND);
    }
} else {
    // Invalid or tampered request
    file_put_contents('logs/paypal_ipn.log', "[" . date('Y-m-d H:i:s') . "] ERROR: IPN NOT VERIFIED\n\n", FILE_APPEND);
}
