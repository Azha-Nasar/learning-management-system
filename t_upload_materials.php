<?php
session_start();
include('dbcon.php');

// Check teacher login
$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Initialize message
$message = '';

// Fetch classes assigned to this teacher
$teacherClassesStmt = $conn->prepare("
    SELECT c.class_id, c.class_name 
    FROM teacher_class tc
    JOIN class c ON tc.class_id = c.class_id
    WHERE tc.teacher_id = ?
");
$teacherClassesStmt->bind_param("i", $teacher_id);
$teacherClassesStmt->execute();
$classResult = $teacherClassesStmt->get_result();

// DELETE FILE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT file_path FROM files WHERE file_id = ? AND uploaded_by = ?");
    $stmt->bind_param("ii", $delete_id, $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $file = $res->fetch_assoc();
        if (file_exists($file['file_path'])) unlink($file['file_path']);

        $del = $conn->prepare("DELETE FROM files WHERE file_id = ? AND uploaded_by = ?");
        $del->bind_param("ii", $delete_id, $teacher_id);
        $del->execute();

        $_SESSION['message'] = "File deleted successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['message'] = "File not found or unauthorized.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// UPLOAD FILE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customFileName = trim($_POST['file_name_input'] ?? '');
    $description = trim($_POST['description_input'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);

    // Validate input
    if ($customFileName === '') {
        $message = "Please enter a file name.";
    } elseif ($class_id === 0) {
        $message = "Please select a class.";
    } elseif (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
        $message = "Please select a file to upload.";
    } else {
        $fileTmpPath = $_FILES['material_file']['tmp_name'];
        $originalFileName = basename($_FILES['material_file']['name']);
        $ext = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $allowed = ['pdf','docx','pptx','zip','txt','jpg','png'];
        
        if (!in_array(strtolower($ext), $allowed)) {
            $message = "Invalid file type. Allowed: " . implode(", ", $allowed);
        } else {
            $safeFileName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $customFileName) . '.' . $ext;
            $uploadDir = 'uploads/materials/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $destPath = $uploadDir . time() . '_' . $safeFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmt = $conn->prepare("INSERT INTO files (file_name, file_path, uploaded_by, class_id, upload_date, description) VALUES (?, ?, ?, ?, NOW(), ?)");
                $stmt->bind_param("ssiis", $customFileName, $destPath, $teacher_id, $class_id, $description);
                if ($stmt->execute()) {
                    $_SESSION['message'] = "File uploaded successfully.";
                    header("Location: " . $_SERVER['PHP_SELF']); // Prevent resubmission
                    exit;
                } else {
                    $message = "Database error: " . $conn->error;
                    unlink($destPath);
                }
            } else {
                $message = "Error moving uploaded file.";
            }
        }
    }
}

// Fetch files uploaded by this teacher
$stmt = $conn->prepare("
    SELECT f.*, c.class_name 
    FROM files f 
    JOIN class c ON f.class_id = c.class_id 
    WHERE f.uploaded_by = ? 
    ORDER BY f.upload_date DESC
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$files = $stmt->get_result();

// Include layout after processing headers
include('teacher_layout.php');
?>

<div class="container mt-0">
    <div class="d-flex justify-content-between align-items-center">
        <h4>📚 Upload Materials</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">➕ Upload File</button>
    </div>
    <hr>

    <?php
    $message = $_SESSION['message'] ?? $message;
    unset($_SESSION['message']);
    ?>
    <?php if ($message): ?>
        <div class="alert alert-success mt-2" id="flash-message"><?= htmlspecialchars($message) ?></div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('flash-message');
                if (msg) msg.style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <div class="card p-2 mt-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-secondary">
                <tr>
                    <th>File Name</th>
                    <th>Class</th>
                    <th>Description</th>
                    <th>Upload Date</th>
                    <th>Download</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($files->num_rows === 0): ?>
                <tr><td colspan="6" class="text-center">No files uploaded.</td></tr>
            <?php else: ?>
                <?php while ($file = $files->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($file['file_name']) ?></td>
                        <td><?= htmlspecialchars($file['class_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($file['description'])) ?></td>
                        <td><?= htmlspecialchars($file['upload_date']) ?></td>
                        <td><a href="<?= htmlspecialchars($file['file_path']) ?>" download class="btn btn-success btn-sm">Download</a></td>
                        <td><a href="?delete=<?= $file['file_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this file?');">Delete</a></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file_name_input" class="form-label">File Name</label>
                        <input type="text" name="file_name_input" id="file_name_input" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="class_id" class="form-label">Select Class</label>
                        <select name="class_id" id="class_id" class="form-control" required>
                            <option value="">-- Select Your Class --</option>
                            <?php
                            mysqli_data_seek($classResult, 0);
                            while ($class = $classResult->fetch_assoc()):
                            ?>
                                <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description_input" class="form-label">Description</label>
                        <textarea name="description_input" id="description_input" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="material_file" class="form-label">Select File</label>
                        <input type="file" name="material_file" id="material_file" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
