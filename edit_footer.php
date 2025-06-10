<?php 
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch current footer settings
$stmt = $pdo->query("SELECT * FROM footer_settings WHERE id = 1");
$footer = $stmt->fetch(PDO::FETCH_ASSOC);

// Decode JSON fields properly
$newsletter_text = $footer['newsletter_text'] ?? '';
$explore_links = !empty($footer['explore_links']) ? json_decode($footer['explore_links'], true) : [];
$contact_info = !empty($footer['contact_info']) ? json_decode($footer['contact_info'], true) : [];
$social_links = !empty($footer['social_links']) ? json_decode($footer['social_links'], true) : [];
$popular_posts = !empty($footer['popular_posts']) ? json_decode($footer['popular_posts'], true) : [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_newsletter_text = $_POST['newsletter_text'] ?? '';

    // Decode JSON before storing
    $new_explore_links = isset($_POST['explore_links']) ? json_decode($_POST['explore_links'], true) : [];
    $new_contact_info = isset($_POST['contact_info']) ? json_decode($_POST['contact_info'], true) : [];
    $new_social_links = isset($_POST['social_links']) ? json_decode($_POST['social_links'], true) : [];
    $new_popular_posts = isset($_POST['popular_posts']) ? json_decode($_POST['popular_posts'], true) : [];

    // Ensure valid JSON format before saving
    $new_explore_links = json_encode($new_explore_links, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $new_contact_info = json_encode($new_contact_info, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $new_social_links = json_encode($new_social_links, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $new_popular_posts = json_encode($new_popular_posts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    // Update database
    $sql = "UPDATE footer_settings SET 
                newsletter_text = ?, 
                explore_links = ?, 
                contact_info = ?, 
                social_links = ?, 
                popular_posts = ? 
            WHERE id = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_newsletter_text, $new_explore_links, $new_contact_info, $new_social_links, $new_popular_posts]);

    // Redirect to refresh the page with updated settings
    header("Location: edit_footer.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Footer</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar bg-dark text-white p-3">
 <h2 class="text-center mb-4">Admin Panel</h2>
    <ul class="nav flex-column">
       
        <li class="nav-item">
            <a href="settings.php" class="nav-link text-white"><i class="fas fa-cogs me-2"></i> Settings</a>
        </li>
    </ul>
</div>
<div class="container mt-4">
    <!-- Back to Admin Settings Button -->
    <a href="settings.php" class="btn btn-secondary mb-3">← Back to Settings</a>

    <h2>Edit Footer Settings</h2>

    <form action="edit_footer.php" method="post">
        <!-- Explore Links -->
        <div class="mb-3">
            <label class="form-label">Explore Links</label>
            <textarea class="form-control" name="explore_links"><?= htmlspecialchars(json_encode($explore_links, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); ?></textarea>
            <small>Enter JSON format: [{"text": "Home", "url": "investment/index.php"}, ...]</small>
        </div>

        <!-- Contact Info -->
        <div class="mb-3">
            <label class="form-label">Contact Info</label>
            <textarea class="form-control" name="contact_info"><?= htmlspecialchars(json_encode($contact_info, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); ?></textarea>
            <small>Enter JSON format: [{"icon": "fas fa-map-marker-alt", "text": "123 Street, New York"}]</small>
        </div>

        <!-- Social Links -->
        <div class="mb-3">
            <label class="form-label">Social Links</label>
            <textarea class="form-control" name="social_links"><?= htmlspecialchars(json_encode($social_links, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); ?></textarea>
            <small>Enter JSON format: [{"icon": "fab fa-facebook", "url": "https://facebook.com"}]</small>
        </div>

        <!-- Popular Posts -->
        <div class="mb-3">
            <label class="form-label">Popular Posts</label>
            <textarea class="form-control" name="popular_posts"><?= htmlspecialchars(json_encode($popular_posts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)); ?></textarea>
            <small>Enter JSON format: [{"category": "Investment", "title": "Revisiting Your Investment"}]</small>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
