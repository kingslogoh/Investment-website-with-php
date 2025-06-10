<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$txn_id = $_GET['txn'] ?? null;

if (!$txn_id) {
    echo "Invalid request.";
    exit();
}

// Fetch transaction
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? AND transaction_id = ? AND status = 'pending'");
$stmt->execute([$user_id, $txn_id]);
$txn = $stmt->fetch();

if (!$txn) {
    echo "No pending transaction found.";
    exit();
}

// Get system BNB wallet address
$settings = $pdo->query("SELECT bnb_wallet FROM payment_settings LIMIT 1")->fetch();
$bnb_wallet = $settings['bnb_wallet'];
$amount = $txn['amount'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BNB Payment</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .countdown { font-size: 1.8rem; font-weight: bold; color: #dc3545; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="card p-4 shadow">
        <h3 class="text-center mb-4">🚀 Complete Your BNB Deposit</h3>

        <div class="mb-3">
            <p><strong>Send exactly:</strong></p>
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <strong id="amount"><?= number_format($amount, 6) ?> BNB</strong>
                <button class="btn btn-sm btn-outline-success" onclick="copyText('amount')">Copy</button>
            </div>
        </div>

        <div class="mb-3">
            <p><strong>To this wallet address:</strong></p>
            <div class="alert alert-secondary d-flex justify-content-between align-items-center">
                <span id="bnb-address"><?= htmlspecialchars($bnb_wallet) ?></span>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyText('bnb-address')">Copy</button>
            </div>
            <div class="text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($bnb_wallet) ?>" alt="QR Code">
            </div>
        </div>

        <div class="mb-3">
            <p>⏱ Time remaining: <span class="countdown" id="timer">15:00</span></p>
            <p class="text-muted">This page will automatically detect the payment.</p>
        </div>

        <div id="status" class="text-center mt-4"></div>

        <div class="text-center mt-4">
            <a href="member.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</div>

<script>
    function copyText(id) {
        const text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert("Copied: " + text);
        });
    }

    const timerEl = document.getElementById('timer');
    const statusEl = document.getElementById('status');
    const txnId = "<?= $txn_id ?>";
    const storageKey = `bnb_timer_${txnId}`;

    // Set expiration time if not already set
    if (!sessionStorage.getItem(storageKey)) {
        const expireAt = Date.now() + 15 * 60 * 1000; // 15 minutes from now
        sessionStorage.setItem(storageKey, expireAt);
    }

    let timerInterval;

    function updateTimer() {
        const expireAt = parseInt(sessionStorage.getItem(storageKey));
        const now = Date.now();
        const timeLeft = Math.floor((expireAt - now) / 1000);

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            sessionStorage.removeItem(storageKey);
            timerEl.textContent = "00:00";
            statusEl.innerHTML = `<div class="alert alert-danger">⛔ Time expired. Please try again.</div>`;
            return;
        }

        const min = Math.floor(timeLeft / 60);
        const sec = timeLeft % 60;
        timerEl.textContent = `${min}:${sec < 10 ? '0' : ''}${sec}`;
    }

    timerInterval = setInterval(updateTimer, 1000);
    updateTimer();

    function checkPayment() {
        fetch('check_bnb_payment.php?txn=<?= urlencode($txn_id) ?>')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    clearInterval(timerInterval);
                    sessionStorage.removeItem(storageKey);
                    statusEl.innerHTML = `
                        <div class="alert alert-success">
                            ✅ Payment detected! <br> Amount: ${data.amount} BNB<br><br>
                            <a href="member.php" class="btn btn-success">Go to Dashboard</a>
                        </div>`;
                }
            });
    }

    setInterval(() => {
        const expireAt = parseInt(sessionStorage.getItem(storageKey));
        if (Date.now() < expireAt) {
            checkPayment();
        }
    }, 10000);
</script>
</body>
</html>
