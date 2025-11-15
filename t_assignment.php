<?php
include('teacher_layout.php');
include('dbcon.php');

// Get the logged-in teacher ID
$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    header("Location: login.php");
    exit;
}

// Handle assignment upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_assignment'])) {
    $class_id = $_POST['class_id'];
    $fdesc = $_POST['fdesc'];
    $fname = $_FILES['file']['name'];
    $floc = 'uploads/' . basename($fname);
    $fdatein = date("Y-m-d");

    if (move_uploaded_file($_FILES['file']['tmp_name'], $floc)) {
        $stmt = $conn->prepare("INSERT INTO assignment (floc, fdatein, fdesc, teacher_id, class_id, fname) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $floc, $fdatein, $fdesc, $teacher_id, $class_id, $fname);
        $stmt->execute();
    }
}

// Handle assignment delete
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM assignment WHERE assignment_id = $delete_id");
}

// Fetch classes for the dropdown
$class_stmt = $conn->prepare("
    SELECT c.class_id, c.class_name 
    FROM teacher_class tc 
    JOIN class c ON tc.class_id = c.class_id 
    WHERE tc.teacher_id = ?
");
$class_stmt->bind_param("i", $teacher_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();

// Fetch uploaded assignments
$assignment_stmt = $conn->prepare("
    SELECT a.*, c.class_name 
    FROM assignment a 
    JOIN class c ON a.class_id = c.class_id 
    WHERE a.teacher_id = ? 
    ORDER BY a.fdatein DESC
");
$assignment_stmt->bind_param("i", $teacher_id);
$assignment_stmt->execute();
$assignment_result = $assignment_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            font-size: 14px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
        }
        .btn-custom {
            background-color: #007BFF;
            color: white;
            font-size: 14px;
            padding: 8px 14px;
        }
        h4 {
            margin-top: 20px;
        }
        /* Optional: fix table vertical scroll if table gets long */
        .table-wrapper {
            max-height: 75vh;
            overflow-y: auto;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-0">
    <h4>📘 Assignment Uploade </h4> 
    <hr>

    <div class="row mt-2">
        <!-- Table on Left -->
        <div class="col-md-9 table-wrapper" style="font-size:14px;">
            <table class="table table-bordered table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Class</th>
                        <th>Description</th>
                        <th>File Name</th>
                        <th>Uploaded Date</th>
                        <th>Download</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($assignment_result->num_rows > 0): ?>
                        <?php $i = 1; while ($row = $assignment_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($row['fdesc']) ?></td>
                                <td><?= htmlspecialchars($row['fname']) ?></td>
                                <td><?= htmlspecialchars($row['fdatein']) ?></td>
                                <td><a href="<?= $row['floc'] ?>" class="btn btn-sm btn-success" download>Download</a></td>
                                <td><a href="?delete_id=<?= $row['assignment_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this assignment?')">Delete</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No assignments uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Form on Right -->
        <div class="col-md-3">
            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Select Class:</label>
                        <select name="class_id" class="form-select form-select-sm" required>
                            <option value="">-- Choose Class --</option>
                            <?php while ($row = $class_result->fetch_assoc()): ?>
                                <option value="<?= $row['class_id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description:</label>
                        <input type="text" name="fdesc" class="form-control form-control-sm" placeholder="Enter description" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Choose File:</label>
                        <input type="file" name="file" class="form-control form-control-sm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="upload_assignment" class="btn btn-custom">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
