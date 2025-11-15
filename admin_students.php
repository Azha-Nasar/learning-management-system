<?php
// Session and authentication handled by admin_layout.php
include('dbcon.php');

// Handle student deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $student_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT user_id FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    
    if ($student) {
        $stmt = $conn->prepare("DELETE FROM student WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $student['user_id']);
        $stmt->execute();
        
        $_SESSION['success'] = "Student deleted successfully!";
    }
    header("Location: admin_students.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $student_id = $_POST['student_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE student SET status = ? WHERE student_id = ?");
    $stmt->bind_param("si", $status, $student_id);
    $stmt->execute();
    $_SESSION['success'] = "Status updated successfully!";
    header("Location: admin_students.php");
    exit;
}

// Fetch students
$search = $_GET['search'] ?? '';
$class_filter = $_GET['class_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$query = "SELECT s.*, c.class_name FROM student s LEFT JOIN class c ON s.class_id = c.class_id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (s.name LIKE ? OR s.email LIKE ? OR s.student_number LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}
if ($class_filter) {
    $query .= " AND s.class_id = ?";
    $params[] = $class_filter;
    $types .= "i";
}
if ($status_filter) {
    $query .= " AND s.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY s.created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result();

$classes = $conn->query("SELECT * FROM class ORDER BY class_name");
$total_students = $conn->query("SELECT COUNT(*) as count FROM student")->fetch_assoc()['count'];
$active_students = $conn->query("SELECT COUNT(*) as count FROM student WHERE status='Active'")->fetch_assoc()['count'];
$suspended_students = $conn->query("SELECT COUNT(*) as count FROM student WHERE status='Suspended'")->fetch_assoc()['count'];

include('admin_layout.php');
?>

<style>
.page-header {
    background: white;
    padding: 20px 24px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.page-header h4 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
}

.stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
}

.stat-box {
    flex: 1;
    background: white;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-box:nth-child(2) .stat-icon { background: #10b981; }
.stat-box:nth-child(3) .stat-icon { background: #f59e0b; }

.stat-info h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-info p {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}

.filter-bar {
    background: white;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.filter-bar form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-bar input,
.filter-bar select {
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
}

.filter-bar input { flex: 1; min-width: 250px; }

.students-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.student-row {
    background: white;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s;
}

.student-row:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateX(4px);
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}

.student-info {
    flex: 1;
    display: grid;
    grid-template-columns: 2fr 1.5fr 2fr 1.5fr 1fr 0.8fr auto;
    gap: 16px;
    align-items: center;
}

.info-block h5 {
    margin: 0 0 4px 0;
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
}

.info-block p {
    margin: 0;
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.suspended {
    background: #fee2e2;
    color: #991b1b;
}

.action-btns {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.85rem;
}

.action-btn.view { background: #e0e7ff; color: #4338ca; }
.action-btn.edit { background: #fef3c7; color: #92400e; }
.action-btn.delete { background: #fee2e2; color: #991b1b; }

.action-btn:hover {
    transform: scale(1.1);
}

.empty-state {
    background: white;
    padding: 60px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.empty-state h5 {
    margin: 0 0 8px 0;
    color: #475569;
}

.empty-state p {
    margin: 0;
    color: #94a3b8;
}

.modal-content {
    border: none;
    border-radius: 12px;
}

.modal-header {
    background: #667eea;
    color: white;
    border-radius: 12px 12px 0 0;
    padding: 20px 24px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.modal-body {
    padding: 24px;
}

.profile-header {
    text-align: center;
    margin-bottom: 24px;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    margin-bottom: 12px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.detail-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
}

.detail-item label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.detail-item .value {
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
}

@media (max-width: 768px) {
    .stats-row { flex-direction: column; }
    .student-info { 
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <h4>Student Management</h4>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="stats-row">
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $total_students ?></h3>
            <p>Total</p>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <h3><?= $active_students ?></h3>
            <p>Active</p>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-info">
            <h3><?= $suspended_students ?></h3>
            <p>Suspended</p>
        </div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <select name="class_filter">
            <option value="">All Classes</option>
            <?php 
            $classes->data_seek(0);
            while($class = $classes->fetch_assoc()): 
            ?>
                <option value="<?= $class['class_id'] ?>" <?= $class_filter == $class['class_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($class['class_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <select name="status_filter">
            <option value="">All Status</option>
            <option value="Active" <?= $status_filter == 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Suspended" <?= $status_filter == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="admin_students.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="students-container">
    <?php if ($students->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No Students Found</h5>
            <p>Try adjusting your filters</p>
        </div>
    <?php else: ?>
        <?php 
        $students->data_seek(0);
        while($student = $students->fetch_assoc()): 
        ?>
            <div class="student-row">
                <img src="<?= htmlspecialchars($student['profile_image'] ?? 'default.png') ?>" 
                     alt="" class="student-avatar" onerror="this.src='default.png'">
                
                <div class="student-info">
                    <div class="info-block">
                        <h5>Name</h5>
                        <p><?= htmlspecialchars($student['name']) ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Student No</h5>
                        <p><?= htmlspecialchars($student['student_number'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Email</h5>
                        <p><?= htmlspecialchars($student['email']) ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Class</h5>
                        <p><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Enrolled</h5>
                        <p><?= date('M d, Y', strtotime($student['enrollment_date'])) ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Status</h5>
                        <span class="status-badge <?= strtolower($student['status']) ?>">
                            <?= $student['status'] ?>
                        </span>
                    </div>
                    <div class="action-btns">
                        <button class="action-btn view" data-bs-toggle="modal" data-bs-target="#viewModal<?= $student['student_id'] ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit" data-bs-toggle="modal" data-bs-target="#statusModal<?= $student['student_id'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?= $student['student_id'] ?>" class="action-btn delete"
                           onclick="return confirm('Delete this student?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="viewModal<?= $student['student_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Student Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="profile-header">
                                <img src="<?= htmlspecialchars($student['profile_image'] ?? 'default.png') ?>" 
                                     alt="" class="profile-avatar" onerror="this.src='default.png'">
                                <h5><?= htmlspecialchars($student['name']) ?></h5>
                            </div>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Student Number</label>
                                    <div class="value"><?= htmlspecialchars($student['student_number'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Email</label>
                                    <div class="value"><?= htmlspecialchars($student['email']) ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Class</label>
                                    <div class="value"><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Phone</label>
                                    <div class="value"><?= htmlspecialchars($student['phone'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Date of Birth</label>
                                    <div class="value"><?= $student['date_of_birth'] ? date('M d, Y', strtotime($student['date_of_birth'])) : 'N/A' ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Address</label>
                                    <div class="value"><?= htmlspecialchars($student['address'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Enrollment</label>
                                    <div class="value"><?= date('M d, Y', strtotime($student['enrollment_date'])) ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Status</label>
                                    <div class="value">
                                        <span class="status-badge <?= strtolower($student['status']) ?>">
                                            <?= $student['status'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusModal<?= $student['student_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Update Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="student_id" value="<?= $student['student_id'] ?>">
                                <div class="detail-item" style="margin-bottom: 16px;">
                                    <label>Student</label>
                                    <div class="value"><?= htmlspecialchars($student['name']) ?></div>
                                </div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Active" <?= $student['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Suspended" <?= $student['status'] == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>