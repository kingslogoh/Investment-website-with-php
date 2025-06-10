<?php
include 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $facebook = $_POST['facebook'] ?? null;
    $twitter = $_POST['twitter'] ?? null;
    $instagram = $_POST['instagram'] ?? null;

    $target_dir = "uploads/team/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image = $_FILES['image']['name'];
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = "uploads/team/" . $image;

        $sql = "INSERT INTO team (name, position, image, facebook, twitter, instagram) 
                VALUES (:name, :position, :image, :facebook, :twitter, :instagram)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':position' => $position,
            ':image' => $image_path,
            ':facebook' => $facebook,
            ':twitter' => $twitter,
            ':instagram' => $instagram,
        ]);

        header("Location: team.php");
        exit;
    } else {
        echo "Failed to upload image.";
    }
}
?>
