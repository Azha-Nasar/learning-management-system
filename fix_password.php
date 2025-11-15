<?php
include 'dbcon.php';

// Change these to YOUR details
$user_id = 8;  // Afraaz's user_id
$plain_password = "afraz";  // Current password

// Hash it
$hashed = password_hash($plain_password, PASSWORD_DEFAULT);

// Update database
$query = "UPDATE teacher SET password = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);

if (mysqli_stmt_execute($stmt)) {
    echo "✓ Password updated successfully!<br>";
    echo "You can now login with username/email: Afraz <br>";
    echo "Password: afraz <br><br>";
    echo "<strong>Now DELETE this file!</strong>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>