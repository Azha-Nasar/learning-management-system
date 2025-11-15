<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's class
$class_query = "SELECT class_id FROM student WHERE student_id = ?";
$stmt = $conn->prepare($class_query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$class_result = $stmt->get_result()->fetch_assoc();
$student_class_id = $class_result['class_id'];

// Fetch available materials
$materials_query = "SELECT 
                      f.*,
                      c.class_name,
                      t.name as teacher_name
                    FROM files f
                    JOIN class c ON f.class_id = c.class_id
                    JOIN teacher t ON f.uploaded_by = t.teacher_id
                    WHERE f.class_id = ?
                    ORDER BY f.upload_date DESC";

$stmt = $conn->prepare($materials_query);
$stmt->bind_param("i", $student_class_id);
$stmt->execute();
$materials = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Downloadable Materials | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .material-card {
            background: white;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .material-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .material-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .icon-pdf {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
        }

        .icon-doc {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .icon-ppt {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .icon-default {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }

        .material-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .material-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <h4 class="mb-4">
        <i class="fas fa-download me-2"></i>Downloadable Materials
    </h4>
    <hr>

    <?php if ($materials->num_rows > 0): ?>
        <?php while($material = $materials->fetch_assoc()): 
            $ext = strtolower(pathinfo($material['file_name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, ['pdf'])) {
                $icon_class = 'icon-pdf';
                $icon = 'fa-file-pdf';
            } elseif (in_array($ext, ['doc', 'docx'])) {
                $icon_class = 'icon-doc';
                $icon = 'fa-file-word';
            } elseif (in_array($ext, ['ppt', 'pptx'])) {
                $icon_class = 'icon-ppt';
                $icon = 'fa-file-powerpoint';
            } else {
                $icon_class = 'icon-default';
                $icon = 'fa-file';
            }
        ?>
            <div class="material-card">
                <div class="d-flex align-items-center">
                    <div class="material-icon <?= $icon_class ?>">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="material-title"><?= htmlspecialchars($material['file_name']) ?></div>
                        <div class="material-meta">
                            <i class="fas fa-book me-1"></i><?= htmlspecialchars($material['class_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($material['teacher_name']) ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i><?= date('M d, Y', strtotime($material['upload_date'])) ?>
                        </div>
                        <?php if (!empty($material['description'])): ?>
                            <p class="mb-0 mt-2 text-muted" style="font-size: 0.9rem;">
                                <?= nl2br(htmlspecialchars($material['description'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= htmlspecialchars($material['file_path']) ?>" 
                       class="btn btn-primary ms-3" 
                       download>
                        <i class="fas fa-download me-1"></i>Download
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No materials available for your class at this time.
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>