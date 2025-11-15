<?php
session_start();
include('dbcon.php');

$teacher_id = $_SESSION['teacher_id'] ?? null;
$message = "";

if (!$teacher_id) {
  header("Location: register_teacher.php");
  exit;
}

// Fetch current teacher data - FIXED: using 'name' instead of 'firstname, lastname'
$stmt = $conn->prepare("SELECT name, password, profile_image FROM teacher WHERE teacher_id = ?");

if (!$stmt) {
  die("Database error: " . $conn->error . "<br>Make sure 'teacher' table has columns: name, password, profile_image");
}

$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Password update
  if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $teacher['password'])) {
      $message = "<div class='alert alert-danger'>Current password is incorrect.</div>";
    } elseif ($new_password !== $confirm_password) {
      $message = "<div class='alert alert-warning'>New passwords do not match.</div>";
    } else {
      $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
      
      // Update password in both tables
      $stmt = $conn->prepare("UPDATE teacher SET password = ? WHERE teacher_id = ?");
      if (!$stmt) {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
      } else {
        $stmt->bind_param("si", $new_hashed_password, $teacher_id);
        $stmt->execute();
        $stmt->close();
        
        // Also update in users table
        $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE user_id = (SELECT user_id FROM teacher WHERE teacher_id = ?)");
        if ($stmt2) {
          $stmt2->bind_param("si", $new_hashed_password, $teacher_id);
          $stmt2->execute();
          $stmt2->close();
        }
        
        $message = "<div class='alert alert-success'>Password updated successfully.</div>";
        $teacher['password'] = $new_hashed_password; // Update local variable
      }
    }
  }

  // Profile image update
  if (!empty($_FILES['avatar']['name'])) {
    $file = $_FILES['avatar'];
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] === 0 && in_array($file_ext, $allowed_ext)) {
      $target_dir = "uploads/";
      if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
      $new_filename = uniqid("avatar_", true) . "." . $file_ext;
      $target_path = $target_dir . $new_filename;

      if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $stmt = $conn->prepare("UPDATE teacher SET profile_image = ? WHERE teacher_id = ?");
        if (!$stmt) {
          $message .= "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
        } else {
          $stmt->bind_param("si", $target_path, $teacher_id);
          $stmt->execute();
          $stmt->close();
          $message .= "<div class='alert alert-success'>Profile image updated successfully.</div>";
          
          // Update session variable
          $_SESSION['teacher_profile_image'] = $target_path;
          
          // Refresh to show new image
          header("Location: " . $_SERVER['PHP_SELF']);
          exit;
        }
      } else {
        $message .= "<div class='alert alert-danger'>Failed to upload image.</div>";
      }
    } else {
      $message .= "<div class='alert alert-danger'>Invalid image file. Only JPG, JPEG, PNG allowed.</div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Update Lecturer Profile | EduHub LMS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <style>
    body {
      margin: 0;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #121212;
      background-image:
        radial-gradient(circle at center, rgba(255, 255, 255, 0.1), transparent 80%),
        radial-gradient(circle at top left, rgba(255, 0, 150, 0.2), transparent 70%),
        radial-gradient(circle at bottom right, rgba(0, 200, 255, 0.2), transparent 70%);
      background-blend-mode: screen;
      color: #eee;
      padding: 20px;
    }

    .container {
      background: rgba(30, 30, 30, 0.55);
      border-radius: 20px;
      padding: 30px 40px;
      max-width: 600px;
      width: 100%;
      box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.9);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #ddd;
    }

    h3 {
      color: #00d4ff;
      margin-bottom: 10px;
    }

    .teacher-name {
      text-align: center;
      color: #aaa;
      margin-bottom: 25px;
      font-size: 18px;
    }

    .profile-pic {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(255, 255, 255, 0.4);
      margin-bottom: 20px;
      filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.2));
      transition: transform 0.3s ease;
    }

    .profile-pic:hover {
      transform: scale(1.05);
      filter: drop-shadow(0 0 8px #00ffff);
    }

    input.form-control {
      background: rgba(255, 255, 255, 0.08);
      border: none;
      color: #eee;
      border-radius: 8px;
      padding: 10px 15px;
      transition: background 0.3s ease;
    }

    input.form-control::placeholder {
      color: #bbb;
    }

    input.form-control:focus {
      background: rgba(0, 255, 255, 0.15);
      outline: none;
      box-shadow: 0 0 8px #00ffff;
      color: #fff;
    }

    label {
      font-weight: 600;
      color: #ccc;
    }

    button.btn-primary {
      background: rgba(0, 200, 255, 0.2);
      border: none;
      font-weight: 600;
      border-radius: 12px;
      padding: 12px 0;
      transition: background 0.3s ease, box-shadow 0.3s ease;
    }

    button.btn-primary:hover {
      background: #2563eb;
      box-shadow: 0 0 10px #376ce0cc;
    }

    .alert {
      font-weight: 600;
      border-radius: 10px;
      margin-bottom: 25px;
      padding: 15px 20px;
      text-align: center;
    }

    .alert-success {
      background-color: #00ffffaa;
      color: #222;
    }

    .alert-danger {
      background-color: #ff4444aa;
      color: #fff;
    }

    .alert-warning {
      background-color: #ffaa00aa;
      color: #222;
    }

    .back-link {
      text-align: center;
      margin-top: 20px;
    }

    .back-link a {
      color: #00d4ff;
      text-decoration: none;
      font-weight: 600;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <div class="container">
    <h3 class="text-center">Lecturer Profile Update</h3>
    <div class="teacher-name"><?php echo htmlspecialchars($teacher['name']); ?></div>

    <?php echo $message; ?>

    <form method="post" enctype="multipart/form-data" autocomplete="off">
      <div class="mb-4 text-center">
        <img src="<?php echo htmlspecialchars($teacher['profile_image'] ?? 'default.png'); ?>" alt="Profile Picture" class="profile-pic" />
        <input type="file" class="form-control" name="avatar" accept=".jpg,.jpeg,.png" />
      </div>

      <hr style="border-color: rgba(255,255,255,0.2);" />

      <h5>Change Password</h5>
      <div class="mb-3">
        <label for="current_password" class="form-label">Current Password</label>
        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" />
      </div>
      <div class="mb-3">
        <label for="new_password" class="form-label">New Password</label>
        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password" />
      </div>
      <div class="mb-4">
        <label for="confirm_password" class="form-label">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password" />
      </div>

      <button type="submit" class="btn btn-primary w-100">Update Profile</button>
    </form>

    <div class="back-link">
      <a href="teacher_dashboard.php">← Back to Dashboard</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>