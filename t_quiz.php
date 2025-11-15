<?php
include('teacher_layout.php');
include('dbcon.php'); // your DB connection

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">'
        . htmlspecialchars($_SESSION['success']) .
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show m-3" role="alert">'
        . htmlspecialchars($_SESSION['error']) .
        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['error']);
}

$teacher_id = $_SESSION['teacher_id'] ?? 1; // use session or default 1 for testing

// Fetch all quizzes for this teacher
$quizQuery = mysqli_query($conn, "SELECT * FROM quiz WHERE teacher_id = $teacher_id ORDER BY date_added DESC");

// Fetch all classes taught by this teacher (join to get class_name)
$classQuery = mysqli_query($conn, "
    SELECT tc.teacher_class_id, c.class_name 
    FROM teacher_class tc 
    JOIN class c ON tc.class_id = c.class_id 
    WHERE tc.teacher_id = $teacher_id
");

// Fetch classes into an array so we can reuse in multiple modals
$classes = [];
while ($class = mysqli_fetch_assoc($classQuery)) {
    $classes[] = $class;
}
?>

<div class="container mt-1">
    <h4 class="mb-2">📝 Quiz Management
        <hr>
    </h4>

    <!-- Create Quiz Form -->
    <div class="card mb-4">
        <div class="card-header">Create New Quiz</div>
        <div class="card-body">
            <form action="create_quiz.php" method="POST">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" name="quiz_title" class="form-control" placeholder="Quiz Title" required>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="quiz_description" class="form-control" placeholder="Quiz Description">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quiz List -->
    <div class="card">
        <div class="card-header">Your Quizzes</div>
        <div class="card-body" style="font-size:14px;">
            <table class="table table-bordered ">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($quizQuery) === 0): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No quizzes created yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while ($quiz = mysqli_fetch_assoc($quizQuery)): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['quiz_title']) ?></td>
                            <td><?= htmlspecialchars($quiz['quiz_description']) ?></td>
                            <td><?= htmlspecialchars($quiz['date_added']) ?></td>
                            <td style="min-width: 130px;">
                                <a href="add_quiz_questions.php?quiz_id=<?= $quiz['quiz_id'] ?>"
                                    class="btn btn-outline-primary btn-sm w-100 mb-1"
                                    style="min-width: 120px;">
                                    Add Questions
                                </a>
                                <button type="button"
                                    class="btn btn-outline-warning btn-sm w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#assignModal<?= $quiz['quiz_id'] ?>">
                                    Assign
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Modals -->
<?php
// Re-fetch quizzes for modals (or use earlier fetched data)
$quizQuery2 = mysqli_query($conn, "SELECT * FROM quiz WHERE teacher_id = $teacher_id ORDER BY date_added DESC");
while ($quiz = mysqli_fetch_assoc($quizQuery2)):
?>
    <div class="modal fade" id="assignModal<?= $quiz['quiz_id'] ?>" tabindex="-1" aria-labelledby="assignModalLabel<?= $quiz['quiz_id'] ?>" aria-hidden="true">
        <div class="modal-dialog">
            <form action="assign_quiz.php" method="POST">
                <input type="hidden" name="quiz_id" value="<?= $quiz['quiz_id'] ?>">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalLabel<?= $quiz['quiz_id'] ?>">Assign Quiz to Class</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="teacher_class_id_<?= $quiz['quiz_id'] ?>" class="form-label">Select Class</label>
                            <select name="teacher_class_id" id="teacher_class_id_<?= $quiz['quiz_id'] ?>" class="form-select" required>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= $class['teacher_class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quiz_time_<?= $quiz['quiz_id'] ?>" class="form-label">Quiz Duration (minutes)</label>
                            <input type="number" name="quiz_time" id="quiz_time_<?= $quiz['quiz_id'] ?>" class="form-control" min="1" required placeholder="e.g., 30">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-warning">Assign Quiz</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endwhile; ?>

<!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>