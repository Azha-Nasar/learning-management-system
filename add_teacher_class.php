<?php
session_start();
include("dbcon.php");

// Fetch dropdown values
$classResult = mysqli_query($conn, "SELECT * FROM class");
$subjectResult = mysqli_query($conn, "SELECT * FROM subject");

// On form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_SESSION['teacher_id'];
    $class_id = $_POST['class_id'];
    $subject_id = $_POST['subject_id'];
    $school_year = $_POST['school_year'];

    // Handle image upload
    $thumbnail = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["thumbnail"]["name"]);
        $targetFilePath = $targetDir . time() . "_" . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allowed file types
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $targetFilePath)) {
                $thumbnail = $targetFilePath;
            } else {
                echo "Error uploading the image.";
                exit;
            }
        } else {
            echo "Only JPG, JPEG, PNG, & GIF files are allowed.";
            exit;
        }
    } else {
        echo "Please upload a thumbnail image.";
        exit;
    }

    // Insert into DB
    $query = "INSERT INTO teacher_class (teacher_id, class_id, subject_id, school_year, thumbnails)
              VALUES ('$teacher_id', '$class_id', '$subject_id', '$school_year', '".mysqli_real_escape_string($conn, $thumbnail)."')";

    if (mysqli_query($conn, $query)) {
        header("Location: t_my_class.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher Class</title>
    <style>
        .form-container {
            padding: 40px;
            background: #fff;
            width: 500px;
            margin: 30px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            margin-top: 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<?php include('teacher_layout.php'); ?>

<div class="form-container">
    <h3>Assign Class to Teacher</h3>
    <form method="post" enctype="multipart/form-data">
        <label>Select Class</label>
        <select name="class_id" required>
            <option value="">Select Class</option>
            <?php while ($class = mysqli_fetch_assoc($classResult)) { ?>
                <option value="<?= $class['class_id']; ?>"><?= htmlspecialchars($class['class_name']); ?></option>
            <?php } ?>
        </select>

        <label>Select Subject</label>
        <select name="subject_id" required>
            <option value="">Select Subject</option>
            <?php while ($subject = mysqli_fetch_assoc($subjectResult)) { ?>
                <option value="<?= $subject['subject_id']; ?>"><?= htmlspecialchars($subject['subject_name']); ?></option>
            <?php } ?>
        </select>

        <label>School Year</label>
        <input type="text" name="school_year" value="2024-2025" required>

        <label>Upload Thumbnail</label>
        <input type="file" name="thumbnail" accept="image/*" required>

        <button type="submit">Assign Class</button>
    </form>
</div>

</body>
</html>
