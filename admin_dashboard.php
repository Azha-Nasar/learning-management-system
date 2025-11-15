<?php
session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// Fetch statistics
$stats = [];

// Total Students
$result = $conn->query("SELECT COUNT(*) as count FROM student WHERE status='Active'");
$stats['students'] = $result->fetch_assoc()['count'];

// Total Teachers
$result = $conn->query("SELECT COUNT(*) as count FROM teacher WHERE status='Active'");
$stats['teachers'] = $result->fetch_assoc()['count'];

// Total Classes
$result = $conn->query("SELECT COUNT(*) as count FROM class");
$stats['classes'] = $result->fetch_assoc()['count'];

// Total Subjects
$result = $conn->query("SELECT COUNT(*) as count FROM subject");
$stats['subjects'] = $result->fetch_assoc()['count'];

// Active Assignments
$result = $conn->query("SELECT COUNT(*) as count FROM assignment WHERE fdatein >= CURDATE()");
$stats['assignments'] = $result->fetch_assoc()['count'];

// Total Quizzes
$result = $conn->query("SELECT COUNT(*) as count FROM quiz");
$stats['quizzes'] = $result->fetch_assoc()['count'];

// Recent Students
$recent_students = $conn->query("SELECT * FROM student ORDER BY created_at DESC LIMIT 5");

// Recent Activity
$recent_activity = $conn->query("SELECT * FROM activity_log ORDER BY activity_log_id DESC LIMIT 5");

// Class Distribution
$class_distribution = $conn->query("
    SELECT c.class_name, COUNT(s.student_id) as student_count 
    FROM class c 
    LEFT JOIN student s ON c.class_id = s.class_id 
    GROUP BY c.class_id 
    ORDER BY student_count DESC 
    LIMIT 5
");

include('admin_layout.php');
?>

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

.stat-number {
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

.content-section {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f7fafc;
}

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.btn-view {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-view:hover {
    background: #5568d3;
    color: white;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    padding: 0.6rem;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.8rem;
    text-transform: uppercase;
    background: #f7fafc;
}

table td {
    padding: 0.6rem;
    color: #4a5568;
    font-size: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}

table tbody tr:hover {
    background: #f7fafc;
}

.badge {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #d4f4dd;
    color: #22863a;
}

.activity-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    background: #eef2ff;
    color: #667eea;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #2d3748;
    font-size: 0.85rem;
    margin-bottom: 0.15rem;
}

.activity-time {
    font-size: 0.75rem;
    color: #94a3b8;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}
</style>

<div class="page-header">
    <h4><i class="fas fa-chart-line me-2"></i>Dashboard Overview</h4>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-number"><?= $stats['students'] ?></div>
        <div class="stat-label">Students</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-number"><?= $stats['teachers'] ?></div>
        <div class="stat-label">Teachers</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-door-open"></i>
        </div>
        <div class="stat-number"><?= $stats['classes'] ?></div>
        <div class="stat-label">Classes</div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-number"><?= $stats['subjects'] ?></div>
        <div class="stat-label">Subjects</div>
    </div>

    <div class="stat-card pink">
        <div class="stat-icon">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-number"><?= $stats['assignments'] ?></div>
        <div class="stat-label">Assignments</div>
    </div>

    <div class="stat-card indigo">
        <div class="stat-icon">
            <i class="fas fa-question-circle"></i>
        </div>
        <div class="stat-number"><?= $stats['quizzes'] ?></div>
        <div class="stat-label">Quizzes</div>
    </div>
</div>

<!-- Recent Students and Activity -->
<div class="content-grid">
    <div class="content-section">
        <div class="section-header">
            <h5 class="section-title">Recent Students</h5>
            <a href="admin_students.php" class="btn-view">View All</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($student = $recent_students->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                    <td><?= htmlspecialchars($student['email']) ?></td>
                    <td>
                        <span class="badge">
                            <?= $student['status'] ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h5 class="section-title">Recent Activity</h5>
            <a href="admin_logs.php" class="btn-view">View All</a>
        </div>
        <div>
            <?php while($activity = $recent_activity->fetch_assoc()): ?>
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">
                        <?= htmlspecialchars($activity['action']) ?>
                    </div>
                    <div class="activity-time">
                        <?= date('M d, Y h:i A', strtotime($activity['date'])) ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="content-section">
        <div class="section-header">
            <h5 class="section-title">Class Distribution</h5>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Students</th>
                </tr>
            </thead>
            <tbody>
                <?php while($class = $class_distribution->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($class['class_name']) ?></strong></td>
                    <td><span class="badge"><?= $class['student_count'] ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>