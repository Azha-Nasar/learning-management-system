<?php
session_start();
include('teacher_layout.php');
include("dbcon.php");

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    echo "<div class='p-4'>You are not logged in.</div>";
    exit;
}

$selected_class = $_GET['teacher_class_id'] ?? null;

// Fetch classes taught by this teacher
$classes_query = "SELECT tc.teacher_class_id, c.class_name 
                  FROM teacher_class tc
                  INNER JOIN class c ON tc.class_id = c.class_id
                  WHERE tc.teacher_id = ? AND tc.status = 'active'
                  ORDER BY c.class_name";
$stmt = $conn->prepare($classes_query);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes = $stmt->get_result();
$stmt->close();

// Class statistics
$class_stats = null;
$students_data = [];

if ($selected_class) {
    // Get class averages and total students
    $class_stats_query = "
        SELECT 
            COUNT(DISTINCT tcs.student_id) as total_students,
            COALESCE(AVG(CASE WHEN sa.grade IS NOT NULL AND sa.grade != '' THEN CAST(sa.grade AS UNSIGNED) END), 0) as avg_assignment_score,
            COALESCE(AVG(CASE WHEN scq.grade IS NOT NULL AND scq.grade != '' THEN CAST(scq.grade AS UNSIGNED) END), 0) as avg_quiz_score
        FROM teacher_class_student tcs
        LEFT JOIN student_assignment sa ON tcs.student_id = sa.student_id
        LEFT JOIN student_class_quiz scq ON tcs.student_id = scq.student_id
        WHERE tcs.teacher_class_id = ?
    ";
    $stmt = $conn->prepare($class_stats_query);
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $class_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Get individual student progress
    $progress_query = "
        SELECT 
            s.student_id,
            s.name,
            s.email,
            s.status as student_status,
            COALESCE(COUNT(DISTINCT sa.assignment_id), 0) as assignments_submitted,
            COALESCE(AVG(CASE WHEN sa.grade IS NOT NULL AND sa.grade != '' THEN CAST(sa.grade AS UNSIGNED) END), 0) as avg_assignment_score,
            COALESCE(COUNT(DISTINCT scq.class_quiz_id), 0) as quizzes_taken,
            COALESCE(AVG(CASE WHEN scq.grade IS NOT NULL AND scq.grade != '' THEN CAST(scq.grade AS UNSIGNED) END), 0) as avg_quiz_score
        FROM teacher_class_student tcs
        INNER JOIN student s ON tcs.student_id = s.student_id
        LEFT JOIN student_assignment sa ON s.student_id = sa.student_id
        LEFT JOIN student_class_quiz scq ON s.student_id = scq.student_id
        WHERE tcs.teacher_class_id = ?
        GROUP BY s.student_id, s.name, s.email, s.status
        ORDER BY s.name ASC
    ";
    
    $stmt = $conn->prepare($progress_query);
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Calculate overall average
        $overall_avg = ($row['avg_assignment_score'] + $row['avg_quiz_score']) / 2;
        
        // Determine status with 5 levels based on overall performance
        if ($overall_avg >= 80) {
            $status = 'Excellent';
            $status_class = 'success';
            $status_icon = '🌟';
        } elseif ($overall_avg >= 70) {
            $status = 'Good';
            $status_class = 'info';
            $status_icon = '✓';
        } elseif ($overall_avg >= 60) {
            $status = 'Fair';
            $status_class = 'warning';
            $status_icon = '⚠';
        } elseif ($overall_avg >= 50) {
            $status = 'Poor';
            $status_class = 'danger';
            $status_icon = '⚠';
        } else {
            $status = 'Critical';
            $status_class = 'danger';
            $status_icon = '🚨';
        }
        
        $row['overall_avg'] = $overall_avg;
        $row['status'] = $status;
        $row['status_class'] = $status_class;
        $row['status_icon'] = $status_icon;
        
        $students_data[] = $row;
    }
    $stmt->close();
}

// Calculate grade distribution
$grade_distribution = [
    'Excellent' => 0,
    'Good' => 0,
    'Fair' => 0,
    'Poor' => 0,
    'Critical' => 0
];

foreach ($students_data as $student) {
    $grade_distribution[$student['status']]++;
}
?>

<style>
.content-box {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.content-box h4 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0;
}

.content-box hr {
    border: 0;
    border-top: 2px solid #e9ecef;
    margin-top: 0.75rem;
    margin-bottom: 1.25rem;
}

.progress-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

.class-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-box {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.stat-box.blue { border-left-color: #3498db; }
.stat-box.green { border-left-color: #2ecc71; }
.stat-box.purple { border-left-color: #9b59b6; }
.stat-box.orange { border-left-color: #e67e22; }

.stat-box h6 {
    font-size: 0.8rem;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.stat-box .value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
}

.student-card {
    background: white;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
}

.student-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f0f0f0;
}

.student-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.status-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.metric-item {
    text-align: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.metric-item .label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    margin-bottom: 0.25rem;
}

.metric-item .value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2c3e50;
}

.progress-bar-container {
    height: 8px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-bar {
    height: 100%;
    transition: width 0.3s ease;
}

.distribution-chart {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 0.5rem;
    margin-top: 1rem;
}

.distribution-item {
    text-align: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.distribution-item .count {
    font-size: 1.5rem;
    font-weight: 700;
}

.distribution-item .label {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>

<div class="container">
    <div class="content-box">
        <h4>📊 Student Progress Reports</h4>
        <hr>

    <!-- Class Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Select Class:</label>
                    <select name="teacher_class_id" class="form-select" required>
                        <option value="">-- Choose a Class --</option>
                        <?php 
                        $classes->data_seek(0);
                        while ($class = $classes->fetch_assoc()): 
                        ?>
                            <option value="<?= $class['teacher_class_id'] ?>" 
                                <?= $selected_class == $class['teacher_class_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class['class_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">View Report</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_class && $class_stats): ?>
        <!-- Class Statistics -->
        <div class="class-stats">
            <div class="stat-box blue">
                <h6>Total Students</h6>
                <div class="value"><?= $class_stats['total_students'] ?></div>
            </div>
            <div class="stat-box green">
                <h6>Avg Assignment</h6>
                <div class="value"><?= number_format($class_stats['avg_assignment_score'], 1) ?>%</div>
            </div>
            <div class="stat-box purple">
                <h6>Avg Quiz</h6>
                <div class="value"><?= number_format($class_stats['avg_quiz_score'], 1) ?>%</div>
            </div>
            <div class="stat-box orange">
                <h6>Overall Average</h6>
                <div class="value">
                    <?= number_format(($class_stats['avg_assignment_score'] + $class_stats['avg_quiz_score']) / 2, 1) ?>%
                </div>
            </div>
        </div>

        <!-- Grade Distribution -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Grade Distribution</h5>
                <div class="distribution-chart">
                    <div class="distribution-item">
                        <div class="count text-success"><?= $grade_distribution['Excellent'] ?></div>
                        <div class="label">Excellent</div>
                    </div>
                    <div class="distribution-item">
                        <div class="count text-info"><?= $grade_distribution['Good'] ?></div>
                        <div class="label">Good</div>
                    </div>
                    <div class="distribution-item">
                        <div class="count text-warning"><?= $grade_distribution['Fair'] ?></div>
                        <div class="label">Fair</div>
                    </div>
                    <div class="distribution-item">
                        <div class="count text-danger"><?= $grade_distribution['Poor'] ?></div>
                        <div class="label">Poor</div>
                    </div>
                    <div class="distribution-item">
                        <div class="count text-danger"><?= $grade_distribution['Critical'] ?></div>
                        <div class="label">Critical</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Cards -->
        <h5 class="mb-3">Individual Student Performance</h5>
        <?php if (empty($students_data)): ?>
            <div class="alert alert-info">No students enrolled in this class.</div>
        <?php else: ?>
            <?php foreach ($students_data as $student): ?>
                <div class="student-card">
                    <div class="student-header">
                        <div>
                            <div class="student-name">
                                <?= $student['status_icon'] ?> <?= htmlspecialchars($student['name']) ?>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($student['email']) ?></small>
                            <?php if ($student['student_status'] == 'Suspended'): ?>
                                <span class="badge bg-secondary ms-2">Suspended</span>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge bg-<?= $student['status_class'] ?> text-white">
                            <?= $student['status'] ?>
                        </span>
                    </div>

                    <div class="metrics-grid">
                        <div class="metric-item">
                            <div class="label">Assignments</div>
                            <div class="value"><?= $student['assignments_submitted'] ?></div>
                            <div class="label"><?= number_format($student['avg_assignment_score'], 1) ?>%</div>
                        </div>
                        <div class="metric-item">
                            <div class="label">Quizzes</div>
                            <div class="value"><?= $student['quizzes_taken'] ?></div>
                            <div class="label"><?= number_format($student['avg_quiz_score'], 1) ?>%</div>
                        </div>
                        <div class="metric-item">
                            <div class="label">Overall Avg</div>
                            <div class="value"><?= number_format($student['overall_avg'], 1) ?>%</div>
                        </div>
                    </div>

                    <!-- Progress Bars -->
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Assignment Progress</small>
                            <div class="progress-bar-container">
                                <div class="progress-bar bg-primary" 
                                     style="width: <?= min(100, $student['avg_assignment_score']) ?>%"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Quiz Progress</small>
                            <div class="progress-bar-container">
                                <div class="progress-bar bg-success" 
                                     style="width: <?= min(100, $student['avg_quiz_score']) ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    </div>
</div>

</div>
</body>
</html>