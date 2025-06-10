<?php
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch current settings
$stmt = $pdo->query("SELECT id, site_name, site_logo, site_description FROM settings LIMIT 1");
$site = $stmt->fetch(PDO::FETCH_ASSOC);

$site_id = $site['id'] ?? null;
$site_name = $site['site_name'] ?? 'Investment Platform';
$site_logo = $site['site_logo'] ?? 'default-logo.png';
$site_description = $site['site_description'] ?? '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_site_name = $_POST['siteTitle'] ?? $site_name;
    $new_site_description = $_POST['siteDescription'] ?? $site_description;
    
    // Handle logo upload
    if (!empty($_FILES['siteLogo']['name'])) {
        $target_dir = "uploads/";
        $uploaded_file = $target_dir . basename($_FILES["siteLogo"]["name"]);
        move_uploaded_file($_FILES["siteLogo"]["tmp_name"], $uploaded_file);
        $site_logo = $uploaded_file; // Update logo path
    }

    if ($site_id) {
        // Update existing settings
        $sql = "UPDATE settings SET site_name = ?, site_logo = ?, site_description = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_site_name, $site_logo, $new_site_description, $site_id]);
    } else {
        // Insert new settings
        $sql = "INSERT INTO settings (site_name, site_logo, site_description) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_site_name, $site_logo, $new_site_description]);
    }

    // Redirect to refresh the page with updated settings
    header("Location: settings.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel - Settings</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Stylesheets -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

 <!-- Navbar Start -->
   <nav class="navbar navbar-expand-lg navbar-light bg-light px-4">
    <a class="navbar-brand" href="admin.php">
        <img src="<?php echo htmlspecialchars($site_logo); ?>" width="50" alt="Logo">
        <?php echo htmlspecialchars($site_name); ?> Admin
    </a>
    <div class="ms-auto">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</nav>
    <!-- Navbar End -->

<!-- Sidebar Start -->
<div class="sidebar bg-dark text-white p-3">
    <h2 class="text-center mb-4">Admin Panel</h2>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="settings.php" class="nav-link text-white">
                <i class="fas fa-cogs me-2"></i> Settings
            </a>
        </li>
                     <li class="nav-item">
    <a href="edit_home.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit_home page
    </a>
</li> 
                <li class="nav-item">
    <a href="manage_team.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Manage team
    </a>
</li> 
              <li class="nav-item">
    <a href="edit_faq.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit faq
    </a>
</li>   
             <li class="nav-item">
    <a href="admin_testimonials.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit testimonials
    </a>
</li> 
       
        <li class="nav-item">
    <a href="edit_footer.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit Footer
    </a>
</li>
<li class="nav-item">
    <a href="edit_about.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit About
    </a>
</li>
<li class="nav-item">
    <a href="admin_plans.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Edit Plans
    </a>
</li>
<li class="nav-item">
    <a href="admin_payment_settings.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> Payment Settings
    </a>
</li> 
<li class="nav-item">
    <a href="admin_edit_policies.php" class="nav-link text-white">
        <i class="fas fa-edit me-2"></i> edit_terms

    </a>
</li> 
    </ul>
</div>
<!-- Sidebar End -->

<!-- Main Content Start -->
<div class="container mt-4 text-center">
    <h2 class="text-primary"><i class="fas fa-cogs"></i> Settings</h2>
    <p>Manage your website settings here.</p>

    <div class="row justify-content-center">
        <!-- General Settings -->
        <div class="col-md-6 mb-4">
            <div class="card p-4 text-start">
                <h4><i class="fas fa-wrench"></i> General Settings</h4>
                <form action="settings.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="siteTitle" class="form-label">Site Title</label>
                        <input type="text" class="form-control" id="siteTitle" name="siteTitle" value="<?= htmlspecialchars($site_name); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="siteDescription" class="form-label">Site Description</label>
                        <textarea class="form-control" id="siteDescription" name="siteDescription" rows="3"><?= htmlspecialchars($site_description); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="siteLogo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="siteLogo" name="siteLogo">
                        <p>Current Logo: <img src="<?= htmlspecialchars($site_logo); ?>" alt="Logo" width="100"></p>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>

         

        <!-- Appearance Settings -->
     <!--   <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h4><i class="fas fa-paint-brush"></i> Appearance Settings</h4>
                <form>
                    <div class="mb-3">
                        <label for="siteTheme" class="form-label">Site Theme</label>
                        <select class="form-control" id="siteTheme">
                            <option selected>Light Mode</option>
                            <option>Dark Mode</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Save Appearance Settings</button>
                </form>
            </div>
        </div>-->

        <!-- Notifications Settings -->
       <!-- <div class="col-md-6 mb-4">
            <div class="card p-4">
                <h4><i class="fas fa-bell"></i> Notification Settings</h4>
                <form>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                        <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                    </div>
                    <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Save Notification Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>-->
<!-- Main Content End -->

<!-- Footer -->
<div class="container-fluid footer bg-dark text-white py-4 mt-5">
    <div class="text-center">
        <p>&copy; 2025 Investments. All rights reserved.</p>
    </div>
</div>

<!-- JavaScript -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
