<?php
// Session and authentication handled by admin_layout.php
include('dbcon.php');

// Handle teacher addition
if (isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $employee_id = $_POST['employee_id'];
    $department = $_POST['department'];
    $phone = $_POST['phone'];
    $office_location = $_POST['office_location'];
    $specialization = $_POST['specialization'];
    $hire_date = $_POST['hire_date'];
    $status = $_POST['status'];
    
    // Split name into firstname and lastname
    $name_parts = explode(' ', $name, 2);
    $firstname = $name_parts[0];
    $lastname = isset($name_parts[1]) ? $name_parts[1] : '';
    
    // First, create user account
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, firstname, lastname, user_type, status) VALUES (?, ?, ?, ?, ?, 'teacher', 'active')");
    
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: admin_teachers.php");
        exit;
    }
    
    $stmt->bind_param("sssss", $email, $email, $password, $firstname, $lastname);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        
        // Then create teacher record
        $stmt = $conn->prepare("INSERT INTO teacher (user_id, name, email, password, employee_id, department, phone, office_location, specialization, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: admin_teachers.php");
            exit;
        }
        
        $stmt->bind_param("issssssssss", $user_id, $name, $email, $password, $employee_id, $department, $phone, $office_location, $specialization, $hire_date, $status);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Teacher added successfully!";
        } else {
            $_SESSION['error'] = "Error adding teacher: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "Error creating user account: " . $stmt->error;
    }
    
    header("Location: admin_teachers.php");
    exit;
}

// Handle teacher deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $teacher_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT user_id FROM teacher WHERE teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();
    
    if ($teacher) {
        $stmt = $conn->prepare("DELETE FROM teacher WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $teacher['user_id']);
        $stmt->execute();
        
        $_SESSION['success'] = "Teacher deleted successfully!";
    }
    header("Location: admin_teachers.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $teacher_id = $_POST['teacher_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE teacher SET status = ? WHERE teacher_id = ?");
    $stmt->bind_param("si", $status, $teacher_id);
    $stmt->execute();
    $_SESSION['success'] = "Status updated successfully!";
    header("Location: admin_teachers.php");
    exit;
}

// Fetch teachers
$search = $_GET['search'] ?? '';
$department_filter = $_GET['department_filter'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

$query = "SELECT t.* FROM teacher t WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (t.name LIKE ? OR t.email LIKE ? OR t.employee_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}
if ($department_filter) {
    $query .= " AND t.department = ?";
    $params[] = $department_filter;
    $types .= "s";
}
if ($status_filter) {
    $query .= " AND t.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY t.created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$teachers = $stmt->get_result();

// Get unique departments for filter
$departments_result = $conn->query("SELECT DISTINCT department FROM teacher WHERE department IS NOT NULL AND department != '' ORDER BY department");
$departments = $departments_result ? $departments_result : [];

// Get statistics with error handling
$total_result = $conn->query("SELECT COUNT(*) as count FROM teacher");
$total_teachers = $total_result ? $total_result->fetch_assoc()['count'] : 0;

$active_result = $conn->query("SELECT COUNT(*) as count FROM teacher WHERE status='Active'");
$active_teachers = $active_result ? $active_result->fetch_assoc()['count'] : 0;

$inactive_result = $conn->query("SELECT COUNT(*) as count FROM teacher WHERE status='Inactive'");
$inactive_teachers = $inactive_result ? $inactive_result->fetch_assoc()['count'] : 0;

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

.page-header .btn-primary {
    background: #667eea;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.page-header .btn-primary:hover {
    background: #5568d3;
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
.stat-box:nth-child(3) .stat-icon { background: #ef4444; }

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

.teachers-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.teacher-row {
    background: white;
    padding: 16px 20px;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s;
}

.teacher-row:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateX(4px);
}

.teacher-avatar {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
}

.teacher-info {
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

.status-badge.inactive {
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
    .teacher-info { 
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h4 style="margin: 0;">Teacher Management</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal" style="white-space: nowrap;">
            <i class="fas fa-plus"></i> Add Teacher
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

<div class="stats-row">
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="stat-info">
            <h3><?= $total_teachers ?></h3>
            <p>Total</p>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <h3><?= $active_teachers ?></h3>
            <p>Active</p>
        </div>
    </div>
    <div class="stat-box">
        <div class="stat-icon"><i class="fas fa-times"></i></div>
        <div class="stat-info">
            <h3><?= $inactive_teachers ?></h3>
            <p>Inactive</p>
        </div>
    </div>
</div>

<div class="filter-bar">
    <form method="GET">
        <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
        <select name="department_filter">
            <option value="">All Departments</option>
            <?php 
            if ($departments && $departments->num_rows > 0):
                $departments->data_seek(0);
                while($dept = $departments->fetch_assoc()): 
            ?>
                <option value="<?= htmlspecialchars($dept['department']) ?>" <?= $department_filter == $dept['department'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dept['department']) ?>
                </option>
            <?php 
                endwhile;
            endif; 
            ?>
        </select>
        <select name="status_filter">
            <option value="">All Status</option>
            <option value="Active" <?= $status_filter == 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= $status_filter == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="admin_teachers.php" class="btn btn-secondary">Reset</a>
    </form>
</div>

<div class="teachers-container">
    <?php if ($teachers->num_rows === 0): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h5>No Teachers Found</h5>
            <p>Try adjusting your filters</p>
        </div>
    <?php else: ?>
        <?php 
        $teachers->data_seek(0);
        while($teacher = $teachers->fetch_assoc()): 
        ?>
            <div class="teacher-row">
                <img src="<?= htmlspecialchars($teacher['profile_image'] ?? 'default.png') ?>" 
                     alt="" class="teacher-avatar" onerror="this.src='default.png'">
                
                <div class="teacher-info">
                    <div class="info-block">
                        <h5>Name</h5>
                        <p><?= htmlspecialchars($teacher['name']) ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Employee ID</h5>
                        <p><?= htmlspecialchars($teacher['employee_id'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Email</h5>
                        <p><?= htmlspecialchars($teacher['email']) ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Department</h5>
                        <p><?= htmlspecialchars($teacher['department'] ?? 'N/A') ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Joined</h5>
                        <p><?= isset($teacher['hire_date']) ? date('M d, Y', strtotime($teacher['hire_date'])) : 'N/A' ?></p>
                    </div>
                    <div class="info-block">
                        <h5>Status</h5>
                        <span class="status-badge <?= strtolower($teacher['status'] ?? 'active') ?>">
                            <?= $teacher['status'] ?? 'Active' ?>
                        </span>
                    </div>
                    <div class="action-btns">
                        <button class="action-btn view" data-bs-toggle="modal" data-bs-target="#viewModal<?= $teacher['teacher_id'] ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit" data-bs-toggle="modal" data-bs-target="#statusModal<?= $teacher['teacher_id'] ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?= $teacher['teacher_id'] ?>" class="action-btn delete"
                           onclick="return confirm('Delete this teacher?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="viewModal<?= $teacher['teacher_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Teacher Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="profile-header">
                                <img src="<?= htmlspecialchars($teacher['profile_image'] ?? 'default.png') ?>" 
                                     alt="" class="profile-avatar" onerror="this.src='default.png'">
                                <h5><?= htmlspecialchars($teacher['name']) ?></h5>
                            </div>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Employee ID</label>
                                    <div class="value"><?= htmlspecialchars($teacher['employee_id'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Email</label>
                                    <div class="value"><?= htmlspecialchars($teacher['email']) ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Department</label>
                                    <div class="value"><?= htmlspecialchars($teacher['department'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Phone</label>
                                    <div class="value"><?= htmlspecialchars($teacher['phone'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Specialization</label>
                                    <div class="value"><?= htmlspecialchars($teacher['specialization'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Office Location</label>
                                    <div class="value"><?= htmlspecialchars($teacher['office_location'] ?? 'N/A') ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Hire Date</label>
                                    <div class="value"><?= isset($teacher['hire_date']) ? date('M d, Y', strtotime($teacher['hire_date'])) : 'N/A' ?></div>
                                </div>
                                <div class="detail-item">
                                    <label>Status</label>
                                    <div class="value">
                                        <span class="status-badge <?= strtolower($teacher['status'] ?? 'active') ?>">
                                            <?= $teacher['status'] ?? 'Active' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusModal<?= $teacher['teacher_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Update Status</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="teacher_id" value="<?= $teacher['teacher_id'] ?>">
                                <div class="detail-item" style="margin-bottom: 16px;">
                                    <label>Teacher</label>
                                    <div class="value"><?= htmlspecialchars($teacher['name']) ?></div>
                                </div>
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Active" <?= ($teacher['status'] ?? 'Active') == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($teacher['status'] ?? 'Active') == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
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

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Office Location</label>
                            <input type="text" name="office_location" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Hire Date *</label>
                            <input type="date" name="hire_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>