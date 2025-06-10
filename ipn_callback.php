<?php
include 'database.php';

// Get IPN data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Security: verify status is finished and amount is confirmed
if ($data && isset($data['payment_status']) && $data['payment_status'] === 'finished') {
    $transaction_id = $data['order_id'];
    $amount = floatval($data['price_amount']);
    $currency = $data['pay_currency'];

    // Check if transaction exists and is still pending
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
    $stmt->execute([$transaction_id]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($txn) {
        // Update transaction to completed
        $update = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
        $update->execute([$transaction_id]);

        // Credit user balance
        $updateBalance = $pdo->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?");
        $updateBalance->execute([$amount, $txn['user_id']]);

        // Optionally log or email notification here
    }
}
http_response_code(200);
echo "IPN received";
