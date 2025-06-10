<?php
include 'database.php';

// Get JSON payload
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log file (optional for debugging)
// file_put_contents("ipn_log.txt", print_r($data, true), FILE_APPEND);

if (!empty($data['payment_id']) && $data['payment_status'] === 'finished') {
    $payment_id = $data['payment_id'];

    // Get transaction
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND status = 'pending'");
    $stmt->execute([$payment_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        $user_id = $transaction['user_id'];
        $amount = $transaction['amount'];

        // Update user balance
        $stmt = $pdo->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?");
        $stmt->execute([$amount, $user_id]);

        // Mark transaction as completed
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
        $stmt->execute([$payment_id]);
    }
}
