<?php
session_start();
include('dbcon.php');

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    die("Please login first.");
}

$teacher_id = $_SESSION['teacher_id'];
$quiz_id = $_GET['quiz_id'] ?? null;
if (!$quiz_id) {
    die("Error: quiz_id missing.");
}

// --- HANDLE FORM SUBMISSIONS BEFORE ANY OUTPUT ---
// Add Question
if (isset($_POST['submit_question'])) {
    $question_text = $_POST['question_text'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("INSERT INTO quiz_question (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Question added successfully.";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: add_quiz_questions.php?quiz_id=" . $quiz_id);
    exit;
}

// Delete Question
if (isset($_POST['delete_question'])) {
    $del_question_id = $_POST['question_id'];
    $stmt_del_q = $conn->prepare("DELETE FROM quiz_question WHERE question_id = ? AND quiz_id = ?");
    $stmt_del_q->bind_param("ii", $del_question_id, $quiz_id);
    $stmt_del_q->execute();
    $stmt_del_q->close();

    $_SESSION['success'] = "Question deleted successfully.";
    header("Location: add_quiz_questions.php?quiz_id=" . $quiz_id);
    exit;
}

// --- INCLUDE LAYOUT AFTER FORM HANDLING ---
include('teacher_layout.php');

// Fetch recently added questions
$stmt = $conn->prepare("SELECT question_id, question_text, correct_option FROM quiz_question WHERE quiz_id = ? ORDER BY question_id DESC LIMIT 10");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Questions to Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>❓ Add Questions to Quiz (ID: <?= htmlspecialchars($quiz_id) ?>)</h4>
        <div>
            <a href="t_quiz.php" class="btn btn-secondary me-2">← Back</a>
        </div>
    </div>
    <hr>

    <!-- Flash messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="flash-msg">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="flash-msg">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Add Question Form -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Question</label>
                            <textarea name="question_text" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-2"><input type="text" name="option_a" class="form-control" placeholder="Option A" required></div>
                        <div class="mb-2"><input type="text" name="option_b" class="form-control" placeholder="Option B" required></div>
                        <div class="mb-2"><input type="text" name="option_c" class="form-control" placeholder="Option C" required></div>
                        <div class="mb-2"><input type="text" name="option_d" class="form-control" placeholder="Option D" required></div>
                        <div class="mb-2">
                            <label class="form-label">Correct Option</label>
                            <select name="correct_option" class="form-select" required>
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                            </select>
                        </div>
                        <button name="submit_question" class="btn btn-success w-100">➕ Add Question</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recently Added Questions -->
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">📋 Recently Added Questions</h5>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Question</th>
                                <th>Correct</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><?= htmlspecialchars($row['question_text']) ?></td>
                                <td><?= htmlspecialchars($row['correct_option']) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');" style="display:inline;">
                                        <input type="hidden" name="question_id" value="<?= $row['question_id'] ?>">
                                        <button type="submit" name="delete_question" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php $i++; endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto fade flash messages after 3 seconds
setTimeout(() => {
    const alert = document.getElementById('flash-msg');
    if (alert) {
        let bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }
}, 3000);
</script>
</body>
</html>
