<?php
include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $class_id = $_POST['class_id'];
    $status = $_POST['status'];

    // Handle profile image if uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $imageName = basename($_FILES["profile_image"]["name"]);
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFilePath = $targetDir . uniqid() . "_" . $imageName;
        move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetFilePath);
        $profileImage = $targetFilePath;

        $query = "UPDATE student SET name=?, email=?, password=?, class_id=?, status=?, profile_image=? WHERE student_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssissi", $name, $email, $password, $class_id, $status, $profileImage, $student_id);
    } else {
        $query = "UPDATE student SET name=?, email=?, password=?, class_id=?, status=? WHERE student_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssisi", $name, $email, $password, $class_id, $status, $student_id);
    }

    if ($stmt->execute()) {
        // ✅ Redirect after update to prevent resubmission
        header("Location: t_students.php?success=1");
        exit();
    } else {
        echo "Error updating student: " . $stmt->error;
    }
} else {
    echo "Invalid request.";
}
?>
