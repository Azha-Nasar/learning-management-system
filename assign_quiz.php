<?php
session_start();
include('dbcon.php');

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = $_POST['quiz_id'] ?? null;
    $teacher_class_id = $_POST['teacher_class_id'] ?? null;
    $quiz_time = $_POST['quiz_time'] ?? null;

    if ($quiz_id && $teacher_class_id && $quiz_time) {
        $stmt = $conn->prepare("INSERT INTO class_quiz (teacher_class_id, quiz_time, quiz_id) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $teacher_class_id, $quiz_time, $quiz_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Quiz assigned successfully!";
        } else {
            $_SESSION['error'] = "Failed to assign quiz: " . $conn->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill all fields.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

header("Location: t_quiz.php");
exit;
?>
