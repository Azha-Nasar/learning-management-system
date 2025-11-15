<?php
// Notification Helper Functions

function createNotification($conn, $user_id, $user_type, $message, $type = 'info') {
    $stmt = $conn->prepare("INSERT INTO notification (user_id, user_type, message, type, seen, timestamp) VALUES (?, ?, ?, ?, 0, NOW())");
    $stmt->bind_param("isss", $user_id, $user_type, $message, $type);
    return $stmt->execute();
}

// Example usage after assignment submission:
// createNotification($conn, $teacher_id, 'teacher', "New assignment submission from $student_name", 'info');

// Example usage after quiz completion:
// createNotification($conn, $teacher_id, 'teacher', "$student_name completed quiz: $quiz_title", 'success');

// Example usage after grading:
// createNotification($conn, $student_id, 'student', "Your assignment '$assignment_name' has been graded: $grade%", 'success');
?>