<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch enrolled classes with teacher and subject info
$query = "SELECT 
            tc.teacher_class_id,
            tc.thumbnails,
            tc.school_year,
            c.class_name,
            s.subject_name,
            t.name as teacher_name,
            tcs.enrollment_date,
            tcs.status
          FROM teacher_class_student tcs
          JOIN teacher_class tc ON tcs.teacher_class_id = tc.teacher_class_id
          JOIN class c ON tc.class_id = c.class_id
          JOIN subject s ON tc.subject_id = s.subject_id
          JOIN teacher t ON tc.teacher_id = t.teacher_id
          WHERE tcs.student_id = ?
          ORDER BY tcs.enrollment_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$classes = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classes | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .class-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .class-thumbnail {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .class-body {
            padding: 1.25rem;
        }

        .class-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .class-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-enrolled {
            background: #28a745;
            color: white;
        }

        .badge-dropped {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <h4 class="mb-4">
        <i class="fas fa-book me-2"></i>My Classes
    </h4>
    <hr>

    <?php if ($classes->num_rows > 0): ?>
        <div class="row">
            <?php while($class = $classes->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="class-card position-relative">
                        <span class="status-badge <?= $class['status'] == 'enrolled' ? 'badge-enrolled' : 'badge-dropped' ?>">
                            <?= ucfirst($class['status']) ?>
                        </span>
                        <img src="<?= htmlspecialchars($class['thumbnails']) ?>" 
                             alt="Class Thumbnail" 
                             class="class-thumbnail">
                        <div class="class-body">
                            <div class="class-title"><?= htmlspecialchars($class['class_name']) ?></div>
                            <div class="class-meta">
                                <i class="fas fa-book-open me-1"></i>
                                <?= htmlspecialchars($class['subject_name']) ?>
                            </div>
                            <div class="class-meta">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($class['teacher_name']) ?>
                            </div>
                            <div class="class-meta">
                                <i class="fas fa-calendar me-1"></i>
                                <?= htmlspecialchars($class['school_year']) ?>
                            </div>
                            <div class="class-meta">
                                <i class="fas fa-clock me-1"></i>
                                Enrolled: <?= date('M d, Y', strtotime($class['enrollment_date'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            You are not enrolled in any classes yet. Please contact your administrator.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>