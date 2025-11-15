<?php
session_start();
require_once 'dbcon.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Handle search
$search = trim($_GET['q'] ?? '');
$where = "";
$params = [];
$types  = "";

if ($search !== "") {
    $where  = "WHERE title LIKE CONCAT('%', ?, '%') OR author LIKE CONCAT('%', ?, '%') OR category LIKE CONCAT('%', ?, '%')";
    $params = [$search, $search, $search];
    $types  = "sss";
}

$sql = "SELECT * FROM library_book $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

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
  <style>

    .book-card {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      height: 100%;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      border-left: 4px solid #3498db;
    }

    .book-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }

    .book-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }

    .book-author {
      color: #6c757d;
      font-size: 0.95rem;
      margin-bottom: 0.75rem;
    }

    .book-category {
      display: inline-block;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }

    .book-description {
      font-size: 0.9rem;
      color: #495057;
      line-height: 1.6;
      margin-bottom: 1rem;
      max-height: 80px;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .search-section {
      background: white;
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
<?php include 'student_layout.php'; ?>

<div class="container-fluid mt-4">
  <!-- Header -->
  <div class="page-header-simple">
    <h4>
      <i class="fas fa-book-open me-2"></i>Online Library <hr>
    </h4>
  </div>

  <!-- Search Bar -->
  <div class="search-section">
    <form method="get">
      <div class="input-group">
        <input type="text" 
               name="q" 
               class="form-control form-control-lg" 
               placeholder="Search by title, author, or category..." 
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary px-4" type="submit">
          <i class="fas fa-search me-2"></i>Search
        </button>
        <?php if ($search !== ""): ?>
          <a href="s_library.php" class="btn btn-outline-secondary">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Books Grid -->
  <div class="row g-4">
  <?php if ($res->num_rows > 0): ?>
      <?php while ($book = $res->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
              <div class="book-card">
                  <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                  <div class="book-author">
                    <i class="fas fa-user me-1"></i><?= htmlspecialchars($book['author']) ?>
                  </div>
                  
                  <?php if (!empty($book['category'])): ?>
                    <span class="book-category"><?= htmlspecialchars($book['category']) ?></span>
                  <?php endif; ?>
                  
                  <?php if (!empty($book['description'])): ?>
                    <div class="book-description">
                        <?= nl2br(htmlspecialchars($book['description'])) ?>
                    </div>
                  <?php endif; ?>

                  <div class="d-flex gap-2 mt-auto">
                      <?php if (!empty($book['file_path'])): ?>
                          <a href="<?= htmlspecialchars($book['file_path']) ?>" 
                             download 
                             class="btn btn-success btn-sm flex-fill">
                              <i class="fas fa-download me-2"></i>Download
                          </a>
                      <?php endif; ?>

                      <?php if (!empty($book['external_url'])): ?>
                          <a href="<?= htmlspecialchars($book['external_url']) ?>" 
                             target="_blank" 
                             class="btn btn-primary btn-sm flex-fill">
                              <i class="fas fa-external-link-alt me-2"></i>Read Online
                          </a>
                      <?php endif; ?>
                  </div>
              </div>
          </div>
      <?php endwhile; ?>
  <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info text-center">
          <i class="fas fa-book-reader fa-3x mb-3" style="opacity: 0.3;"></i>
          <h5>No books found</h5>
          <p class="text-muted mb-0">
            <?= $search !== "" ? "Try searching with different keywords." : "No books available in the library yet." ?>
          </p>
        </div>
      </div>
  <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>