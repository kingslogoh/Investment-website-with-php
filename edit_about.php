<?php 
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch current about content
$stmt = $pdo->query("SELECT about_text, about_text2, global_customers, years_experience, team_members, about_image FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);

$about_text = $site['about_text'] ?? '';
$about_text2 = $site['about_text2'] ?? '';
$global_customers = $site['global_customers'] ?? 0;
$years_experience = $site['years_experience'] ?? 0;
$team_members = $site['team_members'] ?? 0;
$about_image = $site['about_image'] ?? '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_about_text = $_POST['aboutText'] ?? '';
    $new_about_text2 = $_POST['aboutText2'] ?? '';
    $new_global_customers = $_POST['globalCustomers'] ?? 0;
    $new_years_experience = $_POST['yearsExperience'] ?? 0;
    $new_team_members = $_POST['teamMembers'] ?? 0;
    
    // Handle image upload
    if (!empty($_FILES["aboutImage"]["name"])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["aboutImage"]["name"]);
        move_uploaded_file($_FILES["aboutImage"]["tmp_name"], $target_file);
        $about_image = $target_file;
    }

    // Update about content in the database
    $sql = "UPDATE settings SET about_text = ?, about_text2 = ?, global_customers = ?, years_experience = ?, team_members = ?, about_image = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_about_text, $new_about_text2, $new_global_customers, $new_years_experience, $new_team_members, $about_image]);

    // Redirect to avoid re-submission issues
    header("Location: edit_about.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - Edit About</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-white px-4 py-3">
    <a href="dashboard.php" class="navbar-brand p-0">
        <h3 class="text-primary m-0">Admin Panel</h3>
    </a>
    <a href="logout.php" class="nav-item nav-link">Logout</a>
</nav>

<!-- Sidebar -->
<div class="sidebar bg-dark text-white p-3">
    <h2 class="text-center mb-4">Admin Panel</h2>
    <ul class="nav flex-column">
       
        <li class="nav-item">
            <a href="settings.php" class="nav-link text-white"><i class="fas fa-cogs me-2"></i> Settings</a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <h2 class="text-primary"><i class="fas fa-edit"></i> Edit About</h2>
    <form action="edit_about.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="aboutText" class="form-label">First About Section</label>
            <textarea class="form-control" id="aboutText" name="aboutText" rows="4"><?= htmlspecialchars($about_text); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="aboutText2" class="form-label">Second About Section</label>
            <textarea class="form-control" id="aboutText2" name="aboutText2" rows="4"><?= htmlspecialchars($about_text2); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="globalCustomers" class="form-label">Global Customers</label>
            <input type="number" class="form-control" id="globalCustomers" name="globalCustomers" value="<?= htmlspecialchars($global_customers); ?>">
        </div>
        <div class="mb-3">
            <label for="yearsExperience" class="form-label">Years of Experience</label>
            <input type="number" class="form-control" id="yearsExperience" name="yearsExperience" value="<?= htmlspecialchars($years_experience); ?>">
        </div>
        <div class="mb-3">
            <label for="teamMembers" class="form-label">Team Members</label>
            <input type="number" class="form-control" id="teamMembers" name="teamMembers" value="<?= htmlspecialchars($team_members); ?>">
        </div>
        <div class="mb-3">
            <label for="aboutImage" class="form-label">Upload About Image</label>
            <input type="file" class="form-control" id="aboutImage" name="aboutImage">
            <?php if (!empty($about_image)) : ?>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($about_image); ?>" alt="Current Image" width="150">
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
    </form>
</div>

<!-- Footer -->
<div class="container-fluid footer bg-dark text-white py-4 mt-5">
    <div class="text-center">
        <p>&copy; 2025 Investments. All rights reserved.</p>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
