<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Mark notification as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notif_id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE notification SET seen = 1 WHERE notification_id = ? AND user_id = ? AND user_type = 'student'");
    $stmt->bind_param("ii", $notif_id, $student_id);
    $stmt->execute();
    header("Location: s_notifications.php");
    exit();
}

// Fetch all notifications for this student
$notifications_query = "SELECT * FROM notification 
                        WHERE user_id = ? AND user_type = 'student' 
                        ORDER BY timestamp DESC";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Count unread notifications
$unread_query = "SELECT COUNT(*) as unread_count FROM notification 
                 WHERE user_id = ? AND user_type = 'student' AND seen = 0";
$stmt = $conn->prepare($unread_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$unread_result = $stmt->get_result()->fetch_assoc();
$unread_count = $unread_result['unread_count'];

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notification-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .notification-card.unread {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }

        .notification-card.read {
            border-left-color: #e0e0e0;
        }

        .notification-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .notif-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 1rem;
        }

        .icon-info { background: #e3f2fd; color: #2196f3; }
        .icon-warning { background: #fff3e0; color: #ff9800; }
        .icon-success { background: #e8f5e9; color: #4caf50; }
        .icon-error { background: #ffebee; color: #f44336; }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .notif-message {
            font-size: 1rem;
            color: #333;
            line-height: 1.6;
        }

        .notif-time {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .unread-badge {
            background: #2196f3;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            <i class="fas fa-bell me-2"></i>Notifications
            <?php if ($unread_count > 0): ?>
                <span class="unread-badge"><?= $unread_count ?> New</span>
            <?php endif; ?>
        </h4>
    </div>
    <hr>

    <?php if ($notifications->num_rows > 0): ?>
        <?php while($notif = $notifications->fetch_assoc()): 
            $icon_class = 'icon-' . strtolower($notif['type']);
            $icon_map = [
                'info' => 'fa-info-circle',
                'warning' => 'fa-exclamation-triangle',
                'success' => 'fa-check-circle',
                'error' => 'fa-times-circle'
            ];
            $icon = $icon_map[$notif['type']] ?? 'fa-bell';
        ?>
            <div class="notification-card <?= $notif['seen'] ? 'read' : 'unread' ?>">
                <div class="d-flex align-items-start">
                    <div class="notif-icon <?= $icon_class ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="notif-header">
                            <div class="notif-message">
                                <?= nl2br(htmlspecialchars($notif['message'])) ?>
                            </div>
                            <?php if (!$notif['seen']): ?>
                                <a href="?mark_read=<?= $notif['notification_id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    Mark as Read
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="notif-time">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('M d, Y - H:i', strtotime($notif['timestamp'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-bell-slash fa-3x mb-3" style="opacity: 0.3;"></i>
            <h5>No notifications yet</h5>
            <p class="text-muted">You'll see updates and important messages here.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>