<?php
// Connection to database (replace with your actual database connection details)
include'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Add Section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_section'])) {
    $section_name = $_POST['section_name'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];

    $query = "INSERT INTO about_us (section_name, content, image_url) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$section_name, $content, $image_url]);
    echo "Section added successfully!";
}

// Delete Section
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query = "DELETE FROM about_us WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    echo "Section deleted successfully!";
}

// Edit Section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_section'])) {
    $id = $_POST['id'];
    $section_name = $_POST['section_name'];
    $content = $_POST['content'];
    $image_url = $_POST['image_url'];

    $query = "UPDATE about_us SET section_name = ?, content = ?, image_url = ? WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$section_name, $content, $image_url, $id]);
    echo "Section updated successfully!";
}

// Fetch current sections
$query = "SELECT * FROM about_us";
$stmt = $pdo->query($query);
$sections = $stmt->fetchAll();
?>

<h2>Manage About Us Page</h2>

<!-- Add Section Form -->
<h3>Add New Section</h3>
<form method="POST">
    <input type="text" name="section_name" placeholder="Section Name" required><br>
    <textarea name="content" placeholder="Content" required></textarea><br>
    <input type="text" name="image_url" placeholder="Image URL (optional)"><br>
    <button type="submit" name="add_section">Add Section</button>
</form>

<h3>Existing Sections</h3>
<table>
    <tr>
        <th>Section Name</th>
        <th>Content</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($sections as $section): ?>
        <tr>
            <td><?= $section['section_name'] ?></td>
            <td><?= $section['content'] ?></td>
            <td>
                <a href="edit_about_us.php?id=<?= $section['id'] ?>">Edit</a> | 
                <a href="?delete=<?= $section['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
