<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection (Change credentials)
$host = "localhost";
$user = "studente_investment";  // Change this if necessary
$pass = "kWZmHB3VvKxnFh6QEW7Q";      // Your database password
$dbname = "studente_investment";  

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pi Crypto Wallet</title>
    <script src="https://sdk.minepi.com/pi-sdk.js"></script>
    <script>
        Pi.init({ version: "2.0", sandbox: true });

        function authenticateUser() {
            Pi.authenticate(['payments', 'username'], function(payment) {
                console.log('Incomplete payment found:', payment);
            }).then(auth => {
                document.getElementById('username').innerText = `Welcome, ${auth.user.username}`;
                document.getElementById('user_id').value = auth.user.uid;
            }).catch(error => {
                console.error('Authentication error:', error);
            });
        }

        function sendPi() {
            const amount = document.getElementById("amount").value;
            const memo = "Sending Pi payment";
            const userId = document.getElementById('user_id').value;

            const paymentData = { amount: parseFloat(amount), memo: memo, metadata: {} };
            const paymentCallbacks = {
                onReadyForServerApproval: function(paymentId) {
                    console.log("Approving payment:", paymentId);
                    fetch("wallet.php", {
                        method: "POST",
                        body: JSON.stringify({ action: "approve", paymentId: paymentId, amount: amount, user_id: userId }),
                        headers: { "Content-Type": "application/json" }
                    });
                },
                onReadyForServerCompletion: function(paymentId, txid) {
                    console.log("Completing payment:", paymentId, txid);
                    fetch("wallet.php", {
                        method: "POST",
                        body: JSON.stringify({ action: "complete", paymentId: paymentId, txid: txid }),
                        headers: { "Content-Type": "application/json" }
                    });
                },
                onCancel: function(paymentId) {
                    console.log("Payment canceled:", paymentId);
                },
                onError: function(error) {
                    console.error("Payment error:", error);
                }
            };

            Pi.createPayment(paymentData, paymentCallbacks)
                .then(payment => console.log(payment))
                .catch(error => console.error(error));
        }

        function loadHistory() {
            fetch("wallet.php", {
                method: "POST",
                body: JSON.stringify({ action: "history" }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                let historyDiv = document.getElementById("history");
                historyDiv.innerHTML = "<h3>Transaction History</h3>";
                data.forEach(tx => {
                    historyDiv.innerHTML += `<p>${tx.type}: ${tx.amount} Pi - ${tx.status}</p>`;
                });
            });
        }

        window.onload = function() {
            authenticateUser();
            loadHistory();
        };
    </script>
</head>
<body>
    <h1>Pi Cryptocurrency Wallet</h1>
    <p id="username">Authenticating...</p>
    <input type="hidden" id="user_id">

    <h2>Send Pi</h2>
    <input type="number" id="amount" placeholder="Enter amount" required>
    <button onclick="sendPi()">Send</button>

    <div id="history"></div>
</body>
</html>
