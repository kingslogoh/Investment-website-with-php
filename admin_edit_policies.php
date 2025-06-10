<?php
session_start();
include 'database.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $terms = $_POST['terms'];
    $privacy = $_POST['privacy'];

    $stmt = $pdo->prepare("UPDATE site_policies SET terms = ?, privacy = ? WHERE id = 1");
    $stmt->execute([$terms, $privacy]);

    $message = "Updated successfully!";
}

// Fetch existing data
$stmt = $pdo->query("SELECT * FROM site_policies WHERE id = 1");
$policies = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Terms & Privacy Policy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Load TinyMCE from local path -->
 <script src="tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: 'textarea.tinymce-editor',
        height: 400,
        menubar: false,
        plugins: 'lists link image table code preview autosave',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link image table | preview code',
        autosave_interval: '30s',
        autosave_prefix: 'autosave-{path}{query}-{id}-',
        images_upload_url: 'upload_image.php',
        automatic_uploads: true,
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_image.php');
            xhr.onload = function () {
                if (xhr.status !== 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }
                var json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location !== 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                success(json.location);
            };
            var formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }
    });
</script>

</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Edit Terms & Privacy Policy</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="form-label fw-bold">Terms and Conditions</label>
         <textarea name="terms" class="tinymce-editor"><?= htmlspecialchars($policies['terms']) ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold">Privacy Policy</label>
           <textarea name="privacy" class="tinymce-editor"><?= htmlspecialchars($policies['privacy']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <hr class="my-5">

    <!-- Preview Panel -->
    <div class="row mt-5">
        <div class="col-md-6">
            <h4>Terms Preview</h4>
            <div class="border p-3 bg-white" style="min-height: 300px;">
                <?= $policies['terms'] ?>
            </div>
        </div>
        <div class="col-md-6">
            <h4>Privacy Policy Preview</h4>
            <div class="border p-3 bg-white" style="min-height: 300px;">
                <?= $policies['privacy'] ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
