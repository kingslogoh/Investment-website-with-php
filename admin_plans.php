<?php
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Delete plan
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM investment_plans WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $success = "Plan deleted.";
}

// Fetch site settings
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Add or update plan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_id'])) {
        // Update
        $stmt = $pdo->prepare("UPDATE investment_plans SET name=?, min_amount=?, max_amount=?, interest_rate=?, duration_days=? WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['min_amount'],
            $_POST['max_amount'],
            $_POST['interest_rate'],
            $_POST['duration_days'],
            $_POST['edit_id']
        ]);
        $success = "Plan updated successfully.";
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO investment_plans (name, min_amount, max_amount, interest_rate, duration_days) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['min_amount'],
            $_POST['max_amount'],
            $_POST['interest_rate'],
            $_POST['duration_days']
        ]);
        $success = "Plan added successfully.";
    }
}

// Fetch plans
$plans = $pdo->query("SELECT * FROM investment_plans")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Investment Plans</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-3">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?= htmlspecialchars($site_logo) ?>" width="50" alt="Logo">
        <?= htmlspecialchars($site_name) ?>
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>
    <div class="container">
        <h2 class="mb-4">Manage Investment Plans</h2>

        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

        <form method="POST" class="mb-4">
            <input type="hidden" name="edit_id" id="edit_id">
            <div class="row row-cols-1 row-cols-md-6 g-2">
                <div class="col">
                    <input class="form-control" name="name" id="name" placeholder="Plan Name" required>
                </div>
                <div class="col">
                    <input class="form-control" type="number" step="0.01" name="min_amount" id="min_amount" placeholder="Min Amount" required>
                </div>
                <div class="col">
                    <input class="form-control" type="number" step="0.01" name="max_amount" id="max_amount" placeholder="Max Amount" required>
                </div>
                <div class="col">
                    <input class="form-control" type="number" step="0.01" name="interest_rate" id="interest_rate" placeholder="Interest %" required>
                </div>
                <div class="col">
                    <input class="form-control" type="number" name="duration_days" id="duration_days" placeholder="Duration (days)" required>
                </div>
                <div class="col d-grid">
                    <button class="btn btn-primary" type="submit">Save Plan</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Min</th>
                        <th>Max</th>
                        <th>Rate (%)</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?= htmlspecialchars($plan['name']) ?></td>
                            <td>$<?= htmlspecialchars($plan['min_amount']) ?></td>
                            <td>$<?= htmlspecialchars($plan['max_amount']) ?></td>
                            <td><?= htmlspecialchars($plan['interest_rate']) ?>%</td>
                            <td><?= htmlspecialchars($plan['duration_days']) ?> days</td>
                            <td>
                                <button class="btn btn-sm btn-warning mb-1" onclick='editPlan(<?= json_encode($plan) ?>)'>Edit</button>
                                <a href="?delete=<?= $plan['id'] ?>" onclick="return confirm('Delete this plan?')" class="btn btn-sm btn-danger mb-1">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function editPlan(plan) {
        document.getElementById('edit_id').value = plan.id;
        document.getElementById('name').value = plan.name;
        document.getElementById('min_amount').value = plan.min_amount;
        document.getElementById('max_amount').value = plan.max_amount;
        document.getElementById('interest_rate').value = plan.interest_rate;
        document.getElementById('duration_days').value = plan.duration_days;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    </script>
</body>
</html>
