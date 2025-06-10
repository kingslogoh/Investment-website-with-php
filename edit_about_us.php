<?php
// Connection to database
include'database.php';

// Fetch section to edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM about_us WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $section = $stmt->fetch();
}

// Edit section
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
?>

<h2>Edit Section</h2>
<form method="POST">
    <input type="hidden" name="id" value="<?= $section['id'] ?>">
    <input type="text" name="section_name" value="<?= $section['section_name'] ?>" required><br>
    <textarea name="content" required><?= $section['content'] ?></textarea><br>
    <input type="text" name="image_url" value="<?= $section['image_url'] ?>" placeholder="Image URL (optional)"><br>
    <button type="submit" name="edit_section">Update Section</button>
</form>
