<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EduHub LMS - Teacher Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            background: url('imgs/bg-image.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            display: flex;
            flex-direction: column;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.44);
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .content-box {
            display: flex;
            width: 100%;
            max-width: 1100px;
            background: transparent;
        }

        .left-content,
        .right-content {
            flex: 1;
            padding: 30px;
        }

        .left-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .left-content h1 {
            font-weight: bold;
            font-size: 2.5rem;
        }

        .right-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #ccc;
            color: white;
        }

        .form-control::placeholder {
            color: #e0e0e0;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }

        a {
            color: #ccc;
        }

        footer {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            padding: 12px 0;
        }

        @media (max-width: 768px) {
            .content-box {
                flex-direction: column;
                text-align: center;
            }

            .left-content,
            .right-content {
                border: none;
            }

            .form-container {
                margin: auto;
            }
        }
    </style>
</head>

<body>

    <!-- Overlay Content -->
    <div class="overlay">
        <div class="content-box">
            <!-- Left Section -->
            <div class="left-content">
                <h1 class="display-5">EduHub LMS</h1>
                <p class="lead mt-3">Excellence, Competence and Educational Leadership in Science and Technology.</p>
            </div>

            <!-- Right Section: Teacher Login Form -->
            <div class="right-content">
                <div class="form-container">
                    <h3 class="text-center mb-4">Lecturer Login</h3>
                    <form action="teacher_login.php" method="POST">

                        <?php
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>

                        <div class="mb-3">
                            <input type="text" name="Lecturer_name" class="form-control" placeholder="UserName " required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; 2025 EduHub LMS. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>