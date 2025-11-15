<?php
session_start();
include 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = trim($_POST['Lecturer_name']);
    $password = $_POST['password'];

    // Check if fields are empty
    if (empty($login_input) || empty($password)) {
        $_SESSION['error'] = "Please enter username/email and password.";
        header("Location: register_teacher.php");
        exit();
    }

    // Get user from database
    $query = "SELECT u.user_id, u.email, u.password, u.firstname, u.lastname,
                     t.teacher_id, t.name, t.profile_image
              FROM users u
              INNER JOIN teacher t ON u.user_id = t.user_id
              WHERE (u.email = ? OR u.username = ?) AND u.user_type = 'teacher'
              LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $login_input, $login_input);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        $_SESSION['error'] = "Teacher account not found.";
        mysqli_stmt_close($stmt);
        header("Location: register_teacher.php");
        exit();
    }

    mysqli_stmt_bind_result($stmt, $user_id, $email, $hashed_password, 
                           $firstname, $lastname, $teacher_id, $name, $profile_image);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Check password
    if (!password_verify($password, $hashed_password)) {
        $_SESSION['error'] = "Incorrect password.";
        header("Location: register_teacher.php");
        exit();
    }

    // Login successful - set session
    $_SESSION['teacher_id'] = $teacher_id;
    $_SESSION['teacher_user_id'] = $user_id;
    $_SESSION['teacher_email'] = $email;
    $_SESSION['teacher_name'] = $name;
    $_SESSION['teacher_profile_image'] = $profile_image;
    $_SESSION['user_type'] = 'teacher';

    header("Location: teacher_dashboard.php");
    exit();
}
?>