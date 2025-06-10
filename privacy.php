<?php
include 'database.php';

// Fetch site name
$site_stmt = $pdo->query("SELECT site_name FROM settings LIMIT 1");
$site = $site_stmt->fetch(PDO::FETCH_ASSOC);

// Fetch site name and logo from database
$stmt = $pdo->query("SELECT site_name, site_logo FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';

// Fetch privacy policy
$stmt = $pdo->query("SELECT privacy FROM site_policies WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - <?= htmlspecialchars($site['site_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include"header.php";?>
    <div class="container py-5">
   <h3 class="text-primary m-0"><a href="./index.php"> <img src="<?php echo htmlspecialchars($site_logo); ?>" alt="Logo" width="100"></a>
 <?php echo htmlspecialchars($site_name); ?></h3>
        <h1 class="mb-4 text-center">Privacy Policy</h1>
        <div class="bg-white p-4 shadow rounded">
            <?= $row['privacy'] ?>
        </div>
        
    </div>
<?php include"footer.php";?>