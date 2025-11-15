<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('dbcon.php');

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

$query = "SELECT name FROM teacher WHERE teacher_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $fullName = $row['name'];
} else {
    $fullName = 'Teacher';
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Teacher | LMS Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS and FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 240px;
            background-color: #2c3e50;
            color: white;
            z-index: 100;
            padding-top: 60px;
        }

        .sidebar h5 {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        .sidebar .nav-link {
            color: white;
            padding: 10px 20px;
            display: block;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #1a252f;
            border-left: 4px solid #3498db;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 60px;
            background-color: #34495e;
            color: white;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 99;
        }

        .content {
            margin-left: 240px;
            margin-top: 60px;
            padding: 30px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            background-color: #f4f4f4;
        }

        .dropdown-menu-end {
            right: 0;
            left: auto;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-2">
        <h5 class="text-center mt-3 mb-4">📘 EduHub LMS</h5>
        <a href="teacher_dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i> Home</a>
        <a href="t_notifications.php" class="nav-link"><i class="fas fa-bell me-2"></i> Notifications</a>
        <a href="t_messages.php" class="nav-link"><i class="fas fa-comment-alt me-2"></i> Messages</a>
        <a href="t_my_class.php" class="nav-link"><i class="fas fa-book me-2"></i> My Class</a>
        <a href="t_students.php" class="nav-link"><i class="fas fa-users me-2"></i> Students</a>
        <a href="t_progress_report.php" class="nav-link"><i class="fas fa-chart-line me-2"></i> Progress Reports</a>
        <a href="t_upload_materials.php" class="nav-link"><i class="fas fa-upload me-2"></i> Upload Materials</a>
        <a href="t_assignment.php" class="nav-link"><i class="fas fa-tasks me-2"></i> Manage Assignments</a>
        <a href="t_grade_assignments.php" class="nav-link"><i class="fas fa-tasks me-2"></i> Grade Assignments</a>
        <a href="t_quiz.php" class="nav-link"><i class="fas fa-question-circle me-2"></i> Quizzes</a>
        <a href="t_timetable.php" class="nav-link"><i class="fas fa-calendar-alt me-2"></i> Time Table</a>
        <a href="add_announcement.php" class="nav-link"><i class="fas fa-bullhorn me-2"></i> Announcement</a>
        <a href="library.php" class="nav-link"><i class="fas fa-book-open me-2"></i> Library</a>

    </div>

    <!-- Topbar -->
    <div class="topbar">
        <h6 class="mb-0">Lecturer Portal</h6>
        <div class="dropdown">
            <a href="#" class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown">
                <i class="fas fa-user"></i> <?= htmlspecialchars($fullName); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="update_teacher_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="content">
