<?php
include 'database.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $pdo->prepare("SELECT image FROM team WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $member = $stmt->fetch();

    if ($member) {
        $image_path = '../' . $member['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        $stmt = $pdo->prepare("DELETE FROM team WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header("Location: team.php");
        exit;
    } else {
        echo "Member not found.";
    }
}
?>
