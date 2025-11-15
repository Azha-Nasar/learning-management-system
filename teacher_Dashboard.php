<?php
session_start();
include('dbcon.php');

// Ensure teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// ---------- Get Class Count ----------
$query_class = "SELECT COUNT(*) AS class_count FROM teacher_class WHERE teacher_id = ?";
$stmt_class = $conn->prepare($query_class);
if (!$stmt_class) {
    die("Database prepare error (class count): " . $conn->error);
}
$stmt_class->bind_param("i", $teacher_id);
$stmt_class->execute();
$stmt_class->bind_result($class_count);
$stmt_class->fetch();
$stmt_class->close();

// ---------- Get Student Count ----------
$query_student = "SELECT COUNT(DISTINCT tcs.student_id) AS student_count 
                  FROM teacher_class_student tcs
                  WHERE tcs.teacher_id = ?";
$stmt_student = $conn->prepare($query_student);
if (!$stmt_student) {
    die("Database prepare error (student count): " . $conn->error);
}
$stmt_student->bind_param("i", $teacher_id);
$stmt_student->execute();
$stmt_student->bind_result($student_count);
$stmt_student->fetch();
$stmt_student->close();

// ---------- Get Notifications ----------
$notifications = [];
$query_notif = "SELECT notification_id, message, timestamp
                FROM notification
                WHERE user_id = ? AND user_type = 'teacher'
                ORDER BY timestamp DESC 
                LIMIT 5";

$stmt_notif = $conn->prepare($query_notif);
if (!$stmt_notif) {
    die("Database prepare error (notification): " . $conn->error);
}
$stmt_notif->bind_param("i", $teacher_id);
$stmt_notif->execute();
$result = $stmt_notif->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt_notif->close();

// ---------- Get Teacher Info ----------
$query_teacher = "SELECT name FROM teacher WHERE teacher_id = ? LIMIT 1";
$stmt_teacher = $conn->prepare($query_teacher);
if (!$stmt_teacher) {
    die("Database prepare error (teacher info): " . $conn->error);
}
$stmt_teacher->bind_param("i", $teacher_id);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();
$teacher_info = $result_teacher->fetch_assoc();
$teacher_name = $teacher_info['name'] ?? 'Teacher';
$stmt_teacher->close();

// ---------- Include layout ----------
include('teacher_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.25rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.dashboard-header h2 {
    margin: 0;
    font-weight: 600;
    font-size: 1.5rem;
}

.dashboard-header p {
    margin: 0.3rem 0 0 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    border-left: 3px solid;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 3px 12px rgba(0,0,0,0.1);
}

.stat-card.classes {
    border-left-color: #3498db;
}

.stat-card.students {
    border-left-color: #2ecc71;
}

.stat-card .icon-box {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin-bottom: 0.75rem;
}

.stat-card.classes .icon-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card.students .icon-box {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-card h3 {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.3rem;
}

.stat-card .number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.notification-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.notification-card h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.notification-item {
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 0.6rem;
    background: #f8f9fa;
    border-left: 3px solid #3498db;
    transition: all 0.2s ease;
}

.notification-item:hover {
    background: #e9ecef;
    border-left-color: #2980b9;
}

.notification-item:last-child {
    margin-bottom: 0;
}

.notification-item p {
    margin: 0 0 0.4rem 0;
    color: #2c3e50;
    font-size: 0.9rem;
    line-height: 1.4;
}

.notification-item small {
    color: #6c757d;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.empty-state {
    text-align: center;
    padding: 2rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.3;
}

.badge-count {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

@media (max-width: 768px) {
    .stat-card .number {
        font-size: 1.5rem;
    }
    
    .dashboard-header {
        padding: 1rem;
    }
    
    .dashboard-header h2 {
        font-size: 1.3rem;
    }
}
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Welcome Header -->
        <div class="dashboard-header">
            <h2>Welcome back, <?= htmlspecialchars($teacher_name); ?>!</h2>
            <p>Here's what's happening with your classes today</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="stat-card classes">
                    <div class="icon-box">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Total Classes</h3>
                    <p class="number"><?= $class_count; ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="stat-card students">
                    <div class="icon-box">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>Total Students</h3>
                    <p class="number"><?= $student_count; ?></p>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="stat-card" style="border-left-color: #e74c3c;">
                    <div class="icon-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p class="number"><?= count($notifications); ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="row">
            <div class="col-12">
                <div class="notification-card">
                    <h3>
                        <i class="fas fa-bell"></i>
                        Recent Notifications
                        <?php if (count($notifications) > 0): ?>
                            <span class="badge-count"><?= count($notifications); ?></span>
                        <?php endif; ?>
                    </h3>

                    <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $note): ?>
                            <div class="notification-item">
                                <p><?= htmlspecialchars($note['message']); ?></p>
                                <small>
                                    <i class="far fa-clock"></i>
                                    <?= htmlspecialchars($note['timestamp']); ?>
                                </small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="far fa-bell-slash"></i>
                            <p>No notifications at the moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>