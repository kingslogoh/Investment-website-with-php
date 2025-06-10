<?php
session_start();
include 'database.php';
include 'header.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}


// Fetch team members
$sql = "SELECT * FROM team ORDER BY id DESC";
$stmt = $pdo->query($sql);
$team = $stmt->fetchAll();
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
    <h2>Manage Team Members</h2>

    <!-- Add New Team Member Form -->
    <form action="save_team.php" method="post" enctype="multipart/form-data" class="mb-5">
        <h4>Add New Member</h4>
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="position" class="form-label">Position</label>
            <input type="text" class="form-control" id="position" name="position" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <div class="mb-3">
            <label for="facebook" class="form-label">Facebook Link</label>
            <input type="text" class="form-control" id="facebook" name="facebook">
        </div>
        <div class="mb-3">
            <label for="twitter" class="form-label">Twitter Link</label>
            <input type="text" class="form-control" id="twitter" name="twitter">
        </div>
        <div class="mb-3">
            <label for="instagram" class="form-label">Instagram Link</label>
            <input type="text" class="form-control" id="instagram" name="instagram">
        </div>
        <button type="submit" class="btn btn-primary">Add Member</button>
    </form>

    <!-- List of Existing Team Members -->
    <h4>Existing Team Members</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Position</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($team as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['name']); ?></td>
                    <td><?= htmlspecialchars($member['position']); ?></td>
                    <td><img src="./<?= htmlspecialchars($member['image']); ?>" width="50" alt="Image"></td>
                    <td>
                        <a href="delete_team.php?id=<?= $member['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
