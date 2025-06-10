<?php
session_start();
include 'database.php';

$apiKey = "50MXW9N-Y7W46QB-MY0WSYV-46J68B4"; // NOWPayments API key
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = floatval($_POST['amount']);
    $currency = "USDT";
    $pay_currency = "BTC";
    $order_id = uniqid("INV-");

    if ($amount <= 0) {
        $error = "Invalid amount.";
    } else {
        // Prepare data
        $data = [
            "price_amount" => $amount,
            "price_currency" => $currency,
            "pay_currency" => $pay_currency,
            "ipn_callback_url" => "https://studentearning.com/investment/ipn.php",
            "order_id" => $order_id,
            "order_description" => "Funding user balance"
        ];

        // Call NOWPayments
        $ch = curl_init("https://api.nowpayments.io/v1/payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-api-key: $apiKey",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['invoice_url'], $result['payment_id'])) {
            // Save transaction
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_id, amount, currency, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$user_id, $result['payment_id'], $amount, $currency]);

            // Redirect to NOWPayments
            header("Location: " . $result['invoice_url']);
            exit;
        } else {
            $error = "Payment error! Please try again.";
        }
    }
}
?>

<!-- HTML PART -->
<!DOCTYPE html>
<html>
<head>
    <title>Fund Balance</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Fund Your Account</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label for="amount">Amount (USDT)</label>
            <input type="number" name="amount" id="amount" class="form-control" value="10" min="1" required>
        </div>
        <button type="submit" class="btn btn-success">Proceed to Pay</button>
    </form>
</div>
</body>
</html>
