<?php
session_start();
include('dbcon.php');

$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Fetch teacher full name
$query = "SELECT name FROM teacher WHERE teacher_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$fullName = $row['name'];

// ---------- Get classes assigned to teacher ----------
$classStmt = $conn->prepare("
    SELECT c.class_id, c.class_name
    FROM class c
    INNER JOIN teacher_class tc ON c.class_id = tc.class_id
    WHERE tc.teacher_id = ?
");
$classStmt->bind_param("i", $teacher_id);
$classStmt->execute();
$classResult = $classStmt->get_result();

// ---------- Add student ----------
if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $class_id = (int)$_POST['class_id'];
    $status = $_POST['status'];
    $enrollment_date = date('Y-m-d');

    // Insert into users table
    $parts = explode(' ', $name, 2);
    $firstname = $parts[0];
    $lastname = $parts[1] ?? '';

    $stmt_user = $conn->prepare("
        INSERT INTO users (firstname, lastname, email, password, user_type, created_at)
        VALUES (?, ?, ?, ?, 'student', NOW())
    ");
    if (!$stmt_user) die("User prepare failed: " . $conn->error);
    $stmt_user->bind_param("ssss", $firstname, $lastname, $email, $password);

    if ($stmt_user->execute()) {
        $user_id = $stmt_user->insert_id;

        // Insert into student table
        $stmt_student = $conn->prepare("
            INSERT INTO student (user_id, name, email, class_id, status, enrollment_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt_student) die("Student prepare failed: " . $conn->error);
        $stmt_student->bind_param("ississ", $user_id, $name, $email, $class_id, $status, $enrollment_date);

        if (!$stmt_student->execute()) {
            die("Student insert error: " . $conn->error);
        }
    } else {
        die("User insert error: " . $conn->error);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ---------- Fetch students in teacher's classes ----------
$studentsStmt = $conn->prepare("
    SELECT s.*, c.class_name
    FROM student s
    INNER JOIN class c ON s.class_id = c.class_id
    INNER JOIN teacher_class tc ON c.class_id = tc.class_id
    WHERE tc.teacher_id = ?
    ORDER BY s.student_id DESC
");
$studentsStmt->bind_param("i", $teacher_id);
$studentsStmt->execute();
$students = $studentsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        .container-fluid { margin-left: 240px; margin-top: 20px; }
        .card { border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .table th, .table td { font-size: 14px; }
        .modal-content { border-radius: 12px; }
        .status-active { color: green; font-weight: 600; }
        .status-suspended { color: red; font-weight: 600; }
    </style>
</head>
<body>
<?php include 'teacher_layout.php'; ?>

<div class="container-fluid mt-0">
    <h4 class="fw-semibold">👨‍🎓 Manage Students</h4>
    <hr>
</div>

<div class="container-fluid">
    <div class="row g-2">
        <!-- Student Table -->
        <div class="col-md-8">
            <div class="card p-2 mb-4">
                <h5 class="mb-3">Student List</h5>
                <table class="table table-striped align-middle">
                    <thead class="table-secondary">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td>
                                <?php if (strtolower($row['status']) === 'active'): ?>
                                    <span class="status-active">Active</span>
                                <?php else: ?>
                                    <span class="status-suspended">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['student_id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal<?= $row['student_id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="update_student.php">
                                        <div class="modal-body">
                                            <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>" />
                                            <div class="mb-2">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required />
                                            </div>
                                            <div class="mb-2">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required />
                                            </div>
                                            <div class="mb-2">
                                                <label>Class</label>
                                                <select name="class_id" class="form-control" required>
                                                    <?php mysqli_data_seek($classResult, 0); while ($class = $classResult->fetch_assoc()): ?>
                                                        <option value="<?= $class['class_id'] ?>" <?= $row['class_id'] == $class['class_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($class['class_name']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="Active" <?= $row['status']=='Active'?'selected':'' ?>>Active</option>
                                                    <option value="Suspended" <?= $row['status']=='Suspended'?'selected':'' ?>>Suspended</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" name="update_student" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Student Form -->
        <div class="col-md-4">
            <div class="card p-3">
                <h5 class="mb-3">Add Student</h5>
                <form method="POST">
                    <div class="mb-2">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required />
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required />
                    </div>
                    <div class="mb-2">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required />
                    </div>
                    <div class="mb-2">
                        <label>Class</label>
                        <select name="class_id" class="form-control" required>
                            <option value="">-- Select Class --</option>
                            <?php mysqli_data_seek($classResult, 0); while ($class = $classResult->fetch_assoc()): ?>
                                <option value="<?= $class['class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                    <button type="submit" name="add_student" class="btn btn-success w-100">Add Student</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
