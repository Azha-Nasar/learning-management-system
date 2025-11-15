<?php
session_start();
include('dbcon.php');

// Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student info with class
$query = "SELECT s.*, c.class_name 
          FROM student s 
          LEFT JOIN class c ON s.class_id = c.class_id 
          WHERE s.student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get enrolled classes count
$classes_query = "SELECT COUNT(DISTINCT tc.teacher_class_id) as class_count
                  FROM teacher_class_student tcs
                  JOIN teacher_class tc ON tcs.teacher_class_id = tc.teacher_class_id
                  WHERE tcs.student_id = ? AND tcs.status = 'enrolled'";
$stmt = $conn->prepare($classes_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_result = $stmt->get_result()->fetch_assoc();
$class_count = $class_result['class_count'];
$stmt->close();

// Get pending assignments count
$assignments_query = "SELECT COUNT(DISTINCT a.assignment_id) as pending_count
                      FROM assignment a
                      JOIN teacher_class tc ON a.class_id = tc.class_id
                      JOIN teacher_class_student tcs ON tc.teacher_class_id = tcs.teacher_class_id
                      LEFT JOIN student_assignment sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ?
                      WHERE tcs.student_id = ? AND sa.student_assignment_id IS NULL";
$stmt = $conn->prepare($assignments_query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$assignment_result = $stmt->get_result()->fetch_assoc();
$pending_assignments = $assignment_result['pending_count'];
$stmt->close();

// Get available quizzes count
$quiz_query = "SELECT COUNT(DISTINCT cq.class_quiz_id) as quiz_count
               FROM class_quiz cq
               JOIN teacher_class_student tcs ON cq.teacher_class_id = tcs.teacher_class_id
               LEFT JOIN student_class_quiz scq ON cq.class_quiz_id = scq.class_quiz_id AND scq.student_id = ?
               WHERE tcs.student_id = ? AND scq.student_class_quiz_id IS NULL";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$quiz_result = $stmt->get_result()->fetch_assoc();
$available_quizzes = $quiz_result['quiz_count'];
$stmt->close();

// Get recent announcements
$announcements_query = "SELECT a.*, u.firstname, u.lastname 
                        FROM announcements a
                        JOIN users u ON a.posted_by = u.user_id
                        ORDER BY a.created_at DESC 
                        LIMIT 5";
$announcements = $conn->query($announcements_query);

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .stat-card.classes { border-left-color: #3498db; }
        .stat-card.assignments { border-left-color: #e74c3c; }
        .stat-card.quizzes { border-left-color: #f39c12; }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-card.classes .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card.assignments .stat-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .stat-card.quizzes .stat-icon {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #333;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .announcement-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .announcement-item {
            padding: 1rem;
            border-left: 3px solid #3498db;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        /* ✅ NEW: Success alert styling with animation */
        .login-success-alert {
            animation: slideInDown 0.5s ease-out;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <!-- ✅ NEW: Login Success Alert -->
    <?php if (isset($_SESSION['login_success'])): ?>
        <div class="alert login-success-alert alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['login_success']) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <div class="dashboard-header">
        <h2>Welcome, <?= htmlspecialchars($student_data['name']) ?>!</h2>
        <p class="mb-0">Class: <?= htmlspecialchars($student_data['class_name']) ?> | Student Number: <?= htmlspecialchars($student_data['student_number'] ?? 'N/A') ?></p>
    </div>

    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-4">
            <div class="stat-card classes">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h6 class="text-muted mb-2">ENROLLED CLASSES</h6>
                <div class="stat-number"><?= $class_count ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card assignments">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h6 class="text-muted mb-2">PENDING ASSIGNMENTS</h6>
                <div class="stat-number"><?= $pending_assignments ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card quizzes">
                <div class="stat-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h6 class="text-muted mb-2">AVAILABLE QUIZZES</h6>
                <div class="stat-number"><?= $available_quizzes ?></div>
            </div>
        </div>
    </div>

    <!-- Recent Announcements -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="announcement-card">
                <h5 class="mb-3">
                    <i class="fas fa-bullhorn me-2"></i>Recent Announcements
                </h5>
                <?php if ($announcements->num_rows > 0): ?>
                    <?php while($ann = $announcements->fetch_assoc()): ?>
                        <div class="announcement-item">
                            <h6 class="mb-1"><?= htmlspecialchars($ann['title']) ?></h6>
                            <small class="text-muted">
                                Posted by <?= htmlspecialchars($ann['firstname'] . ' ' . $ann['lastname']) ?> 
                                on <?= date('M d, Y', strtotime($ann['created_at'])) ?>
                            </small>
                            <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($ann['message'])) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">No announcements at this time.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-dismiss alert after 5 seconds
setTimeout(() => {
    const alert = document.querySelector('.login-success-alert');
    if (alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }
}, 5000);
</script>
</body>
</html>