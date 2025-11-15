<?php
ob_start();
session_start();
include('dbcon.php');
include('teacher_layout.php');


$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Fetch teacher's classes
$classes = [];
$stmt = $conn->prepare("
    SELECT tc.teacher_class_id, c.class_name 
    FROM teacher_class tc
    JOIN class c ON tc.class_id = c.class_id
    WHERE tc.teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();

// Fetch all subjects
$subjects = [];
$res2 = $conn->query("SELECT subject_name FROM subject");
while ($row2 = $res2->fetch_assoc()) {
    $subjects[] = $row2;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $class_name = $_POST['class_name'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $start_datetime = $_POST['start_datetime'] ?? '';
    $end_datetime = $_POST['end_datetime'] ?? '';
    $description = $_POST['description'] ?? '';

    if ($class_name && $subject && $start_datetime && $end_datetime) {
        $stmt = $conn->prepare("
            INSERT INTO lecturer_timetable 
            (teacher_id, class_name, subject, start_datetime, end_datetime, description, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("isssss", $teacher_id, $class_name, $subject, $start_datetime, $end_datetime, $description);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Lecture timetable entry added successfully.";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill all required fields.";
    }

    // Redirect to prevent duplicate submission
    header("Location: t_timetable.php");
    exit;
}

// Fetch existing timetable entries
$stmt2 = $conn->prepare("SELECT * FROM lecturer_timetable WHERE teacher_id = ? ORDER BY start_datetime DESC");
$stmt2->bind_param("i", $teacher_id);
$stmt2->execute();
$result = $stmt2->get_result();
?>

<div class="container mt-1">
    <h4 class="mb-3">📅 Lecturer Timetable <hr></h4>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="flash-msg">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="flash-msg">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Timetable Table -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-3">
                    <table class="table table-striped table-bordered table-sm mb-0" style="font-size:14px;">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Class Name</th>
                                <th>Subject</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows === 0): ?>
                            <tr><td colspan="6" class="text-center">No timetable entries found.</td></tr>
                        <?php else: $i=1; while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td><?= date("Y-m-d H:i", strtotime($row['start_datetime'])) ?></td>
                                <td><?= date("Y-m-d H:i", strtotime($row['end_datetime'])) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                            </tr>
                        <?php $i++; endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Timetable Form -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Class Name *</label>
                            <select name="class_name" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php foreach($classes as $class): ?>
                                    <option value="<?= htmlspecialchars($class['class_name']) ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Subject *</label>
                            <select name="subject" class="form-select" required>
                                <option value="">-- Select Subject --</option>
                                <?php foreach($subjects as $sub): ?>
                                    <option value="<?= htmlspecialchars($sub['subject_name']) ?>"><?= htmlspecialchars($sub['subject_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Start Date & Time *</label>
                            <input type="datetime-local" name="start_datetime" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>End Date & Time *</label>
                            <input type="datetime-local" name="end_datetime" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary w-100">Add</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto-dismiss alerts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
setTimeout(() => {
    const alert = document.getElementById('flash-msg');
    if(alert){
        new bootstrap.Alert(alert).close();
    }
}, 3000);
</script>
