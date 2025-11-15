<?php
session_start(); // Start session at the very beginning

include('dbcon.php');

// Get admin user_id from session
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

$message = '';

// Handle announcement creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['message']);
    $poster_path = null;

    if ($title && $content) {
        // Handle poster upload
        if (!empty($_FILES['poster']['name'])) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $poster_name = time() . '_' . basename($_FILES['poster']['name']);
            $target_file = $target_dir . $poster_name;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $target_file)) {
                $poster_path = $target_file;
            }
        }

        $stmt = $conn->prepare("INSERT INTO announcements (title, message, poster, posted_by, role, created_at) VALUES (?, ?, ?, ?, 'admin', NOW())");
        $stmt->bind_param("sssi", $title, $content, $poster_path, $admin_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Announcement posted successfully!";
        } else {
            $_SESSION['error'] = "Failed to post announcement.";
        }
        $stmt->close();
        header("Location: admin_announcements.php");
        exit;
    }
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ii", $delete_id, $admin_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Announcement deleted successfully!";
    }
    $stmt->close();
    header("Location: admin_announcements.php");
    exit;
}

// Fetch all announcements
$announcements_query = "SELECT a.*, u.firstname, u.lastname 
                        FROM announcements a
                        JOIN users u ON a.posted_by = u.user_id
                        ORDER BY a.created_at DESC";
$announcements = $conn->query($announcements_query);

include('admin_layout.php');
?>

<style>
.announcements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.announcement-card {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    border-left: 3px solid #667eea;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.announcement-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.75rem;
}

.announcement-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.announcement-meta {
    font-size: 0.75rem;
    color: #6c757d;
    margin-bottom: 0.75rem;
}

.announcement-content {
    font-size: 0.9rem;
    color: #495057;
    margin-bottom: 0.75rem;
    flex-grow: 1;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
}

.announcement-poster {
    max-width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    margin-top: 0.75rem;
}

.role-badge {
    padding: 0.25rem 0.6rem;
    border-radius: 15px;
    font-size: 0.7rem;
    font-weight: 600;
}

.role-badge.admin {
    background: #fce4ec;
    color: #c2185b;
}

.role-badge.teacher {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4><i class="fas fa-bullhorn me-2"></i>Announcements Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus me-2"></i>New Announcement
        </button>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if ($announcements->num_rows > 0): ?>
    <div class="announcements-grid">
        <?php while($announcement = $announcements->fetch_assoc()): ?>
            <div class="announcement-card">
                <div class="announcement-header">
                    <div style="flex: 1;">
                        <div class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></div>
                        <div class="announcement-meta">
                            <span class="role-badge <?= htmlspecialchars($announcement['role']) ?>">
                                <?= ucfirst(htmlspecialchars($announcement['role'])) ?>
                            </span>
                            <div style="margin-top: 0.25rem;">
                                <?= htmlspecialchars($announcement['firstname'] . ' ' . $announcement['lastname']) ?>
                            </div>
                            <div>
                                <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($announcement['posted_by'] == $admin_id): ?>
                        <a href="?delete=<?= $announcement['id'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this announcement?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="announcement-content"><?= nl2br(htmlspecialchars($announcement['message'])) ?></div>
                <?php if ($announcement['poster']): ?>
                    <img src="<?= htmlspecialchars($announcement['poster']) ?>" 
                         class="announcement-poster" alt="Poster">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i>No announcements posted yet.
    </div>
<?php endif; ?>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Poster Image (Optional)</label>
                        <input type="file" name="poster" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_announcement" class="btn btn-primary">
                        Post Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>