<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch messages sent to this student
$messages_query = "SELECT 
                     m.*,
                     t.name as sender_name
                   FROM message m
                   JOIN teacher t ON m.sender_id = t.teacher_id
                   WHERE m.reciever_id = ?
                   ORDER BY m.date_sended DESC";

$stmt = $conn->prepare($messages_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$messages = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: transform 0.2s ease;
        }

        .message-card:hover {
            transform: translateX(5px);
        }

        .message-card.unread {
            background: #e3f2fd;
            border-left-color: #1976d2;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .sender-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .message-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .message-content {
            font-size: 1rem;
            line-height: 1.6;
            color: #495057;
        }

        .empty-inbox {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-inbox i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <h4 class="mb-4">
        <i class="fas fa-envelope me-2"></i>Messages from Lecturers
    </h4>
    <hr>

    <?php if ($messages->num_rows > 0): ?>
        <?php while($message = $messages->fetch_assoc()): ?>
            <div class="message-card">
                <div class="message-header">
                    <div class="sender-name">
                        <i class="fas fa-user-tie me-2"></i>
                        <?= htmlspecialchars($message['sender_name']) ?>
                    </div>
                    <div class="message-date">
                        <i class="fas fa-clock me-1"></i>
                        <?= date('M d, Y - H:i', strtotime($message['date_sended'])) ?>
                    </div>
                </div>
                <div class="message-content">
                    <?= nl2br(htmlspecialchars($message['content'])) ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-inbox">
            <i class="fas fa-inbox"></i>
            <h5>No messages yet</h5>
            <p class="text-muted">You haven't received any feedback from your lecturers.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>