<?php

$conn = mysqli_connect('localhost','root','','capstone');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>