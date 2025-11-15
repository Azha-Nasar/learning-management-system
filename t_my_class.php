<?php include('teacher_layout.php'); ?>
<?php
include("dbcon.php");

$teacher_id = $_SESSION['teacher_id'] ?? null;

if (!$teacher_id) {
    echo "You are not logged in.";
    exit;
}

$query = "SELECT tc.teacher_class_id, c.class_name, s.subject_name, tc.school_year, tc.thumbnails
          FROM teacher_class tc
          JOIN class c ON tc.class_id = c.class_id
          JOIN subject s ON tc.subject_id = s.subject_id
          WHERE tc.teacher_id = $teacher_id";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Class</title>
</head>

<style>
    .top-bar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }

    .add-class-btn {
        padding: 8px 14px;
        background: #3498db;
        color: white;
        text-decoration: none;
        font-size: 14px;
        border-radius: 4px;
    }

    .add-class-btn:hover {
        background: #2980b9;
    }

    .class-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .class-card {
        background: #fff;
        width: 260px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: 0.3s;
    }

    .class-card img {
        width: 100%;
        height: 130px;
        object-fit: cover;
    }

    .card-body {
        padding: 15px;
    }

    .card-body h4 {
        margin: 0 0 10px;
        font-size: 16px;
        color: #333;
    }

    .card-body p {
        margin: 5px 0;
        font-size: 14px;
        color: #555;
    }

    .card-body a.remove-btn {
        display: inline-block;
        margin-top: 10px;
        font-size: 13px;
        color: #e74c3c;
        text-decoration: none;
    }

    .card-body a.remove-btn:hover {
        text-decoration: underline;
    }
</style>
</head>

<body>

    <div class="main-content">
        <div class="main-content">
            <h4>📚 My Class</h4>
            <hr>

            <div class="top-bar">
                <a href="add_teacher_class.php" class="add-class-btn">+ Add New Class</a>
            </div>

            <div class="class-cards">
                <?php if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <div class="class-card">
                            <img src="<?= htmlspecialchars($row['thumbnails']); ?>" alt="Thumbnail">
                            <div class="card-body">
                                <h4><?= htmlspecialchars($row['class_name']); ?></h4>
                                <p><strong>Subject:</strong> <?= htmlspecialchars($row['subject_name']); ?></p>
                                <p><strong>School Year:</strong> <?= htmlspecialchars($row['school_year']); ?></p>
                                <a class="remove-btn" href="remove_class.php?id=<?= $row['teacher_class_id']; ?>">Remove</a>
                            </div>
                        </div>
                    <?php }
                } else { ?>
                    <p>No classes assigned yet.</p>
                <?php } ?>
            </div>
        </div>

</body>

</html>