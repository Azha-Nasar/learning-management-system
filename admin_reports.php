<?php
session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// Get statistics with error handling
$result = $conn->query("SELECT COUNT(*) as count FROM student WHERE status='Active'");
$total_students = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM teacher WHERE status='Active'");
$total_teachers = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM class");
$total_classes = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM subject");
$total_subjects = $result ? $result->fetch_assoc()['count'] : 0;

// Get class enrollment data
$class_enrollment = $conn->query("
    SELECT c.class_name, COUNT(DISTINCT s.student_id) as student_count
    FROM class c
    LEFT JOIN student s ON c.class_id = s.class_id
    GROUP BY c.class_id, c.class_name
    ORDER BY student_count DESC
    LIMIT 5
");

// Get teacher workload
$teacher_workload = $conn->query("
    SELECT t.name, COUNT(DISTINCT tc.teacher_class_id) as subject_count
    FROM teacher t
    LEFT JOIN teacher_class tc ON t.teacher_id = tc.teacher_id
    WHERE t.status = 'Active'
    GROUP BY t.teacher_id, t.name
    ORDER BY subject_count DESC
    LIMIT 5
");

// Get subject distribution
$subject_distribution = $conn->query("
    SELECT s.subject_code, s.subject_name as subject_title, COUNT(DISTINCT tc.class_id) as class_count
    FROM subject s
    LEFT JOIN teacher_class tc ON s.subject_id = tc.subject_id
    GROUP BY s.subject_id, s.subject_code, s.subject_name
    ORDER BY class_count DESC
    LIMIT 5
");

include('admin_layout.php');
?>

<style>
@media print {
    .no-print { display: none !important; }
    .page-header, .action-buttons { display: none !important; }
    body { background: white !important; }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border-left: 3px solid;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.stat-card.blue { border-color: #667eea; }
.stat-card.green { border-color: #48bb78; }
.stat-card.orange { border-color: #ed8936; }
.stat-card.purple { border-color: #9f7aea; }

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
}

.stat-card.blue .stat-icon { background: #eef2ff; color: #667eea; }
.stat-card.green .stat-icon { background: #f0fff4; color: #48bb78; }
.stat-card.orange .stat-icon { background: #fffaf0; color: #ed8936; }
.stat-card.purple .stat-icon { background: #faf5ff; color: #9f7aea; }

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.85rem;
    color: #718096;
    font-weight: 500;
}

.report-section {
    background: white;
    border-radius: 10px;
    padding: 1.2rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.report-section h5 {
    color: #2d3748;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table thead {
    background: #f7fafc;
}

table th {
    padding: 0.6rem;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.8rem;
    text-transform: uppercase;
    border-bottom: 2px solid #e2e8f0;
}

table td {
    padding: 0.6rem;
    color: #4a5568;
    font-size: 0.85rem;
    border-bottom: 1px solid #e2e8f0;
}

table tbody tr:hover {
    background: #f7fafc;
}

.badge {
    padding: 0.25rem 0.6rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #eef2ff;
    color: #667eea;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.btn-export {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: white;
    color: #4a5568;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-export:hover {
    background: #f7fafc;
    border-color: #cbd5e0;
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}
</style>

<div class="page-header no-print">
    <h4><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-value"><?= $total_students ?></div>
        <div class="stat-label">Active Students</div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-value"><?= $total_teachers ?></div>
        <div class="stat-label">Total Teachers</div>
    </div>

    <div class="stat-card orange">
        <div class="stat-icon">
            <i class="fas fa-school"></i>
        </div>
        <div class="stat-value"><?= $total_classes ?></div>
        <div class="stat-label">Total Classes</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-value"><?= $total_subjects ?></div>
        <div class="stat-label">Total Subjects</div>
    </div>
</div>

<!-- Export Buttons -->
<div class="action-buttons no-print">
    <button class="btn-export" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>
    <button class="btn-export" onclick="generatePDF()">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</div>

<div class="report-grid">
    <!-- Class Enrollment Report -->
    <div class="report-section">
        <h5>
            <i class="fas fa-users text-primary"></i>
            Top Classes by Enrollment
        </h5>
        <table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Students</th>
                </tr>
            </thead>
            <tbody>
                <?php while($class = $class_enrollment->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($class['class_name']) ?></strong></td>
                        <td><span class="badge"><?= $class['student_count'] ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Teacher Workload Report -->
    <div class="report-section">
        <h5>
            <i class="fas fa-chalkboard-teacher text-success"></i>
            Teacher Workload
        </h5>
        <table>
            <thead>
                <tr>
                    <th>Teacher</th>
                    <th>Classes</th>
                </tr>
            </thead>
            <tbody>
                <?php while($teacher = $teacher_workload->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($teacher['name']) ?></strong></td>
                        <td><span class="badge"><?= $teacher['subject_count'] ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Subject Distribution Report -->
    <div class="report-section">
        <h5>
            <i class="fas fa-book-open text-warning"></i>
            Popular Subjects
        </h5>
        <table>
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Classes</th>
                </tr>
            </thead>
            <tbody>
                <?php while($subject = $subject_distribution->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($subject['subject_code']) ?></strong></td>
                        <td><span class="badge"><?= $subject['class_count'] ?></span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
async function generatePDF() {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'mm', 'a4');
    
    // Title
    pdf.setFontSize(20);
    pdf.setTextColor(102, 126, 234);
    pdf.text('LMS System Report', 105, 20, { align: 'center' });
    
    pdf.setFontSize(10);
    pdf.setTextColor(100);
    pdf.text('Generated: ' + new Date().toLocaleDateString(), 105, 27, { align: 'center' });
    
    let yPos = 40;
    
    // Statistics
    pdf.setFontSize(14);
    pdf.setTextColor(0);
    pdf.text('System Overview', 15, yPos);
    yPos += 8;
    
    pdf.setFontSize(10);
    pdf.text('Active Students: <?= $total_students ?>', 20, yPos);
    yPos += 6;
    pdf.text('Total Teachers: <?= $total_teachers ?>', 20, yPos);
    yPos += 6;
    pdf.text('Total Classes: <?= $total_classes ?>', 20, yPos);
    yPos += 6;
    pdf.text('Total Subjects: <?= $total_subjects ?>', 20, yPos);
    yPos += 12;
    
    // Class Enrollment
    pdf.setFontSize(14);
    pdf.text('Top Classes by Enrollment', 15, yPos);
    yPos += 8;
    
    pdf.setFontSize(10);
    <?php 
    $class_enrollment->data_seek(0);
    while($class = $class_enrollment->fetch_assoc()): 
    ?>
    pdf.text('<?= addslashes($class['class_name']) ?>: <?= $class['student_count'] ?> students', 20, yPos);
    yPos += 6;
    <?php endwhile; ?>
    
    yPos += 6;
    
    // Teacher Workload
    pdf.setFontSize(14);
    pdf.text('Teacher Workload', 15, yPos);
    yPos += 8;
    
    pdf.setFontSize(10);
    <?php 
    $teacher_workload->data_seek(0);
    while($teacher = $teacher_workload->fetch_assoc()): 
    ?>
    pdf.text('<?= addslashes($teacher['name']) ?>: <?= $teacher['subject_count'] ?> classes', 20, yPos);
    yPos += 6;
    <?php endwhile; ?>
    
    yPos += 6;
    
    // Subject Distribution
    pdf.setFontSize(14);
    pdf.text('Popular Subjects', 15, yPos);
    yPos += 8;
    
    pdf.setFontSize(10);
    <?php 
    $subject_distribution->data_seek(0);
    while($subject = $subject_distribution->fetch_assoc()): 
    ?>
    pdf.text('<?= addslashes($subject['subject_code']) ?>: <?= $subject['class_count'] ?> classes', 20, yPos);
    yPos += 6;
    <?php endwhile; ?>
    
    // Save PDF
    pdf.save('LMS_Report_' + new Date().toISOString().split('T')[0] + '.pdf');
}
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>