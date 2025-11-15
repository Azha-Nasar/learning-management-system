<?php
session_start();
include('dbcon.php');
require_once('create_notification.php');

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grade'])) {
    $student_assignment_id = intval($_POST['student_assignment_id']);
    $grade = trim($_POST['grade']);
    
    // Get student and assignment info for notification
    $info_query = "SELECT sa.student_id, s.name as student_name, a.fname as assignment_name
                   FROM student_assignment sa
                   JOIN student s ON sa.student_id = s.student_id
                   JOIN assignment a ON sa.assignment_id = a.assignment_id
                   WHERE sa.student_assignment_id = ?";
    $info_stmt = $conn->prepare($info_query);
    $info_stmt->bind_param("i", $student_assignment_id);
    $info_stmt->execute();
    $info = $info_stmt->get_result()->fetch_assoc();
    
    $stmt = $conn->prepare("UPDATE student_assignment SET grade = ? WHERE student_assignment_id = ?");
    $stmt->bind_param("si", $grade, $student_assignment_id);
    
    if ($stmt->execute()) {
        // Create notification for student
        if ($info) {
            createNotification($conn, $info['student_id'], 'student', 
                "Your assignment '" . $info['assignment_name'] . "' has been graded: " . $grade . "%", 
                'success');
        }
        
        $_SESSION['success'] = "Grade submitted successfully!";
    } else {
        $_SESSION['error'] = "Failed to submit grade: " . $conn->error;
    }
    
    header("Location: t_grade_assignments.php");
    exit;
}

// Fetch submitted assignments for teacher's classes
$submissions_query = "SELECT 
                        sa.*,
                        s.name as student_name,
                        s.email as student_email,
                        a.fname as assignment_name,
                        a.fdesc as assignment_desc,
                        c.class_name
                      FROM student_assignment sa
                      JOIN student s ON sa.student_id = s.student_id
                      JOIN assignment a ON sa.assignment_id = a.assignment_id
                      JOIN class c ON a.class_id = c.class_id
                      WHERE a.teacher_id = ?
                      ORDER BY sa.assignment_fdatein DESC";

$stmt = $conn->prepare($submissions_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$submissions = $stmt->get_result();

// Include layout AFTER all header() calls
include('teacher_layout.php');
?>

<style>
.submission-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid #3498db;
}

.submission-card.graded {
    border-left-color: #28a745;
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 1rem;
}

.student-info h5 {
    margin: 0 0 0.25rem 0;
    color: #2c3e50;
    font-weight: 600;
}

.grade-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

.grade-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.grade-badge.graded {
    background: #d1f4dd;
    color: #155724;
}

.submission-details {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}
</style>

<div class="container-fluid mt-0">
    <h4 class="mb-4">
        <i class="fas fa-check-square me-2"></i>Grade Student Assignments
    </h4>
    <hr>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($submissions->num_rows > 0): ?>
        <?php while($sub = $submissions->fetch_assoc()): ?>
            <div class="submission-card <?= !empty($sub['grade']) ? 'graded' : '' ?>">
                <div class="submission-header">
                    <div class="student-info">
                        <h5><?= htmlspecialchars($sub['student_name']) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($sub['student_email']) ?></small>
                    </div>
                    <div class="grade-badge <?= !empty($sub['grade']) ? 'graded' : 'pending' ?>">
                        <?= !empty($sub['grade']) ? htmlspecialchars($sub['grade']) . '%' : 'Pending' ?>
                    </div>
                </div>

                <div class="submission-details">
                    <p class="mb-2"><strong>Assignment:</strong> <?= htmlspecialchars($sub['assignment_name']) ?></p>
                    <p class="mb-2"><strong>Class:</strong> <?= htmlspecialchars($sub['class_name']) ?></p>
                    <p class="mb-2"><strong>Description:</strong> <?= htmlspecialchars($sub['assignment_desc']) ?></p>
                    <p class="mb-0"><strong>Submitted:</strong> <?= date('M d, Y', strtotime($sub['assignment_fdatein'])) ?></p>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= htmlspecialchars($sub['floc']) ?>" 
                       class="btn btn-primary" 
                       download>
                        <i class="fas fa-download me-2"></i>Download Submission
                    </a>
                    
                    <?php if (empty($sub['grade'])): ?>
                        <button class="btn btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#gradeModal<?= $sub['student_assignment_id'] ?>">
                            <i class="fas fa-check me-2"></i>Grade Assignment
                        </button>
                    <?php else: ?>
                        <button class="btn btn-warning" 
                                data-bs-toggle="modal" 
                                data-bs-target="#gradeModal<?= $sub['student_assignment_id'] ?>">
                            <i class="fas fa-edit me-2"></i>Update Grade
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Grade Modal -->
            <div class="modal fade" id="gradeModal<?= $sub['student_assignment_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title">Grade Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="student_assignment_id" value="<?= $sub['student_assignment_id'] ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Student</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($sub['student_name']) ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Grade (0-100) *</label>
                                    <input type="number" 
                                           name="grade" 
                                           class="form-control" 
                                           min="0" 
                                           max="100" 
                                           value="<?= htmlspecialchars($sub['grade']) ?>"
                                           required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="submit_grade" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Submit Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No student submissions yet.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>