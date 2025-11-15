<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's class_id
$class_query = "SELECT class_id FROM student WHERE student_id = ?";
$stmt = $conn->prepare($class_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_result = $stmt->get_result();
$student_class = $class_result->fetch_assoc();
$class_id = $student_class['class_id'] ?? null;

if (!$class_id) {
    die("Student class not found.");
}

// Fetch subjects for the student's class
$subjects_query = "SELECT 
                    tc.teacher_class_id,
                    tc.thumbnails,
                    s.subject_id,
                    s.subject_code,
                    s.subject_name,
                    s.description,
                    c.class_name,
                    t.name as teacher_name,
                    COUNT(DISTINCT a.assignment_id) as total_assignments,
                    COUNT(DISTINCT cq.class_quiz_id) as total_quizzes,
                    tc.school_year,
                    tc.status
                   FROM teacher_class tc
                   JOIN subject s ON tc.subject_id = s.subject_id
                   JOIN class c ON tc.class_id = c.class_id
                   JOIN teacher t ON tc.teacher_id = t.teacher_id
                   LEFT JOIN assignment a ON tc.class_id = a.class_id
                   LEFT JOIN class_quiz cq ON tc.teacher_class_id = cq.teacher_class_id
                   WHERE tc.class_id = ? AND tc.status = 'active'
                   GROUP BY tc.teacher_class_id, tc.thumbnails, s.subject_id, s.subject_code, 
                            s.subject_name, s.description, c.class_name, t.name, 
                            tc.school_year, tc.status
                   ORDER BY s.subject_name";

$stmt = $conn->prepare($subjects_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $class_id);
$stmt->execute();
$subjects = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subject Overview | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .subject-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            margin-bottom: 1.25rem;
            height: 100%;
        }

        .subject-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #3498db;
        }

        .subject-thumbnail {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .subject-body {
            padding: 1rem;
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.5rem;
        }

        .subject-code {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .year-badge {
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
        }

        .subject-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0.5rem 0;
            line-height: 1.3;
        }

        .teacher-name {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }

        .teacher-name i {
            color: #3498db;
            font-size: 0.8rem;
        }

        .subject-stats {
            display: flex;
            gap: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            color: #6b7280;
        }

        .stat-item i {
            color: #3498db;
            font-size: 0.9rem;
        }

        .stat-number {
            font-weight: 600;
            color: #1a1a1a;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            color: #6b7280;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="page-header-simple">
        <h4>
            <i class="fas fa-book-open me-2"></i>Subject Overview <hr>
        </h4>
    </div>

    <?php if ($subjects->num_rows > 0): ?>
        <div class="row">
            <?php while($subject = $subjects->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="subject-card">
                        <img src="<?= htmlspecialchars($subject['thumbnails']) ?>" 
                             alt="Subject Thumbnail" 
                             class="subject-thumbnail"
                             onerror="this.src='uploads/default-class.jpg'">
                        
                        <div class="subject-body">
                            <div class="subject-header">
                                <span class="subject-code"><?= htmlspecialchars($subject['subject_code']) ?></span>
                                <?php if (!empty($subject['school_year'])): ?>
                                    <span class="year-badge"><?= htmlspecialchars($subject['school_year']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="subject-title"><?= htmlspecialchars($subject['subject_name']) ?></div>
                            
                            <div class="teacher-name">
                                <i class="fas fa-user-tie me-1"></i>
                                <?= htmlspecialchars($subject['teacher_name']) ?>
                            </div>

                            <div class="subject-stats">
                                <div class="stat-item">
                                    <i class="fas fa-tasks"></i>
                                    <span class="stat-number"><?= $subject['total_assignments'] ?></span>
                                    <span>Tasks</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-clipboard-list"></i>
                                    <span class="stat-number"><?= $subject['total_quizzes'] ?></span>
                                    <span>Quizzes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h5>No subjects available</h5>
            <p class="text-muted mb-0">No active subjects assigned to your class yet</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>