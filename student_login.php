<?php
session_start();
include 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($name) || empty($password)) {
        $_SESSION['error'] = "Please enter your name and password.";
        header("Location: index.php");
        exit();
    }

    $query = "SELECT student_id, password, name FROM student WHERE name = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $student_id, $hashed_password, $name);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashed_password)) {
            $_SESSION['student_id'] = $student_id;
            $_SESSION['student_name'] = $name;
            
            // ✅ NEW: Set login success message
            $_SESSION['login_success'] = "Welcome back, " . $name . "! Login successful.";
            
            header("Location: student_Dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['error'] = "User not found.";
    }

    mysqli_stmt_close($stmt);
    header("Location: index.php");
    exit();
}
?>