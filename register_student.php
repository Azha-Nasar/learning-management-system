<?php
session_start();
include 'dbcon.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch classes from DB for dropdown
$class_options = [];
$result = mysqli_query($conn, "SELECT class_id, class_name FROM class ORDER BY class_id ASC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $class_options[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = mysqli_real_escape_string($conn, trim($_POST['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($_POST['lastname']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $class_name = mysqli_real_escape_string($conn, trim($_POST['class']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $address = mysqli_real_escape_string($conn, trim($_POST['address']));
    $date_of_birth = mysqli_real_escape_string($conn, trim($_POST['date_of_birth']));
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];

    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($class_name) || empty($phone) || empty($address) || empty($date_of_birth) || empty($password)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: register_student.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: register_student.php");
        exit();
    }

    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $_SESSION['error'] = "Please enter a valid phone number (10-15 digits).";
        header("Location: register_student.php");
        exit();
    }

    if ($password !== $repassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: register_student.php");
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Password must be at least 6 characters long.";
        header("Location: register_student.php");
        exit();
    }

    $fullname = $firstname . ' ' . $lastname;

    // Check if email already exists
    $check_query = "SELECT student_id FROM student WHERE email = ? LIMIT 1";
    $stmt_check = mysqli_prepare($conn, $check_query);
    
    if (!$stmt_check) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: register_student.php");
        exit();
    }
    
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        $_SESSION['error'] = "Email already registered.";
        mysqli_stmt_close($stmt_check);
        header("Location: register_student.php");
        exit();
    }
    mysqli_stmt_close($stmt_check);

    // Check username in users table
    $check_user = "SELECT user_id FROM users WHERE username = ? LIMIT 1";
    $stmt_check_user = mysqli_prepare($conn, $check_user);
    
    if (!$stmt_check_user) {
        $_SESSION['error'] = "Database error: " . mysqli_error($conn);
        header("Location: register_student.php");
        exit();
    }
    
    mysqli_stmt_bind_param($stmt_check_user, "s", $email);
    mysqli_stmt_execute($stmt_check_user);
    mysqli_stmt_store_result($stmt_check_user);

    if (mysqli_stmt_num_rows($stmt_check_user) > 0) {
        $_SESSION['error'] = "Username already exists.";
        mysqli_stmt_close($stmt_check_user);
        header("Location: register_student.php");
        exit();
    }
    mysqli_stmt_close($stmt_check_user);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Get class_id
    $class_id = 0;
    foreach ($class_options as $class) {
        if ($class['class_name'] === $class_name) {
            $class_id = $class['class_id'];
            break;
        }
    }

    if ($class_id === 0) {
        $_SESSION['error'] = "Invalid class selected.";
        header("Location: register_student.php");
        exit();
    }

    // Handle profile image upload
    $profile_image = NULL;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'student_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $target_file;
            }
        }
    }

    mysqli_begin_transaction($conn);

    try {
        // Insert into users table
        $insert_user = "INSERT INTO users (username, password, firstname, lastname, user_type) VALUES (?, ?, ?, ?, 'student')";
        $stmt_user = mysqli_prepare($conn, $insert_user);
        
        if (!$stmt_user) {
            throw new Exception("Prepare failed for users table: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt_user, "ssss", $email, $hashed_password, $firstname, $lastname);
        
        if (!mysqli_stmt_execute($stmt_user)) {
            throw new Exception("Execute failed for users table: " . mysqli_stmt_error($stmt_user));
        }
        
        $new_user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_user);

        // Generate student number
        $year = date('Y');
        $count_query = "SELECT COUNT(*) as total FROM student";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);
        $student_count = $count_row['total'] + 1;
        $student_number = "STU" . $year . str_pad($student_count, 4, '0', STR_PAD_LEFT);

        $enrollment_date = date('Y-m-d');

        // Insert into student table with ALL fields
        $insert_student = "INSERT INTO student (
            user_id, 
            name, 
            email, 
            password, 
            class_id, 
            student_number, 
            phone, 
            address, 
            date_of_birth, 
            enrollment_date, 
            profile_image, 
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        
        $stmt_student = mysqli_prepare($conn, $insert_student);
        
        if (!$stmt_student) {
            throw new Exception("Prepare failed for student table: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt_student, "isssississs", 
            $new_user_id, 
            $fullname, 
            $email, 
            $hashed_password, 
            $class_id, 
            $student_number,
            $phone,
            $address,
            $date_of_birth,
            $enrollment_date,
            $profile_image
        );
        
        if (!mysqli_stmt_execute($stmt_student)) {
            throw new Exception("Execute failed for student table: " . mysqli_stmt_error($stmt_student));
        }
        
        mysqli_stmt_close($stmt_student);

        mysqli_commit($conn);

        // ✅ FIXED: Set success message with student number
        $_SESSION['registration_success'] = "Registration successful! Your student number is: <strong>" . $student_number . "</strong>. You can now login with your email.";
        
        // ✅ FIXED: Redirect to same page to show success message
        header("Location: register_student.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: register_student.php");
        exit();
    }
} 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduHub LMS - Student Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
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
            padding: 40px 20px;
        }

        .content-box {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background: transparent;
        }

        .left-content, .right-content {
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
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #ccc;
            color: white;
        }

        .form-control::placeholder {
            color: #e0e0e0;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .form-select option {
            color: black;
            background-color: white;
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
            text-decoration: none;
        }

        a:hover {
            color: #fff;
            text-decoration: underline;
        }

        footer {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-align: center;
            padding: 12px 0;
        }

        .alert {
            border-radius: 5px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.3rem;
        }

        /* ✅ NEW: Success alert styling */
        .alert-success {
            background-color: rgba(40, 167, 69, 0.9);
            border: 2px solid #28a745;
            color: white;
            font-weight: 600;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .content-box {
                flex-direction: column;
                text-align: center;
            }

            .left-content, .right-content {
                border: none;
            }

            .form-container {
                margin: auto;
            }
        }
    </style>
</head>

<body>
    <div class="overlay">
        <div class="content-box">
            <div class="left-content">
                <h1 class="display-5">EduHub LMS</h1>
                <p class="lead mt-3">Excellence, Competence and Educational Leadership in Science and Technology.</p>
            </div>
            <div class="right-content">
                <div class="form-container">
                    <h3 class="text-center mb-4">Student Registration</h3>
                    
                    <?php
                    // ✅ FIXED: Show success message with student number
                    if (isset($_SESSION['registration_success'])) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                        echo $_SESSION['registration_success'];
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                        echo '<div class="text-center mb-3">';
                        echo '<a href="index.php" class="btn btn-light">Go to Login Page</a>';
                        echo '</div>';
                        unset($_SESSION['registration_success']);
                    }
                    
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                        echo htmlspecialchars($_SESSION['error']);
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                        unset($_SESSION['error']);
                    }
                    ?>
                    
                    <form action="register_student.php" method="POST" enctype="multipart/form-data" id="registrationForm">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="firstname" class="form-control" placeholder="First Name" 
                                       value="<?= isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="lastname" class="form-control" placeholder="Last Name" 
                                       value="<?= isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" placeholder="1234567890" 
                                       pattern="[0-9]{10,15}" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Date of Birth *</label>
                                <input type="date" name="date_of_birth" class="form-control" 
                                       value="<?= isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : '' ?>" 
                                       max="<?= date('Y-m-d', strtotime('-10 years')) ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Class *</label>
                                <select name="class" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($class_options as $class) : ?>
                                        <option value="<?= htmlspecialchars($class['class_name']) ?>"
                                                <?= (isset($_POST['class']) && $_POST['class'] === $class['class_name']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($class['class_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Address *</label>
                                <textarea name="address" class="form-control" placeholder="Full Address" rows="2" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Profile Picture (Optional)</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif">
                                <small style="color: #ccc;">JPG, PNG, GIF (Max 5MB)</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" 
                                       minlength="6" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="repassword" class="form-control" placeholder="Re-type Password" 
                                       minlength="6" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">Complete Registration</button>
                        <div class="text-center mt-3">
                            <a href="index.php">Already have an account? Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p class="mb-0">&copy; 2025 EduHub LMS. All Rights Reserved.</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const repassword = document.querySelector('input[name="repassword"]').value;
            
            if (password !== repassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>

</html>