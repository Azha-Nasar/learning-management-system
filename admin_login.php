<?php
session_start();
include 'dbcon.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter username and password.";
        header("Location: admin_login.php");
        exit();
    }

    // Check admin credentials
    $query = "SELECT user_id, password, firstname, lastname 
              FROM users 
              WHERE username = ? AND user_type = 'admin' 
              LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        $_SESSION['error'] = "Admin account not found.";
        mysqli_stmt_close($stmt);
        header("Location: admin_login.php");
        exit();
    }

    mysqli_stmt_bind_result($stmt, $user_id, $hashed_password, $firstname, $lastname);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Verify password
    if ($password === $hashed_password || password_verify($password, $hashed_password)) {
        $_SESSION['admin_id'] = $user_id;
        $_SESSION['admin_name'] = $firstname . ' ' . $lastname;
        $_SESSION['user_type'] = 'admin';
        
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Incorrect password.";
        header("Location: admin_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
    background: url('imgs/bg-image.jpg') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', sans-serif;
    position: relative;
}

/* Dark overlay layer */
body::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.51); /* Adjust the 0.6 for more or less darkness */
    z-index: 0;
}

/* Ensure login box appears above overlay */
.login-container {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95); /* slight transparency for modern look */
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    overflow: hidden;
    max-width: 1000px;
    width: 100%;
    display: flex;
}


        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #313750ff 0%, #4961caff 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-left h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #6c757d;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .btn-login {
            background: linear-gradient(135deg, #313750ff 0%, #4961caff 100%);
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div>
                <i class="fas fa-user-shield fa-4x mb-4"></i>
                <h1>Admin Portal</h1>
                <p>Manage your educational institution with powerful administrative tools</p>
                <div class="mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Comprehensive Dashboard</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>User Management</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-3 fa-lg"></i>
                        <span>Advanced Analytics</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back!</h2>
                <p>Sign in to access admin panel</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 text-white">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>