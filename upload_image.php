<?php
if (!empty($_FILES['file']['name'])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
        echo json_encode(['location' => $targetFile]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'File upload failed']);
    }
}
