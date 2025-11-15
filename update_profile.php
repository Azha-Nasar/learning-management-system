<?php
session_start();
include('dbcon.php');

$student_id = $_SESSION['student_id'] ?? null;

// Redirect if not logged in
if (!$student_id) {
    header("Location: login.php");
    exit;
}

// Fetch current student data
$stmt = $conn->prepare("SELECT name, password, profile_image FROM student WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['msg'] = ""; // Reset session message

    // --- PASSWORD UPDATE ---
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!password_verify($current_password, $student['password'])) {
            $_SESSION['msg'] .= "<div class='alert alert-danger'>Current password is incorrect.</div>";
        } elseif ($new_password !== $confirm_password) {
            $_SESSION['msg'] .= "<div class='alert alert-warning'>New passwords do not match.</div>";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE student SET password = ? WHERE student_id = ?");
            $stmt->bind_param("si", $new_hashed_password, $student_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['msg'] .= "<div class='alert alert-success'>Password updated successfully.</div>";
        }
    }

    // --- PROFILE IMAGE UPDATE ---
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
                $stmt = $conn->prepare("UPDATE student SET profile_image = ? WHERE student_id = ?");
                $stmt->bind_param("si", $target_path, $student_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['msg'] .= "<div class='alert alert-success'>Profile image updated successfully.</div>";
            } else {
                $_SESSION['msg'] .= "<div class='alert alert-danger'>Failed to upload image.</div>";
            }
        } else {
            $_SESSION['msg'] .= "<div class='alert alert-danger'>Invalid image file. Only JPG, JPEG, PNG allowed.</div>";
        }
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display message if exists
$message = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Update Profile | EduHub LMS</title>
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
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.12);
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
    }

    input.form-control::placeholder { color: #bbb; }
    input.form-control:focus {
      background: rgba(0, 255, 255, 0.15);
      outline: none;
      box-shadow: 0 0 8px #00ffff;
      color: #fff;
    }

    label { font-weight: 600; color: #ccc; }

    button.btn-primary {
      background: rgba(0, 200, 255, 0.2);
      border: none;
      font-weight: 600;
      letter-spacing: 0.05em;
      box-shadow: 0 0 6px rgba(0, 200, 255, 0.2);
      transition: background 0.3s ease, box-shadow 0.3s ease;
      border-radius: 12px;
      padding: 12px 0;
    }

    button.btn-primary:hover {
      background: #2563eb;
      box-shadow: 0 0 10px #376ce0cc;
    }

    .alert {
      font-weight: 600;
      color: #222;
      background-color: #00ffffaa;
      border: none;
      border-radius: 10px;
      margin-bottom: 25px;
      padding: 15px 20px;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="container">
    <h3 class="mb-4 text-center">Update Profile</h3>

    <?php echo $message; ?>

    <form method="post" enctype="multipart/form-data" autocomplete="off">
      <div class="mb-4 text-center">
        <img src="<?php echo htmlspecialchars($student['profile_image'] ?? 'default.png'); ?>" alt="Profile Picture" class="profile-pic" />
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
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
