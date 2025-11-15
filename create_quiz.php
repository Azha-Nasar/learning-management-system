<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['teacher_id'])) {
    die("Error: Lecturer not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quiz_title = $_POST['quiz_title'];
    $quiz_description = $_POST['quiz_description'];
    $teacher_id = $_SESSION['teacher_id']; // Use correct session key here

    $stmt = $conn->prepare("INSERT INTO quiz (quiz_title, quiz_description, date_added, teacher_id) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param("ssi", $quiz_title, $quiz_description, $teacher_id);

    if ($stmt->execute()) {
        header("Location: t_quiz.php?success=1");
        exit();
    } else {
        echo "Error inserting quiz: " . $stmt->error;
    }
}
?>
