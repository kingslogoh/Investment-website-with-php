<?php
session_start();
include 'database.php'; // Database connection
include 'header.php'; // Admin panel header

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission to add or edit a testimonial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $profession = $_POST['profession'];
    $feedback = $_POST['feedback'];
    $testimonial_id = isset($_POST['testimonial_id']) ? (int)$_POST['testimonial_id'] : 0;

    // Handle image upload
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/testimonials/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $image = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    }

    if ($testimonial_id > 0) {
        // Update existing testimonial
        $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, profession = ?, feedback = ?, image = ? WHERE id = ?");
        $stmt->execute([$name, $profession, $feedback, $image, $testimonial_id]);
    } else {
        // Insert new testimonial
        $stmt = $pdo->prepare("INSERT INTO testimonials (name, profession, feedback, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $profession, $feedback, $image]);
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// Fetch all testimonials
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC");
$testimonials = $stmt->fetchAll();
?>
<!-- Sidebar -->
<div class="sidebar bg-dark text-white p-3">
 <h2 class="text-center mb-4">Admin Panel</h2>
    <ul class="nav flex-column">
       
        <li class="nav-item">
            <a href="settings.php" class="nav-link text-white"><i class="fas fa-cogs me-2"></i> Settings</a>
        </li>
    </ul>
</div>
<div class="container py-5">
    <h2>Manage Testimonials</h2>

    <!-- Form to Add/Edit Testimonial -->
    <form action="admin_testimonials.php" method="post" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="testimonial_id" id="testimonial_id">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="profession" class="form-label">Profession</label>
            <input type="text" class="form-control" id="profession" name="profession" required>
        </div>
        <div class="mb-3">
            <label for="feedback" class="form-label">Feedback</label>
            <textarea class="form-control" id="feedback" name="feedback" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image (Optional)</label>
            <input type="file" class="form-control" id="image" name="image">
        </div>
        <button type="submit" class="btn btn-primary">Save Testimonial</button>
    </form>

    <!-- Display Testimonials -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Profession</th>
                <th>Feedback</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($testimonials as $testimonial): ?>
                <tr>
                    <td><?= htmlspecialchars($testimonial['name']) ?></td>
                    <td><?= htmlspecialchars($testimonial['profession']) ?></td>
                    <td><?= htmlspecialchars($testimonial['feedback']) ?></td>
                    <td>
                        <?php if (!empty($testimonial['image'])): ?>
                            <img src="<?= $testimonial['image'] ?>" alt="Image" style="width: 80px;">
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editTestimonial(<?= htmlspecialchars(json_encode($testimonial)) ?>)">Edit</button>
                        <a href="admin_testimonials.php?delete=<?= $testimonial['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Fill the form with testimonial data for editing
function editTestimonial(testimonial) {
    document.getElementById('testimonial_id').value = testimonial.id;
    document.getElementById('name').value = testimonial.name;
    document.getElementById('profession').value = testimonial.profession;
    document.getElementById('feedback').value = testimonial.feedback;
    window.scrollTo(0, 0);
}
</script>

<?php include 'footer.php'; ?>
