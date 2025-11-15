<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's class
$class_query = "SELECT c.class_id, c.class_name 
                FROM student s
                JOIN class c ON s.class_id = c.class_id
                WHERE s.student_id = ?";
$stmt = $conn->prepare($class_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_info = $stmt->get_result()->fetch_assoc();

if (!$class_info) {
    die("Student class information not found.");
}

$class_id = $class_info['class_id'];
$class_name = $class_info['class_name'];

// Fetch classmates
$classmates_query = "SELECT 
                       student_id,
                       name,
                       email,
                       student_number,
                       profile_image,
                       status
                     FROM student
                     WHERE class_id = ? AND student_id != ? AND status = 'Active'
                     ORDER BY name ASC";

$stmt = $conn->prepare($classmates_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("ii", $class_id, $student_id);
$stmt->execute();
$classmates = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classmates | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .classmate-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .classmate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .classmate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            margin-right: 1rem;
        }

        .classmate-info {
            flex-grow: 1;
        }

        .classmate-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .classmate-details {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .class-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .total-count {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            margin-left: 1rem;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="class-header">
        <h4 class="mb-0">
            <i class="fas fa-users me-2"></i><?= htmlspecialchars($class_name) ?>
            <span class="total-count"><?= $classmates->num_rows ?> Classmates</span>
        </h4>
    </div>

    <?php if ($classmates->num_rows > 0): ?>
        <div class="row">
            <?php while($classmate = $classmates->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="classmate-card">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($classmate['profile_image'] ?? 'default.png') ?>" 
                                 alt="Profile" 
                                 class="classmate-avatar"
                                 onerror="this.src='default.png'">
                            <div class="classmate-info">
                                <div class="classmate-name">
                                    <?= htmlspecialchars($classmate['name']) ?>
                                </div>
                                <div class="classmate-details">
                                    <i class="fas fa-id-card me-1"></i>
                                    <?= htmlspecialchars($classmate['student_number'] ?? 'N/A') ?>
                                </div>
                                <div class="classmate-details">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?= htmlspecialchars($classmate['email']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-users-slash fa-3x mb-3" style="opacity: 0.3;"></i>
            <h5>No classmates found</h5>
            <p class="text-muted">You're the only student in this class right now.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>