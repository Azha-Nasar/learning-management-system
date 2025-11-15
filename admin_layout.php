<?php
// admin_layout.php - Save this file in your root directory
if (session_status() == PHP_SESSION_NONE) session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// Get current page for active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | EduHub LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 65px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #0f172a;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }

        .sidebar-brand:hover {
            color: white;
        }

        .sidebar-brand i {
            font-size: 1.8rem;
        }

        .sidebar-brand h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            padding: 0 20px 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 0.95rem;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-link.active {
            background: var(--sidebar-hover);
            color: white;
            border-left-color: var(--primary-color);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .topbar-left h5 {
            margin: 0;
            color: #1e293b;
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-search {
            position: relative;
        }

        .topbar-search input {
            padding: 8px 15px 8px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            width: 250px;
            transition: all 0.3s;
        }

        .topbar-search input:focus {
            outline: none;
            border-color: var(--primary-color);
            width: 300px;
        }

        .topbar-search i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .notification-badge {
            position: relative;
            cursor: pointer;
        }

        .notification-badge i {
            font-size: 1.3rem;
            color: #64748b;
        }

        .notification-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .user-menu:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .content-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 30px;
            min-height: calc(100vh - var(--topbar-height));
        }

        .page-header {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .page-header h4 {
            margin: 0;
            color: #1e293b;
            font-weight: 700;
        }

        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
            font-size: 0.9rem;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 10px;
        }

        .dropdown-item {
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .dropdown-item:hover {
            background: #f1f5f9;
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .topbar {
                left: 0;
            }
            
            .content-wrapper {
                margin-left: 0;
            }

            .topbar-search input {
                width: 150px;
            }

            .topbar-search input:focus {
                width: 200px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="admin_dashboard.php" class="sidebar-brand">
                <i class="fas fa-graduation-cap"></i>
                <h4>EduHub Admin</h4>
            </a>
        </div>

        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-section-title">Main</div>
                <a href="admin_dashboard.php" class="nav-link <?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin_analytics.php" class="nav-link <?= $current_page == 'admin_analytics.php' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Management</div>
                <a href="admin_students.php" class="nav-link <?= $current_page == 'admin_students.php' ? 'active' : '' ?>">
                    <i class="fas fa-user-graduate"></i>
                    <span>Students</span>
                </a>
                <a href="admin_teachers.php" class="nav-link <?= $current_page == 'admin_teachers.php' ? 'active' : '' ?>">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Lecturers</span>
                </a>
                <a href="admin_departments.php" class="nav-link <?= $current_page == 'admin_departments.php' ? 'active' : '' ?>">
                    <i class="fas fa-building"></i>
                    <span>Departments</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Academic</div>
                <a href="admin_assignments.php" class="nav-link <?= $current_page == 'admin_assignments.php' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
                </a>
                <a href="admin_quizzes.php" class="nav-link <?= $current_page == 'admin_quizzes.php' ? 'active' : '' ?>">
                    <i class="fas fa-question-circle"></i>
                    <span>Quizzes</span>
                </a>
                <a href="admin_timetable.php" class="nav-link <?= $current_page == 'admin_timetable.php' ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timetable</span>
                </a>
                <a href="admin_library.php" class="nav-link <?= $current_page == 'admin_library.php' ? 'active' : '' ?>">
                    <i class="fas fa-book-reader"></i>
                    <span>Library</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Communication</div>
                <a href="admin_announcements.php" class="nav-link <?= $current_page == 'admin_announcements.php' ? 'active' : '' ?>">
                    <i class="fas fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="admin_messages.php" class="nav-link <?= $current_page == 'admin_messages.php' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Settings</div>
                <a href="admin_settings.php" class="nav-link <?= $current_page == 'admin_settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="admin_reports.php" class="nav-link <?= $current_page == 'admin_reports.php' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
                <a href="admin_logs.php" class="nav-link <?= $current_page == 'admin_logs.php' ? 'active' : '' ?>">
                    <i class="fas fa-history"></i>
                    <span>Activity Logs</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="btn btn-link d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="d-none d-md-block">Administrator Panel</h5>
        </div>

        <div class="topbar-right">

            <div class="dropdown">
                <div class="user-menu" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <?= strtoupper(substr($admin_name, 0, 1)) ?>
                    </div>
                    <div class="d-none d-md-block">
                        <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($admin_name) ?></div>
                        <small style="color: #64748b;">Administrator</small>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #94a3b8;"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-user"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="admin_settings.php"><i class="fas fa-cog"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mobile sidebar toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('show');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});
</script>