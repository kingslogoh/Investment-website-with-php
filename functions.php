<?php
function distributeProfitsOnce($pdo) {
    $stmt = $pdo->query("SELECT last_run FROM profit_distribution_log WHERE id = 1");
    $lastRun = $stmt->fetchColumn();

    $now = new DateTime();
    $lastRunTime = new DateTime($lastRun);
    $interval = $now->getTimestamp() - $lastRunTime->getTimestamp();

    if ($interval < 900) return; // Skip if last run was < 15 minutes ago

    // Fetch all matured investments
    $stmt = $pdo->prepare("SELECT * FROM investments WHERE status = 'approved' AND maturity_date <= ?");
    $stmt->execute([date('Y-m-d H:i:s')]);
    $matured = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matured as $inv) {
        $totalReturn = $inv['amount'] + $inv['profit']; // Principal + Interest

        // Add total return to user balance
        $pdo->prepare("UPDATE user_balance SET balance = balance + ? WHERE user_id = ?")
            ->execute([$totalReturn, $inv['user_id']]);

        // Mark investment as completed
        $pdo->prepare("UPDATE investments SET status = 'completed' WHERE id = ?")
            ->execute([$inv['id']]);
    }

    // Update last run time
    $pdo->prepare("UPDATE profit_distribution_log SET last_run = ? WHERE id = 1")
        ->execute([date('Y-m-d H:i:s')]);
}
?>