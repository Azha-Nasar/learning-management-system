<?php
session_start();
require_once 'dbcon.php';

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Handle search
$search = trim($_GET['q'] ?? '');
$where = "";
$params = [];
$types  = "";

if ($search !== "") {
    $where  = "WHERE title LIKE CONCAT('%', ?, '%') OR author LIKE CONCAT('%', ?, '%')";
    $params = [$search, $search];
    $types  = "ss";
}

$sql = "SELECT * FROM library_book $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>EduHub LMS - Online Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include 'teacher_layout.php'; ?>

<div class="container py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fas fa-book-open me-2"></i> Online Library</h4> 
    <hr>
    <a href="library_add.php" class="btn btn-success">
      <i class="fas fa-plus me-2"></i>Add Book
    </a>
  </div>

  <!-- Search Bar -->
  <form method="get" class="mb-4 d-flex">
    <input type="text" 
           name="q" 
           class="form-control me-2" 
           placeholder="Search by title or author" 
           value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
  </form>

  <!-- Books Grid -->
<div class="row g-4">
<?php if ($res->num_rows > 0): ?>
    <?php while ($book = $res->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-80 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <h6 class="text-muted"><?= htmlspecialchars($book['author']) ?></h6>
                    <p class="card-text small flex-grow-2">
                        <?= nl2br(htmlspecialchars($book['description'])) ?>
                    </p>

                    <div class="mt-auto d-flex gap-5">
                        <?php if (!empty($book['file_path'])): ?>
                            <a href="<?= htmlspecialchars($book['file_path']) ?>" 
                               download 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download me-2"></i> Download
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($book['external_url'])): ?>
                            <a href="<?= htmlspecialchars($book['external_url']) ?>" 
                               target="_blank" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-up-right-from-square me-2"></i> Read Book
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="col-12 text-center text-muted">
        <i class="fas fa-circle-info me-2"></i> No books found.
    </div>
<?php endif; ?>
</div>
