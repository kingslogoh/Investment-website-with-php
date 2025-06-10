<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Fetch Flutterwave public key
$stmt = $pdo->prepare("SELECT flutterwave_public_key FROM payment_settings WHERE id = 1 LIMIT 1");
$stmt->execute();
$key_data = $stmt->fetch(PDO::FETCH_ASSOC);
$public_key = $key_data['flutterwave_public_key'] ?? die("Flutterwave public key not found");

// Fetch user info
$stmt = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found");

$user_email = $user['email'];
$user_name = $user['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deposit Money - <?php echo htmlspecialchars($site_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS (Optional for team use) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            min-height: 100vh;
        }
        .container-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            margin: 80px auto;
        }
        h2 {
            text-align: center;
            color: #5e2b97;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        button {
            width: 100%;
            background: #5e2b97;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #432174;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4 shadow-sm">
    <a class="navbar-brand d-flex align-items-center" href="member.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" width="40" class="me-2">
        <strong><?php echo htmlspecialchars($site_name); ?></strong>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<!-- Deposit Form -->
<div class="container-box">
    <h2>Deposit Money</h2>
    <form id="depositForm">
        <label for="amount">Enter Amount ($)</label>
        <input type="number" id="amount" name="amount" min="1" required>
        <button type="button" onclick="payWithFlutterwave()">Deposit Now</button>
    </form>
</div>

<!-- Flutterwave Script -->
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
    function payWithFlutterwave() {
        let amount = parseFloat(document.getElementById("amount").value);
        if (isNaN(amount) || amount <= 0) {
            alert("Enter a valid amount");
            return;
        }

        FlutterwaveCheckout({
            public_key: "<?php echo $public_key; ?>",
            tx_ref: "txn_" + Math.floor(Math.random() * 1000000000),
            amount: amount,
            currency: "USD",
            payment_options: "card, mobilemoney, ussd",
            customer: {
                email: "<?php echo $user_email; ?>",
                phone_number: "0000000000",
                name: "<?php echo $user_name; ?>"
            },
            callback: function(response) {
                if (response.status === "successful") {
                    fetch("verify_deposit.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            transaction_id: response.transaction_id,
                            amount: amount
                        })
                    })
                    .then(res => res.json())
                    .then(data => alert(data.message))
                    .catch(() => alert("Error verifying payment"));
                } else {
                    alert("Payment was cancelled or failed.");
                }
            },
            customizations: {
                title: "<?php echo htmlspecialchars($site_name); ?>",
                description: "Securely deposit into your account",
                logo: "<?php echo htmlspecialchars($site_logo); ?>"
            }
        });
    }
</script>

</body>
</html>
