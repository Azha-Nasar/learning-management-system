<?php
session_start();   
include 'dbcon.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>EduHub-Learning Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">

</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand" href="#">EduHub Learning Management System</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="#">About</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Calendar of Events</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Directories</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Campuses</a></li>
        <li class="nav-item"><a class="nav-link" href="#">History</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Developers</a></li>
      </ul>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container-fluid login-bg">
    <div class="overlay d-flex align-items-center justify-content-center">
      <div class="row w-100 px-5">
        <!-- Left Text -->
        <!-- Left Logo & Text -->
        <div class="col-md-6 d-flex flex-column justify-content-center text-left-box">
          <img src="imgs\EduHub logo Lms-modified.png" alt="EduHub Logo" class="logo-img" />
          <h1 class="mt-2 text-white">EDUHUB LMS</h1>
          <p>Excellence, Competence and Educational Leadership in Science and Technology</p>
        </div>

        <!-- Right Forms -->
        <div class="col-md-6">
          <div class="card bg-light p-4 mb-3 shadow-sm">
            <h5 class="mb-3 text-center"><i class="bi bi-lock"></i> Sign in</h5>

            <?php
            if (isset($_SESSION['error'])) {
              echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
              unset($_SESSION['error']);
            }
            ?>

            <form method="POST" action="student_login.php">
              <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
              <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
              <button type="submit" class="btn btn-primary w-100">Sign in</button>
            </form>
          </div>

          <div class="card bg-light p-3 text-center shadow-sm">
            <p>New to EduHub?</p>
            <div class="d-flex justify-content-around">
              <a href="register_student.php" class="btn btn-outline-primary">I’m a Student</a>
              <a href="register_teacher.php" class="btn btn-outline-secondary">I’m a Teacher</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Footer Section -->
    <footer>
      <div class="container">
        <small>© 2025 EduHub LMS. All rights reserved. |
          <a href="#">Privacy Policy</a> |
          <a href="#">Terms & Conditions</a>
        </small>
      </div>
    </footer>
  </div>
  </div>

</body>

</html>