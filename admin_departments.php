<?php
session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// Handle department creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_department'])) {
    $dept_name = trim($_POST['department_name']);
    $dean = trim($_POST['dean']);

    if ($dept_name && $dean) {
        $stmt = $conn->prepare("INSERT INTO department (department_name, dean) VALUES (?, ?)");
        $stmt->bind_param("ss", $dept_name, $dean);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Department created successfully!";
        } else {
            $_SESSION['error'] = "Failed to create department.";
        }
        $stmt->close();
        header("Location: admin_departments.php");
        exit;
    }
}

// Handle department update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_department'])) {
    $dept_id = $_POST['department_id'];
    $dept_name = trim($_POST['department_name']);
    $dean = trim($_POST['dean']);

    if ($dept_id && $dept_name && $dean) {
        $stmt = $conn->prepare("UPDATE department SET department_name = ?, dean = ? WHERE department_id = ?");
        $stmt->bind_param("ssi", $dept_name, $dean, $dept_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Department updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update department.";
        }
        $stmt->close();
        header("Location: admin_departments.php");
        exit;
    }
}

// Handle adding lecturer to department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lecturer'])) {
    $dept_id = $_POST['department_id'];
    $lecturer_id = $_POST['lecturer_id'];

    if ($dept_id && $lecturer_id) {
        // Get department name
        $dept_stmt = $conn->prepare("SELECT department_name FROM department WHERE department_id = ?");
        $dept_stmt->bind_param("i", $dept_id);
        $dept_stmt->execute();
        $dept_result = $dept_stmt->get_result();
        $dept_name = $dept_result->fetch_assoc()['department_name'];
        $dept_stmt->close();

        // Update lecturer's department
        $stmt = $conn->prepare("UPDATE teacher SET department = ? WHERE teacher_id = ?");
        $stmt->bind_param("si", $dept_name, $lecturer_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Lecturer added to department successfully!";
        } else {
            $_SESSION['error'] = "Failed to add lecturer to department.";
        }
        $stmt->close();
        header("Location: admin_departments.php");
        exit;
    }
}

// Handle removing lecturer from department
if (isset($_GET['remove_lecturer']) && is_numeric($_GET['remove_lecturer'])) {
    $lecturer_id = intval($_GET['remove_lecturer']);
    $stmt = $conn->prepare("UPDATE teacher SET department = NULL WHERE teacher_id = ?");
    $stmt->bind_param("i", $lecturer_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Lecturer removed from department successfully!";
    } else {
        $_SESSION['error'] = "Failed to remove lecturer.";
    }
    $stmt->close();
    header("Location: admin_departments.php");
    exit;
}

// Handle deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if department has lecturers
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher WHERE department = (SELECT department_name FROM department WHERE department_id = ?)");
    $check_stmt->bind_param("i", $delete_id);
    $check_stmt->execute();
    $lecturer_count = $check_stmt->get_result()->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($lecturer_count > 0) {
        $_SESSION['error'] = "Cannot delete department with lecturers. Please remove all lecturers first.";
    } else {
        $stmt = $conn->prepare("DELETE FROM department WHERE department_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Department deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete department.";
        }
        $stmt->close();
    }
    header("Location: admin_departments.php");
    exit;
}

// Fetch all departments with statistics
$departments_query = "SELECT d.*, 
                      (SELECT COUNT(*) FROM teacher WHERE department = d.department_name) as lecturer_count
                      FROM department d
                      ORDER BY d.department_name ASC";
$departments = $conn->query($departments_query);

// Get total counts
$total_departments = $conn->query("SELECT COUNT(*) as count FROM department")->fetch_assoc()['count'];
$total_lecturers = $conn->query("SELECT COUNT(*) as count FROM teacher")->fetch_assoc()['count'];

// Fetch unassigned lecturers for dropdown
$unassigned_lecturers = $conn->query("SELECT teacher_id, teacher_name FROM teacher WHERE department IS NULL OR department = '' ORDER BY teacher_name ASC");

include('admin_layout.php');
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 3px solid;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.stat-card.blue { border-color: #667eea; }
.stat-card.green { border-color: #48bb78; }

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
}

.stat-card.blue .stat-icon { background: #eef2ff; color: #667eea; }
.stat-card.green .stat-icon { background: #f0fff4; color: #48bb78; }

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #718096;
    font-weight: 500;
}

.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.department-card {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-top: 3px solid #667eea;
}

.department-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.department-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.department-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.department-dean {
    font-size: 0.9rem;
    color: #718096;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.department-stats {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.75rem;
    border-top: 1px solid #f1f5f9;
}

.lecturer-count {
    padding: 0.35rem 0.75rem;
    background: #eef2ff;
    color: #667eea;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-icon {
    padding: 0.4rem 0.6rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.85rem;
}

.btn-edit {
    background: #fef3c7;
    color: #92400e;
}

.btn-edit:hover {
    background: #fde68a;
}

.btn-delete {
    background: #fee2e2;
    color: #991b1b;
}

.btn-delete:hover {
    background: #fecaca;
}

.btn-add-lecturer {
    background: #d1fae5;
    color: #065f46;
}

.btn-add-lecturer:hover {
    background: #a7f3d0;
}

.btn-view-lecturers {
    background: #ddd6fe;
    color: #5b21b6;
}

.btn-view-lecturers:hover {
    background: #c4b5fd;
}

.lecturer-list {
    max-height: 300px;
    overflow-y: auto;
}

.lecturer-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 6px;
    margin-bottom: 0.5rem;
}

.lecturer-name {
    font-weight: 500;
    color: #2d3748;
}

.btn-remove {
    padding: 0.25rem 0.5rem;
    background: #fee2e2;
    color: #991b1b;
    border: none;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: pointer;
}

.btn-remove:hover {
    background: #fecaca;
}
</style>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4><i class="fas fa-building me-2"></i>Departments Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="fas fa-plus me-2"></i>Add Department
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

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-value"><?= $total_departments ?></div>
        <div class="stat-label">Total Departments</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-value"><?= $total_lecturers ?></div>
        <div class="stat-label">Total Lecturers</div>
    </div>
</div>

<!-- Departments Grid -->
<?php if ($departments->num_rows > 0): ?>
    <div class="departments-grid">
        <?php while($dept = $departments->fetch_assoc()): ?>
            <div class="department-card">
                <div class="department-header">
                    <div style="flex: 1;">
                        <div class="department-name">
                            <i class="fas fa-building me-2" style="color: #667eea;"></i>
                            <?= htmlspecialchars($dept['department_name']) ?>
                        </div>
                        <div class="department-dean">
                            <i class="fas fa-user-tie"></i>
                            Dean: <?= htmlspecialchars($dept['dean']) ?>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <button class="btn-icon btn-view-lecturers" 
                                onclick="viewLecturers(<?= $dept['department_id'] ?>, '<?= addslashes($dept['department_name']) ?>')"
                                data-bs-toggle="modal" 
                                data-bs-target="#viewLecturersModal"
                                title="View Lecturers">
                            <i class="fas fa-users"></i>
                        </button>
                        <button class="btn-icon btn-add-lecturer" 
                                onclick="openAddLecturer(<?= $dept['department_id'] ?>, '<?= addslashes($dept['department_name']) ?>')"
                                data-bs-toggle="modal" 
                                data-bs-target="#addLecturerModal"
                                title="Add Lecturer">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="btn-icon btn-edit" 
                                onclick="editDepartment(<?= $dept['department_id'] ?>, '<?= addslashes($dept['department_name']) ?>', '<?= addslashes($dept['dean']) ?>')"
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal"
                                title="Edit Department">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?= $dept['department_id'] ?>" 
                           class="btn-icon btn-delete"
                           onclick="return confirm('Delete this department? This action cannot be undone.')"
                           title="Delete Department">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <div class="department-stats">
                    <span class="lecturer-count">
                        <i class="fas fa-users me-1"></i>
                        <?= $dept['lecturer_count'] ?> Lecturers
                    </span>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle me-2"></i>No departments found. Create your first department!
    </div>
<?php endif; ?>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Department Name *</label>
                        <input type="text" name="department_name" class="form-control" 
                               placeholder="e.g., Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dean Name *</label>
                        <input type="text" name="dean" class="form-control" 
                               placeholder="e.g., Dr. John Smith" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_department" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="department_id" id="edit_department_id">
                    <div class="mb-3">
                        <label class="form-label">Department Name *</label>
                        <input type="text" name="department_name" id="edit_department_name" 
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dean Name *</label>
                        <input type="text" name="dean" id="edit_dean" 
                               class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_department" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Department
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Lecturer Modal -->
<div class="modal fade" id="addLecturerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Lecturer to <span id="add_dept_name"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="department_id" id="add_department_id">
                    <div class="mb-3">
                        <label class="form-label">Select Lecturer *</label>
                        <select name="lecturer_id" class="form-select" required>
                            <option value="">Choose a lecturer...</option>
                            <?php 
                            $unassigned_lecturers->data_seek(0);
                            while($lecturer = $unassigned_lecturers->fetch_assoc()): 
                            ?>
                                <option value="<?= $lecturer['teacher_id'] ?>">
                                    <?= htmlspecialchars($lecturer['teacher_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <?php if ($unassigned_lecturers->num_rows == 0): ?>
                            <small class="text-muted">No unassigned lecturers available</small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_lecturer" class="btn btn-primary" 
                            <?= $unassigned_lecturers->num_rows == 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-user-plus me-2"></i>Add Lecturer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Lecturers Modal -->
<div class="modal fade" id="viewLecturersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lecturers in <span id="view_dept_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="lecturer-list" id="lecturers_container">
                    <!-- Lecturers will be loaded here via AJAX or populated via PHP -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function editDepartment(id, name, dean) {
    document.getElementById('edit_department_id').value = id;
    document.getElementById('edit_department_name').value = name;
    document.getElementById('edit_dean').value = dean;
}

function openAddLecturer(deptId, deptName) {
    document.getElementById('add_department_id').value = deptId;
    document.getElementById('add_dept_name').textContent = deptName;
}

function viewLecturers(deptId, deptName) {
    document.getElementById('view_dept_name').textContent = deptName;
    
    // Fetch lecturers via AJAX
    fetch('get_department_lecturers.php?dept_id=' + deptId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('lecturers_container').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('lecturers_container').innerHTML = 
                '<div class="alert alert-danger">Error loading lecturers</div>';
        });
}
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>