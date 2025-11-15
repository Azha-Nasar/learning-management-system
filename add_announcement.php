<?php
session_start();
include('dbcon.php');

// ---------------------
// Check login
// ---------------------
$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Get user_id from teacher_id
$user_query = "SELECT user_id FROM teacher WHERE teacher_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['user_id'] ?? null;
$stmt->close();

if (!$user_id) {
    die("Error: User ID not found for this teacher.");
}

// ---------------------
// Handle form submission
// ---------------------
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['message'] ?? '');
    $poster_path = null;

    if ($title && $content) {
        // Poster upload
        if (!empty($_FILES['poster']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $poster_name = time() . '_' . basename($_FILES['poster']['name']);
            $target_file = $target_dir . $poster_name;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $target_file)) {
                $poster_path = $target_file;
            } else {
                $message = "Failed to upload poster image.";
            }
        }

        // Insert announcement with user_id instead of teacher_id
        $stmt = $conn->prepare("
            INSERT INTO announcements (title, message, poster, posted_by, role, created_at) 
            VALUES (?, ?, ?, ?, 'teacher', NOW())
        ");
        $stmt->bind_param("sssi", $title, $content, $poster_path, $user_id);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Announcement posted successfully!";
            header("Location: add_announcement.php");
            exit();
        } else {
            $message = "Database error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// ---------------------
// Handle deletion
// ---------------------
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Delete using user_id
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Announcement deleted successfully!";
    } else {
        $_SESSION['flash_message'] = "Error deleting announcement: " . $stmt->error;
    }
    $stmt->close();
    header("Location: add_announcement.php");
    exit();
}

// ---------------------
// Fetch announcements with user details
// ---------------------
$sql = "SELECT 
          a.id, 
          a.title, 
          a.message, 
          a.poster, 
          a.role, 
          a.created_at, 
          a.posted_by,
          u.firstname,
          u.lastname,
          u.user_type
        FROM announcements a
        JOIN users u ON a.posted_by = u.user_id
        ORDER BY a.created_at DESC";
$announcements = $conn->query($sql);
if (!$announcements) {
    die("Database query failed: " . $conn->error);
}

// ---------------------
// Include layout
// ---------------------
include('teacher_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📢 Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .page-header-simple {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-radius: 0;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #dee2e6;
    }

    .page-header-simple h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .poster-img { 
        max-width: 100px; 
        max-height: 80px; 
        border-radius: 5px;
        object-fit: cover;
    }
    
    .post-btn { 
        background: linear-gradient(135deg, #667eea, #764ba2); 
        color: white; 
        border: none; 
        padding: 8px 15px; 
        border-radius: 5px; 
        transition: all 0.3s ease;
    }
    
    .post-btn:hover { 
        transform: scale(1.05); 
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .table-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-teacher {
        background: #e3f2fd;
        color: #1976d2;
    }

    .badge-admin {
        background: #fce4ec;
        color: #c2185b;
    }
</style>
</head>
<body>
<div class="container-fluid mt-4">
  <div class="page-header-simple">
    <div class="d-flex justify-content-between align-items-center">
      <h4>
        <i class="fas fa-bullhorn me-2"></i>Announcements
      </h4>
      <button class="post-btn" data-bs-toggle="modal" data-bs-target="#announcementModal">
        <i class="fas fa-plus me-2"></i>New Announcement
      </button>
    </div>
  </div>

  <!-- Flash messages -->
  <?php if (!empty($_SESSION['flash_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert" id="flash-msg">
          <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['flash_message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['flash_message']); ?>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert" id="flash-msg">
          <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  <?php endif; ?>

  <div class="table-card">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Title</th>
            <th>Message</th>
            <th>Posted By</th>
            <th>Poster</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($announcements->num_rows === 0): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No announcements posted yet.</td></tr>
        <?php else: ?>
          <?php while ($row = $announcements->fetch_assoc()): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
              <td><?= nl2br(htmlspecialchars(substr($row['message'], 0, 100))) ?><?= strlen($row['message']) > 100 ? '...' : '' ?></td>
              <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
              <td>
                <?php if($row['poster']): ?>
                  <img src="<?= htmlspecialchars($row['poster']) ?>" class="poster-img" alt="Poster">
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="role-badge badge-<?= htmlspecialchars($row['role']) ?>">
                  <?= ucfirst(htmlspecialchars($row['role'])) ?>
                </span>
              </td>
              <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
              <td>
                <?php if ($row['posted_by'] == $user_id): ?>
                  <a href="?delete=<?= $row['id'] ?>" 
                     class="btn btn-danger btn-sm" 
                     onclick="return confirm('Delete this announcement?');">
                    <i class="fas fa-trash"></i>
                  </a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="announcementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-bullhorn me-2"></i>Post New Announcement
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" placeholder="Enter message" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Poster Image (Optional)</label>
            <input type="file" name="poster" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="post-btn">
            <i class="fas fa-paper-plane me-2"></i>Post Announcement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss flash messages after 3 seconds
setTimeout(() => {
    const alert = document.getElementById('flash-msg');
    if(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }
}, 3000);
</script>
</body>
</html>