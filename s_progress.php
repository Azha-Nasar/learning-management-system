<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get overall statistics
$stats_query = "SELECT 
                  (SELECT COUNT(*) FROM student_assignment WHERE student_id = ? AND grade != '') as graded_assignments,
                  (SELECT COUNT(*) FROM student_assignment WHERE student_id = ?) as total_submitted,
                  (SELECT AVG(CAST(grade AS UNSIGNED)) FROM student_assignment WHERE student_id = ? AND grade != '' AND grade REGEXP '^[0-9]+$') as avg_assignment_grade,
                  (SELECT COUNT(*) FROM student_class_quiz WHERE student_id = ?) as quizzes_taken,
                  (SELECT AVG(CAST(grade AS UNSIGNED)) FROM student_class_quiz WHERE student_id = ?) as avg_quiz_grade";

$stmt = $conn->prepare($stats_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("iiiii", $student_id, $student_id, $student_id, $student_id, $student_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Calculate overall average
$overall_avg = 0;
if ($stats['avg_assignment_grade'] && $stats['avg_quiz_grade']) {
    $overall_avg = ($stats['avg_assignment_grade'] + $stats['avg_quiz_grade']) / 2;
} elseif ($stats['avg_assignment_grade']) {
    $overall_avg = $stats['avg_assignment_grade'];
} elseif ($stats['avg_quiz_grade']) {
    $overall_avg = $stats['avg_quiz_grade'];
}

// Fetch subject-wise performance
$subject_performance_query = "SELECT 
                                s.subject_name,
                                s.subject_code,
                                COUNT(DISTINCT sa.student_assignment_id) as assignments_submitted,
                                AVG(CASE WHEN sa.grade != '' AND sa.grade REGEXP '^[0-9]+$' THEN CAST(sa.grade AS UNSIGNED) END) as avg_assignment,
                                COUNT(DISTINCT scq.student_class_quiz_id) as quizzes_taken,
                                AVG(CAST(scq.grade AS UNSIGNED)) as avg_quiz
                              FROM teacher_class_student tcs
                              JOIN teacher_class tc ON tcs.teacher_class_id = tc.teacher_class_id
                              JOIN subject s ON tc.subject_id = s.subject_id
                              LEFT JOIN assignment a ON tc.class_id = a.class_id
                              LEFT JOIN student_assignment sa ON a.assignment_id = sa.assignment_id AND sa.student_id = ?
                              LEFT JOIN class_quiz cq ON tc.teacher_class_id = cq.teacher_class_id
                              LEFT JOIN student_class_quiz scq ON cq.class_quiz_id = scq.class_quiz_id AND scq.student_id = ?
                              WHERE tcs.student_id = ?
                              GROUP BY s.subject_id, s.subject_name, s.subject_code
                              ORDER BY s.subject_name";

$stmt = $conn->prepare($subject_performance_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$subject_performance = $stmt->get_result();

// Recent quiz results
$recent_quizzes_query = "SELECT 
                           q.quiz_title,
                           s.subject_name,
                           scq.grade,
                           scq.student_quiz_time
                         FROM student_class_quiz scq
                         JOIN class_quiz cq ON scq.class_quiz_id = cq.class_quiz_id
                         JOIN quiz q ON cq.quiz_id = q.quiz_id
                         JOIN teacher_class tc ON cq.teacher_class_id = tc.teacher_class_id
                         JOIN subject s ON tc.subject_id = s.subject_id
                         WHERE scq.student_id = ?
                         ORDER BY scq.student_quiz_time DESC
                         LIMIT 5";

$stmt = $conn->prepare($recent_quizzes_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$recent_quizzes = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Progress | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>


        .overall-summary {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }

        .overall-grade {
            font-size: 3rem;
            font-weight: 700;
            color: #3498db;
            margin: 0.5rem 0;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #3498db;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .subject-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .subject-name {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .subject-code {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .progress-bar-custom {
            height: 6px;
            border-radius: 10px;
            background: #e9ecef;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }

        .grade-badge {
            display: inline-block;
            padding: 0.35rem 0.85rem;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .grade-excellent { background: #4caf50; color: white; }
        .grade-good { background: #2196f3; color: white; }
        .grade-fair { background: #ff9800; color: white; }
        .grade-poor { background: #f44336; color: white; }

        .quiz-item {
            background: #f8f9fa;
            border-left: 3px solid #3498db;
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="page-header-simple">
        <h4>
            <i class="fas fa-chart-line me-2"></i>My Progress <hr>
        </h4>
    </div>

    <!-- Overall Summary -->
    <div class="overall-summary">
        <p class="text-muted mb-1">Overall Average</p>
        <div class="overall-grade"><?= number_format($overall_avg, 1) ?>%</div>
    </div>

    <!-- Statistics -->
    <div class="row mb-3">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_submitted'] ?></div>
                <div class="stat-label">Assignments</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['graded_assignments'] ?></div>
                <div class="stat-label">Graded</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['quizzes_taken'] ?></div>
                <div class="stat-label">Quizzes</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['avg_quiz_grade'] ?? 0, 1) ?>%</div>
                <div class="stat-label">Quiz Avg</div>
            </div>
        </div>
    </div>

    <!-- Subject Performance -->
    <div class="section-title">
        <i class="fas fa-book me-2"></i>Subject Performance
    </div>
    <?php if ($subject_performance->num_rows > 0): ?>
        <?php while($subject = $subject_performance->fetch_assoc()): 
            $subject_avg = 0;
            if ($subject['avg_assignment'] && $subject['avg_quiz']) {
                $subject_avg = ($subject['avg_assignment'] + $subject['avg_quiz']) / 2;
            } elseif ($subject['avg_assignment']) {
                $subject_avg = $subject['avg_assignment'];
            } elseif ($subject['avg_quiz']) {
                $subject_avg = $subject['avg_quiz'];
            }
            
            if ($subject_avg >= 80) {
                $badge_class = 'grade-excellent';
            } elseif ($subject_avg >= 70) {
                $badge_class = 'grade-good';
            } elseif ($subject_avg >= 60) {
                $badge_class = 'grade-fair';
            } else {
                $badge_class = 'grade-poor';
            }
        ?>
            <div class="subject-card">
                <div class="subject-header">
                    <div>
                        <div class="subject-name"><?= htmlspecialchars($subject['subject_name']) ?></div>
                        <span class="subject-code"><?= htmlspecialchars($subject['subject_code']) ?></span>
                    </div>
                    <span class="grade-badge <?= $badge_class ?>"><?= number_format($subject_avg, 1) ?>%</span>
                </div>
                
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: <?= min(100, $subject_avg) ?>%"></div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-6">
                        <small class="text-muted">
                            <i class="fas fa-file-alt me-1"></i><?= $subject['assignments_submitted'] ?> assignments
                        </small>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">
                            <i class="fas fa-question-circle me-1"></i><?= $subject['quizzes_taken'] ?> quizzes
                        </small>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No performance data available yet.
        </div>
    <?php endif; ?>

    <!-- Recent Quiz Results -->
    <div class="section-title mt-4">
        <i class="fas fa-history me-2"></i>Recent Quiz Results
    </div>
    <?php if ($recent_quizzes->num_rows > 0): ?>
        <?php while($quiz = $recent_quizzes->fetch_assoc()): ?>
            <div class="quiz-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong style="font-size: 0.95rem;"><?= htmlspecialchars($quiz['quiz_title']) ?></strong>
                        <small class="text-muted d-block">
                            <?= htmlspecialchars($quiz['subject_name']) ?> • 
                            <?= date('M d, Y', strtotime($quiz['student_quiz_time'])) ?>
                        </small>
                    </div>
                    <span class="badge bg-primary">
                        <?= $quiz['grade'] ?>%
                    </span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No quiz results yet.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>