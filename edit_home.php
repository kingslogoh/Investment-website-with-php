<?php
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Carousel Image Upload with Text Updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['carousel_images'])) {
    if (count($_FILES['carousel_images']['name']) !== 3) {
        echo "<script>alert('Please upload exactly 3 images!'); window.location.href='edit_home.php';</script>";
        exit;
    }

    $target_dir = "uploads/c/";
    $image_names = ["homepage1.jpg", "homepage2.jpg", "homepage3.jpg"];

    foreach ($_FILES['carousel_images']['tmp_name'] as $key => $tmp_name) {
        $target_file = $target_dir . $image_names[$key];
        $first_text = $_POST['first_text'][$key] ?? '';
        $second_text = $_POST['second_text'][$key] ?? '';

        if (move_uploaded_file($tmp_name, $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO carousel_images (image_name, image_path, first_text, second_text) 
                                   VALUES (?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE image_path = VALUES(image_path), 
                                   first_text = VALUES(first_text), 
                                   second_text = VALUES(second_text)");
            $stmt->execute(["homepage" . ($key + 1), $image_names[$key], $first_text, $second_text]);
        } else {
            echo "<script>alert('Error uploading images!');</script>";
        }
    }
    echo "<script>alert('Carousel updated!'); window.location.href='edit_home.php';</script>";
}

// Fetch Carousel Data
$carousel_stmt = $pdo->query("SELECT * FROM carousel_images ORDER BY image_name ASC");
$carousel_images = $carousel_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <!-- Viewport meta tag with smaller initial scale -->
    <meta name="viewport" content="width=device-width, initial-scale=0.8, maximum-scale=0.8, user-scalable=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<div class="container mt-5">
    <h2>Manage Carousel</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="mb-3">
                <label>Carousel <?= $i + 1 ?> Image</label>
                <input type="file" name="carousel_images[]" class="form-control" required>
                
                <label>Carousel <?= $i + 1 ?> First Text</label>
                <input type="text" name="first_text[]" class="form-control" 
                       value="<?= htmlspecialchars($carousel_images[$i]['first_text'] ?? '') ?>" required>
                       
                <label>Carousel <?= $i + 1 ?> Second Text</label>
                <input type="text" name="second_text[]" class="form-control" 
                       value="<?= htmlspecialchars($carousel_images[$i]['second_text'] ?? '') ?>" required>
            </div>
        <?php endfor; ?>
        <button type="submit" class="btn btn-primary">Upload Images & Update Text</button>
    </form>

    <div class="mt-3">
        <?php foreach ($carousel_images as $img): ?>
            <div>
                <img src="uploads/c/<?= htmlspecialchars($img['image_path']) ?>" width="100">
                <p><strong><?= htmlspecialchars($img['first_text']) ?></strong></p>
                <p><?= htmlspecialchars($img['second_text']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
