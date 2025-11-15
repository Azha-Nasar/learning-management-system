<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all announcements from users table
$announcements_query = "SELECT 
                          a.id,
                          a.title,
                          a.message,
                          a.poster,
                          a.role,
                          a.created_at,
                          u.firstname,
                          u.lastname,
                          u.user_type
                        FROM announcements a
                        JOIN users u ON a.posted_by = u.user_id
                        WHERE u.user_type IN ('admin', 'teacher')
                        ORDER BY a.created_at DESC";

$announcements = $conn->query($announcements_query);

if (!$announcements) {
    die("Query failed: " . $conn->error);
}

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Announcements | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        .announcement-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .announcement-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .announcement-poster {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .announcement-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .announcement-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .announcement-message {
            font-size: 1rem;
            line-height: 1.6;
            color: #495057;
        }

        .role-badge {
            display: inline-block;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-teacher {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-admin {
            background: #fce4ec;
            color: #c2185b;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="page-header-simple">
        <h4>
            <i class="fas fa-bullhorn me-2"></i>Announcements <hr>
        </h4>
    </div>

    <?php if ($announcements->num_rows > 0): ?>
        <?php while($announcement = $announcements->fetch_assoc()): ?>
            <div class="announcement-card">
                <div class="row">
                    <div class="<?= !empty($announcement['poster']) ? 'col-md-9' : 'col-md-12' ?>">
                        <div class="announcement-title">
                            <?= htmlspecialchars($announcement['title']) ?>
                        </div>
                        <div class="announcement-meta">
                            <span class="role-badge badge-<?= htmlspecialchars($announcement['role']) ?>">
                                <i class="fas fa-<?= $announcement['role'] == 'teacher' ? 'chalkboard-teacher' : 'user-shield' ?> me-1"></i>
                                <?= ucfirst(htmlspecialchars($announcement['role'])) ?>
                            </span>
                            <span>
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($announcement['firstname'] . ' ' . $announcement['lastname']) ?>
                            </span>
                            <span>
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                            </span>
                            <span>
                                <i class="fas fa-clock me-1"></i>
                                <?= date('h:i A', strtotime($announcement['created_at'])) ?>
                            </span>
                        </div>
                        <div class="announcement-message">
                            <?= nl2br(htmlspecialchars($announcement['message'])) ?>
                        </div>
                    </div>
                    <?php if (!empty($announcement['poster'])): ?>
                        <div class="col-md-3 text-center">
                            <img src="<?= htmlspecialchars($announcement['poster']) ?>" 
                                 alt="Announcement Poster" 
                                 class="announcement-poster img-fluid"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3" style="opacity: 0.3;"></i>
            <h5>No announcements available</h5>
            <p class="text-muted">Check back later for updates from your teachers and administrators.</p>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>