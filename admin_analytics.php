<?php
session_start();
include('dbcon.php');

// Check authentication
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// === DATA FETCHING ===

// Class Distribution (Top 5)
$class_distribution = $conn->query("
    SELECT c.class_name, COUNT(s.student_id) AS student_count
    FROM class c
    LEFT JOIN student s ON c.class_id = s.class_id
    GROUP BY c.class_id
    ORDER BY student_count DESC
    LIMIT 5
");

// Monthly Registrations (Last 6 Months)
$monthly_registrations = $conn->query("
    SELECT DATE_FORMAT(enrollment_date, '%Y-%m') AS month, COUNT(*) AS count
    FROM student
    WHERE enrollment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
");

// Assignment Statistics
$assignment_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT a.assignment_id) as total_assignments,
        COUNT(DISTINCT sa.student_assignment_id) as total_submissions,
        COUNT(DISTINCT CASE WHEN sa.grade != '' THEN sa.student_assignment_id END) as graded_submissions
    FROM assignment a
    LEFT JOIN student_assignment sa ON a.assignment_id = sa.assignment_id
");
$assignment_data = $assignment_stats->fetch_assoc();

// Quiz Statistics
$quiz_stats = $conn->query("
    SELECT 
        COUNT(DISTINCT q.quiz_id) as total_quizzes,
        COUNT(DISTINCT cq.class_quiz_id) as assigned_quizzes,
        COUNT(DISTINCT scq.student_class_quiz_id) as quiz_attempts
    FROM quiz q
    LEFT JOIN class_quiz cq ON q.quiz_id = cq.quiz_id
    LEFT JOIN student_class_quiz scq ON cq.class_quiz_id = scq.class_quiz_id
");
$quiz_data = $quiz_stats->fetch_assoc();

include('admin_layout.php');
?>

<!-- Page Content -->
<div class="page-header">
    <h4><i class="fas fa-chart-bar me-2"></i>Analytics Dashboard</h4>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-value"><?= $assignment_data['total_assignments'] ?></div>
        <div class="stat-label">Total Assignments</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">
            <i class="fas fa-file-upload"></i>
        </div>
        <div class="stat-value"><?= $assignment_data['total_submissions'] ?></div>
        <div class="stat-label">Submissions</div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value"><?= $assignment_data['graded_submissions'] ?></div>
        <div class="stat-label">Graded</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-question-circle"></i>
        </div>
        <div class="stat-value"><?= $quiz_data['total_quizzes'] ?></div>
        <div class="stat-label">Total Quizzes</div>
    </div>

    <div class="stat-card pink">
        <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-value"><?= $quiz_data['assigned_quizzes'] ?></div>
        <div class="stat-label">Assigned Quizzes</div>
    </div>

    <div class="stat-card indigo">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-value"><?= $quiz_data['quiz_attempts'] ?></div>
        <div class="stat-label">Quiz Attempts</div>
    </div>
</div>

<!-- Charts and Tables -->
<div class="row g-3 mb-4">
    <!-- Enrollment Chart -->
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <h5 class="card-title">Student Enrollments (Last 6 Months)</h5>
            </div>
            <div class="chart-container">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Class Distribution Chart -->
    <div class="col-lg-6">
        <div class="card-custom">
            <div class="card-header-custom">
                <h5 class="card-title">Class Distribution</h5>
            </div>
            <div class="chart-container">
                <canvas id="classChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS for charts -->
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 1.2rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        text-align: center;
        border-top: 3px solid;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .stat-card.blue { border-color: #667eea; }
    .stat-card.green { border-color: #48bb78; }
    .stat-card.purple { border-color: #9f7aea; }
    .stat-card.orange { border-color: #ed8936; }
    .stat-card.pink { border-color: #ec4899; }
    .stat-card.indigo { border-color: #6366f1; }

    .stat-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .stat-card.blue .stat-icon { color: #667eea; }
    .stat-card.green .stat-icon { color: #48bb78; }
    .stat-card.purple .stat-icon { color: #9f7aea; }
    .stat-card.orange .stat-icon { color: #ed8936; }
    .stat-card.pink .stat-icon { color: #ec4899; }
    .stat-card.indigo .stat-icon { color: #6366f1; }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #718096;
        font-weight: 500;
    }

    .card-custom {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        height: 100%;
    }

    .card-header-custom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .chart-container {
        position: relative;
        height: 320px;
    }
</style>

<!-- Chart.js Logic -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Enrollment Chart
const enrollmentCtx = document.getElementById('enrollmentChart');
new Chart(enrollmentCtx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            if ($monthly_registrations && $monthly_registrations->num_rows > 0) {
                $monthly_registrations->data_seek(0);
                while($row = $monthly_registrations->fetch_assoc()) {
                    echo "'" . date('M Y', strtotime($row['month'] . '-01')) . "',";
                }
            }
            ?>
        ],
        datasets: [{
            label: 'New Students',
            data: [
                <?php 
                if ($monthly_registrations && $monthly_registrations->num_rows > 0) {
                    $monthly_registrations->data_seek(0);
                    while($row = $monthly_registrations->fetch_assoc()) {
                        echo $row['count'] . ",";
                    }
                }
                ?>
            ],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: { 
            y: { 
                beginAtZero: true,
                ticks: { stepSize: 1 }
            } 
        }
    }
});

// Class Distribution Chart
const classCtx = document.getElementById('classChart');
new Chart(classCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php 
            if ($class_distribution && $class_distribution->num_rows > 0) {
                $class_distribution->data_seek(0);
                while($row = $class_distribution->fetch_assoc()) {
                    echo "'" . htmlspecialchars($row['class_name']) . "',";
                }
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                if ($class_distribution && $class_distribution->num_rows > 0) {
                    $class_distribution->data_seek(0);
                    while($row = $class_distribution->fetch_assoc()) {
                        echo $row['student_count'] . ",";
                    }
                }
                ?>
            ],
            backgroundColor: ['#667eea','#10b981','#f59e0b','#ec4899','#6366f1']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { 
                position: 'bottom',
                labels: { padding: 15 }
            }
        }
    }
});
</script>

<!-- Bootstrap JS (if not already in layout) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</div> <!-- Close content-wrapper -->
</body>
</html>