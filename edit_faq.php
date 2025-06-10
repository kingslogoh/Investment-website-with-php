<?php
session_start();
include 'database.php';
include 'header.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Add a new FAQ
if (isset($_POST['add_faq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (:question, :answer)");
    $stmt->execute(['question' => $question, 'answer' => $answer]);
}

// Update an FAQ
if (isset($_POST['update_faq'])) {
    $id = intval($_POST['id']);
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $stmt = $pdo->prepare("UPDATE faqs SET question = :question, answer = :answer WHERE id = :id");
    $stmt->execute(['question' => $question, 'answer' => $answer, 'id' => $id]);
}

// Delete an FAQ
if (isset($_POST['delete_faq'])) {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

// Fetch all FAQs
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll();
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
    <h1 class="mb-4">Manage FAQs</h1>
    
    <!-- Add New FAQ Form -->
    <h3>Add New FAQ</h3>
    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="question" class="form-label">Question</label>
            <input type="text" name="question" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="answer" class="form-label">Answer</label>
            <textarea name="answer" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" name="add_faq" class="btn btn-success">Add FAQ</button>
    </form>

    <!-- Display Existing FAQs -->
    <h3>Existing FAQs</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faqs as $row): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['question']); ?></td>
                    <td><?php echo htmlspecialchars($row['answer']); ?></td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                        
                        <!-- Delete Form -->
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_faq" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit FAQ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <div class="mb-3">
                                        <label for="question" class="form-label">Question</label>
                                        <input type="text" name="question" class="form-control" value="<?php echo htmlspecialchars($row['question']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="answer" class="form-label">Answer</label>
                                        <textarea name="answer" class="form-control" rows="4" required><?php echo htmlspecialchars($row['answer']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_faq" class="btn btn-primary">Update FAQ</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
