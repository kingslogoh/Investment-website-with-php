<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'database.php';

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user balance
$stmt = $pdo->prepare("SELECT balance FROM user_balance WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_balance = $stmt->fetchColumn() ?? 0;

// Get available investment plans
$plans = $pdo->query("SELECT * FROM investment_plans")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_id = $_POST['plan_id'] ?? null;
    $amount = floatval($_POST['amount']);

    $stmt = $pdo->prepare("SELECT * FROM investment_plans WHERE id = ?");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plan) {
        $error = "Invalid plan selected.";
    } elseif ($amount < $plan['min_amount'] || $amount > $plan['max_amount']) {
        $error = "Amount must be between $" . $plan['min_amount'] . " and $" . $plan['max_amount'] . ".";
    } elseif ($amount > $user_balance) {
        $error = "Insufficient balance.";
    } else {
        $pdo->prepare("UPDATE user_balance SET balance = balance - ? WHERE user_id = ?")->execute([$amount, $user_id]);

        $profit = ($amount * $plan['interest_rate']) / 100;
        $maturity_date = date('Y-m-d H:i:s', strtotime("+" . $plan['duration_days'] . " days"));

        $stmt = $pdo->prepare("INSERT INTO investments (user_id, amount, plan_id, profit, status, created_at, maturity_date) VALUES (?, ?, ?, ?, 'approved', NOW(), ?)");
        if ($stmt->execute([$user_id, $amount, $plan_id, $profit, $maturity_date])) {
            $success = "Investment successful. You will earn \${$profit} after {$plan['duration_days']} days.";
        } else {
            $error = "Error recording investment.";
        }
    }
}
?>
<?php include"header.php";?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invest Money - <?php echo htmlspecialchars($site_name); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="member.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>

<div class="container py-5">
    <h2 class="text-primary">Invest Money</h2>
    <p>Your balance: <strong>$<?php echo number_format($user_balance, 2); ?></strong></p>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <div class="card p-4">
        <form method="POST">
            <div class="mb-3">
                <label for="plan_id" class="form-label">Select Investment Plan</label>
                <select name="plan_id" id="plan_id" class="form-control" required>
                    <option value="">-- Choose Plan --</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['name']) ?> ($<?= $p['min_amount'] ?> - $<?= $p['max_amount'] ?>, <?= $p['interest_rate'] ?>% for <?= $p['duration_days'] ?> days)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount ($)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Invest Now</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
