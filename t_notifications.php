<?php
include('teacher_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications | LMS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f4f4f4;
      font-family: 'Segoe UI', sans-serif;
    }

    .content-box {
      background: white;
      border-radius: 5px;
      padding: 20px;
      margin: 20px;
      box-shadow: 0 0 5px #ccc;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="content-box">
    <h4>🔔 Notifications</h4>
    <hr>

    <?php
    include('dbcon.php');

    $query = "SELECT * FROM teacher_notification ORDER BY date_of_notification DESC";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo '<div class="list-group">';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="list-group-item">';
            echo '<h6>' . htmlspecialchars($row['title']) . '</h6>';
            echo '<small>' . htmlspecialchars($row['date_of_notification']) . '</small>';
            echo '<p>' . htmlspecialchars($row['message']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p class="text-muted">No notifications found.</p>';
    }
    ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
