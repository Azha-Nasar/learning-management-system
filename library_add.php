<?php
require_once 'dbcon.php';
session_start();

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) { 
    header('Location: login.php'); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $desc = trim($_POST['description']);
    $category = trim($_POST['category']);
    $url = trim($_POST['external_url']);
    $file_path = null;

    // ✅ Handle file upload
    if (!empty($_FILES['book_file']['name'])) {
        $uploadDir = "uploads/books/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['book_file']['name']);
        $targetFile = $uploadDir . $fileName;

        $allowed = ['pdf', 'epub', 'docx'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($_FILES['book_file']['tmp_name'], $targetFile)) {
                $file_path = $targetFile;
            } else {
                $error = "File upload failed.";
            }
        } else {
            $error = "Only PDF, EPUB, and DOCX files are allowed.";
        }
    }

    // ✅ Save only if at least one option is filled
    if ($title && ($url || $file_path)) {
        $stmt = $conn->prepare("INSERT INTO library_book (title, author, description, category, external_url, file_path, added_by) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssi", $title, $author, $desc, $category, $url, $file_path, $teacher_id);
        $stmt->execute();
        header("Location: library.php");
        exit;
    } else {
        $error = "Title and either Book Link or File Upload are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Book - EduHub LMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'teacher_layout.php'; ?>

<div class="container py-4">
  <h4><i class="fas fa-plus me-2"></i>Add Online Book</h4> <hr>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  
  <form method="post" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title *</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Author</label>
      <input type="text" name="author" class="form-control">
    </div>
    <div class="col-md-12">
      <label class="form-label">Description</label>
      <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <input type="text" name="category" class="form-control">
    </div>
    
    <div class="col-md-6">
      <label class="form-label">Book Link (URL)</label>
      <input type="url" name="external_url" class="form-control" placeholder="https://...">
    </div>
    
    <div class="col-md-6">
      <label class="form-label">Upload Book (PDF/EPUB/DOCX)</label>
      <input type="file" name="book_file" class="form-control" accept=".pdf,.epub,.docx">
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-success">Save Book</button>
      <a href="library.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>
</body>
</html>
