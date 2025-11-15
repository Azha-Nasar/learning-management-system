<?php
include("dbcon.php");
session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM teacher_class WHERE teacher_class_id = $id");
}

header("Location: t_my_class.php");
exit;
