<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Handle assignment submission - SIMPLIFIED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    
    // Check if file was uploaded
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        
        $fname = $_FILES['assignment_file']['name'];
        $uploadDir = 'uploads/student_submissions/';
        
        // Create directory if needed
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $floc = $uploadDir . time() . '_' . basename($fname);
        
        // Try to move uploaded file
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $floc)) {
            
            $fdatein = date('Y-m-d');
            $fdesc = !empty($_POST['description']) ? trim($_POST['description']) : 'Assignment Submission';
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO student_assignment (assignment_id, floc, assignment_fdatein, fdesc, fname, student_id, grade) VALUES (?, ?, ?, ?, ?, ?, '')");
            $stmt->bind_param("issssi", $assignment_id, $floc, $fdatein, $fdesc, $fname, $student_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = 'Assignment submitted successfully!';
            } else {
                $_SESSION['error_msg'] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
            
        } else {
            $_SESSION['error_msg'] = 'Failed to upload file';
        }
        
    } else {
        $_SESSION['error_msg'] = 'Please select a file to upload';
    }
    
    // Redirect back
    header("Location: s_assignments.php");
    exit();
}

// Fetch pending assignments
$pending_query = "SELECT 
                    a.*,
                    c.class_name,
                    s.subject_name,
                    t.name as teacher_name
                  FROM assignment a
                  JOIN class c ON a.class_id = c.class_id
                  JOIN teacher_class tc ON a.class_id = tc.class_id AND a.teacher_id = tc.teacher_id
                  JOIN subject s ON tc.subject_id = s.subject_id
                  JOIN teacher t ON a.teacher_id = t.teacher_id
                  JOIN teacher_class_student tcs ON tc.teacher_class_id = tcs.teacher_class_id
                  LEFT JOIN student_assignment sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ?
                  WHERE tcs.student_id = ? AND sa.student_assignment_id IS NULL
                  ORDER BY a.fdatein DESC";

$stmt = $conn->prepare($pending_query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$pending_assignments = $stmt->get_result();

// Fetch submitted assignments
$submitted_query = "SELECT 
                      sa.*,
                      c.class_name,
                      s.subject_name
                    FROM student_assignment sa
                    JOIN assignment a ON sa.assignment_id = a.assignment_id
                    JOIN class c ON a.class_id = c.class_id
                    JOIN teacher_class tc ON a.class_id = tc.class_id
                    JOIN subject s ON tc.subject_id = s.subject_id
                    WHERE sa.student_id = ?
                    ORDER BY sa.assignment_fdatein DESC";

$stmt = $conn->prepare($submitted_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$submitted_assignments = $stmt->get_result();

include('student_layout.php');
?>

<style>
    .assignment-card {
        background: white;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-left: 4px solid #3498db;
    }

    .assignment-card.submitted {
        border-left-color: #28a745;
    }

    .assignment-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
    }

    .grade-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .grade-pending {
        background: #ffc107;
        color: #333;
    }

    .grade-scored {
        background: #28a745;
        color: white;
    }
</style>

<div class="container-fluid mt-4">
    <h4 class="mb-4">
        <i class="fas fa-tasks me-2"></i>My Assignments
    </h4>
    <hr>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <!-- Pending Assignments -->
    <h5 class="mt-4 mb-3">Pending Assignments</h5>
    <?php if ($pending_assignments->num_rows > 0): ?>
        <?php while($assignment = $pending_assignments->fetch_assoc()): ?>
            <div class="assignment-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <div class="assignment-title"><?= htmlspecialchars($assignment['fname']) ?></div>
                        <p class="mb-2"><?= htmlspecialchars($assignment['fdesc']) ?></p>
                        <div class="text-muted small">
                            <i class="fas fa-book me-1"></i><?= htmlspecialchars($assignment['class_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-book-open me-1"></i><?= htmlspecialchars($assignment['subject_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($assignment['teacher_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i>Due: <?= date('M d, Y', strtotime($assignment['fdatein'])) ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#modal<?= $assignment['assignment_id'] ?>">
                        <i class="fas fa-upload me-1"></i>Submit
                    </button>
                </div>
                <a href="<?= htmlspecialchars($assignment['floc']) ?>" class="btn btn-sm btn-outline-secondary" download>
                    <i class="fas fa-download me-1"></i>Download Assignment
                </a>
            </div>

            <!-- Modal -->
            <div class="modal fade" id="modal<?= $assignment['assignment_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title">Submit Assignment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id'] ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Description (Optional)</label>
                                    <input type="text" name="description" class="form-control" placeholder="Brief note about your submission">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Upload File <span class="text-danger">*</span></label>
                                    <input type="file" name="assignment_file" class="form-control" required>
                                    <small class="text-muted">Max size: 10MB</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="submit_assignment" value="1" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i>Submit Assignment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No pending assignments at this time.
        </div>
    <?php endif; ?>

    <!-- Submitted Assignments -->
    <h5 class="mt-5 mb-3">Submitted Assignments</h5>
    <?php if ($submitted_assignments->num_rows > 0): ?>
        <?php while($submitted = $submitted_assignments->fetch_assoc()): ?>
            <div class="assignment-card submitted">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1">
                        <div class="assignment-title"><?= htmlspecialchars($submitted['fname']) ?></div>
                        <p class="mb-2"><?= htmlspecialchars($submitted['fdesc']) ?></p>
                        <div class="text-muted small">
                            <i class="fas fa-book me-1"></i><?= htmlspecialchars($submitted['class_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i>Submitted: <?= date('M d, Y', strtotime($submitted['assignment_fdatein'])) ?>
                        </div>
                    </div>
                    <span class="grade-badge <?= empty($submitted['grade']) ? 'grade-pending' : 'grade-scored' ?> ms-3">
                        <?= empty($submitted['grade']) ? 'Pending' : 'Grade: ' . htmlspecialchars($submitted['grade']) ?>
                    </span>
                </div>
                <a href="<?= htmlspecialchars($submitted['floc']) ?>" class="btn btn-sm btn-outline-success" download>
                    <i class="fas fa-download me-1"></i>Download Submission
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No submitted assignments yet.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>