<?php
session_start();
include('dbcon.php');

header('Content-Type: application/json');

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, class_name, subject, start_datetime, end_datetime, description FROM lecturer_timetable WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['subject'] . " - " . $row['class_name'],
        'start' => $row['start_datetime'],
        'end' => $row['end_datetime'],
        'description' => $row['description']
    ];
}

echo json_encode($events);
