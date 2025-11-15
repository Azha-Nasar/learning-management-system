<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include('dbcon.php');

$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
  header("Location: index.php");
  exit;
}

$query = "SELECT name, profile_image FROM student WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$student_name = $row['name'] ?? 'Student';
$profile_image = $row['profile_image'] ?? 'default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | EduHub LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }

        /* CRITICAL FIX: Sidebar z-index must be lower than modals */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 240px;
            background-color: #2c3e50;
            color: white;
            z-index: 50; /* REDUCED FROM 100 */
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            padding-top: 60px;
        }

        .sidebar h5 {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: white;
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            width: 18px;
            text-align: center;
            font-size: 0.95rem;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 240px;
            right: 0;
            height: 60px;
            background: #2c3e50;
            color: white;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 60; /* REDUCED FROM 99 */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .topbar .dropdown {
            position: relative;
            z-index: 70; /* ENSURE DROPDOWN WORKS */
        }

        .topbar h6 {
            margin: 0;
            font-weight: 500;
            font-size: 1rem;
            color: white;
        }

        .content {
            margin-left: 240px;
            margin-top: 60px;
            padding: 30px;
            height: calc(100vh - 60px);
            overflow-y: auto;
            overflow-x: hidden;
            background-color: #ecf0f1;
            position: relative;
            z-index: 1; /* REDUCED FROM AUTO */
        }

        .dropdown-toggle {
            color: white !important;
            text-decoration: none;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.5em;
            vertical-align: middle;
        }

        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
            z-index: 1051 !important;
            margin-top: 0.5rem !important;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        /* ===== CRITICAL MODAL FIX ===== */
        /* Ensure modals always appear above everything */
        .modal-backdrop {
            z-index: 1040 !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        
        .modal {
            z-index: 1050 !important;
            display: none;
        }
        
        .modal.show {
            display: block !important;
        }
        
        .modal-dialog {
            z-index: 1060 !important;
            margin: 1.75rem auto;
            position: relative;
        }

        .modal-content {
            position: relative;
            z-index: 1070 !important;
            background-color: #fff;
            border-radius: 0.5rem;
        }

        /* Ensure modal is clickable */
        body.modal-open {
            overflow: hidden;
        }

        body.modal-open .modal {
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Scrollbar styling */
        .content::-webkit-scrollbar {
            width: 8px;
        }

        .content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .content::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .content::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Ensure page header is always visible */
        .page-header-simple {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-radius: 0;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #dee2e6;
            position: relative;
            z-index: 1;
        }

        .page-header-simple h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-2">
        <h5 class="text-center mt-3 mb-4">📘 EduHub Learning</h5>
        <a href="student_Dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i> Home</a>
        <a href="s_notifications.php" class="nav-link"><i class="fas fa-bell me-2"></i> Notifications</a>
        <a href="s_messages.php" class="nav-link"><i class="fas fa-comment-alt me-2"></i> Messages</a>
        <a href="s_subject_overview.php" class="nav-link"><i class="fas fa-book me-2"></i> Subject Overview</a>
        <a href="s_classes.php" class="nav-link"><i class="fas fa-users me-2"></i> My Classes</a>
        <a href="s_classmates.php" class="nav-link"><i class="fas fa-users me-2"></i> My Classmates</a>
        <a href="s_progress.php" class="nav-link"><i class="fas fa-chalkboard-teacher me-2"></i> My Progress</a>
        <a href="s_materials.php" class="nav-link"><i class="fas fa-file-download me-2"></i> Downloadable Materials</a>
        <a href="s_assignments.php" class="nav-link"><i class="fas fa-upload me-2"></i> Assignments</a>
        <a href="s_quiz.php" class="nav-link"><i class="fas fa-folder-open me-2"></i> Quiz</a>
        <a href="s_announcements.php" class="nav-link"><i class="fas fa-clock me-2"></i> Announcements</a>
        <a href="s_calendar.php" class="nav-link"><i class="fas fa-calendar-alt me-2"></i> Calendar</a>
        <a href="library_student.php" class="nav-link"><i class="fas fa-book-open me-2"></i> Library</a>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <h6 class="mb-0">Student Portal</h6>
        <div class="dropdown">
            <a href="#" class="dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($student_name); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="update_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="content">
    
<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Ensure dropdown works properly
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });

    // CRITICAL FIX: Ensure modals work properly
    document.addEventListener('show.bs.modal', function (event) {
        // Remove any existing backdrops first
        const existingBackdrops = document.querySelectorAll('.modal-backdrop');
        existingBackdrops.forEach(backdrop => backdrop.remove());
        
        // Ensure modal shows above everything
        setTimeout(function() {
            var modal = event.target;
            modal.style.display = 'block';
            modal.style.zIndex = '1050';
            
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.zIndex = '1040';
            }
        }, 10);
    });

    // Clean up on modal hide
    document.addEventListener('hidden.bs.modal', function (event) {
        // Remove any lingering backdrops
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Remove modal-open class from body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    // Force backdrop click to close modal
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-backdrop')) {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modalInstance = bootstrap.Modal.getInstance(openModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        }
    });
});
</script>