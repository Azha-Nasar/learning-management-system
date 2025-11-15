<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $class_quiz_id = intval($_POST['class_quiz_id']);
    $answers = $_POST['answers'] ?? [];
    
    // Get quiz questions and correct answers
    $quiz_query = "SELECT qq.question_id, qq.correct_option 
                   FROM class_quiz cq
                   JOIN quiz_question qq ON cq.quiz_id = qq.quiz_id
                   WHERE cq.class_quiz_id = ?";
    $stmt = $conn->prepare($quiz_query);
    $stmt->bind_param("i", $class_quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_questions = 0;
    $correct_answers = 0;
    
    while ($row = $result->fetch_assoc()) {
        $total_questions++;
        $question_id = $row['question_id'];
        $correct_option = $row['correct_option'];
        
        if (isset($answers[$question_id]) && $answers[$question_id] === $correct_option) {
            $correct_answers++;
        }
    }
    
    $grade = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100) : 0;
    $quiz_time = date('Y-m-d H:i:s');
    
    // Save quiz result
    $insert_stmt = $conn->prepare("INSERT INTO student_class_quiz (class_quiz_id, student_id, student_quiz_time, grade) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iisi", $class_quiz_id, $student_id, $quiz_time, $grade);
    
    if ($insert_stmt->execute()) {
        $_SESSION['quiz_result'] = "Quiz completed! Your score: $correct_answers/$total_questions ($grade%)";
    } else {
        $_SESSION['quiz_result'] = "Error saving quiz result: " . $conn->error;
    }
    
    header("Location: s_quiz.php");
    exit();
}

// Fetch available quizzes
$available_query = "SELECT 
                      cq.class_quiz_id,
                      cq.quiz_time,
                      q.quiz_title,
                      q.quiz_description,
                      c.class_name,
                      sub.subject_name
                    FROM teacher_class_student tcs
                    JOIN teacher_class tc ON tcs.teacher_class_id = tc.teacher_class_id
                    JOIN class_quiz cq ON tc.teacher_class_id = cq.teacher_class_id
                    JOIN quiz q ON cq.quiz_id = q.quiz_id
                    JOIN class c ON tc.class_id = c.class_id
                    JOIN subject sub ON tc.subject_id = sub.subject_id
                    LEFT JOIN student_class_quiz scq ON cq.class_quiz_id = scq.class_quiz_id AND scq.student_id = ?
                    WHERE tcs.student_id = ? AND scq.student_class_quiz_id IS NULL
                    ORDER BY cq.class_quiz_id DESC";

$stmt = $conn->prepare($available_query);
if (!$stmt) {
    die("Prepare failed (available quizzes): " . $conn->error);
}
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$available_quizzes = $stmt->get_result();

// Fetch completed quizzes - FIXED: Changed 's' alias to 'sub' to match available query
$completed_query = "SELECT 
                      scq.*,
                      q.quiz_title,
                      c.class_name,
                      sub.subject_name
                    FROM student_class_quiz scq
                    JOIN class_quiz cq ON scq.class_quiz_id = cq.class_quiz_id
                    JOIN quiz q ON cq.quiz_id = q.quiz_id
                    JOIN teacher_class tc ON cq.teacher_class_id = tc.teacher_class_id
                    JOIN class c ON tc.class_id = c.class_id
                    JOIN subject sub ON tc.subject_id = sub.subject_id
                    WHERE scq.student_id = ?
                    ORDER BY scq.student_quiz_time DESC";

$stmt = $conn->prepare($completed_query);
if (!$stmt) {
    die("Prepare failed (completed quizzes): " . $conn->error);
}
$stmt->bind_param("i", $student_id);
$stmt->execute();
$completed_quizzes = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Quizzes | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quiz-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #f39c12;
        }

        .quiz-card.completed {
            border-left-color: #28a745;
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .quiz-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .quiz-timer {
            background: #fff3cd;
            color: #856404;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .grade-display {
            font-size: 2rem;
            font-weight: 700;
            color: #28a745;
        }

        .question-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .option-label {
            display: block;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option-label:hover {
            background: #e7f3ff;
            border-color: #3498db;
        }

        .option-label input[type="radio"]:checked ~ span {
            font-weight: 600;
            color: #3498db;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <h4 class="mb-4">
        <i class="fas fa-question-circle me-2"></i>My Quizzes
    </h4>
    <hr>

    <?php if (isset($_SESSION['quiz_result'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['quiz_result'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['quiz_result']); ?>
    <?php endif; ?>

    <!-- Available Quizzes -->
    <h5 class="mt-4 mb-3">Available Quizzes</h5>
    <?php if ($available_quizzes->num_rows > 0): ?>
        <?php while($quiz = $available_quizzes->fetch_assoc()): ?>
            <div class="quiz-card">
                <div class="quiz-header">
                    <div>
                        <div class="quiz-title"><?= htmlspecialchars($quiz['quiz_title']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($quiz['class_name']) ?> - <?= htmlspecialchars($quiz['subject_name']) ?></small>
                    </div>
                    <div class="quiz-timer">
                        <i class="fas fa-clock me-1"></i><?= $quiz['quiz_time'] ?> minutes
                    </div>
                </div>
                <p class="mb-3"><?= htmlspecialchars($quiz['quiz_description']) ?></p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quizModal<?= $quiz['class_quiz_id'] ?>">
                    <i class="fas fa-play me-1"></i>Start Quiz
                </button>
            </div>

            <!-- Quiz Modal -->
            <div class="modal fade" id="quizModal<?= $quiz['class_quiz_id'] ?>" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST">
                            <div class="modal-header">
                                <h5 class="modal-title"><?= htmlspecialchars($quiz['quiz_title']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                <input type="hidden" name="class_quiz_id" value="<?= $quiz['class_quiz_id'] ?>">
                                
                                <?php
                                // Fetch questions for this quiz
                                $questions_query = "SELECT qq.* FROM quiz_question qq
                                                   JOIN class_quiz cq ON qq.quiz_id = cq.quiz_id
                                                   WHERE cq.class_quiz_id = ?";
                                $q_stmt = $conn->prepare($questions_query);
                                if (!$q_stmt) {
                                    die("Prepare failed (questions): " . $conn->error);
                                }
                                $q_stmt->bind_param("i", $quiz['class_quiz_id']);
                                $q_stmt->execute();
                                $questions = $q_stmt->get_result();
                                $q_num = 1;
                                ?>
                                
                                <?php while($question = $questions->fetch_assoc()): ?>
                                    <div class="question-card">
                                        <h6 class="mb-3"><?= $q_num ?>. <?= htmlspecialchars($question['question_text']) ?></h6>
                                        
                                        <label class="option-label">
                                            <input type="radio" name="answers[<?= $question['question_id'] ?>]" value="A" required>
                                            <span class="ms-2">A. <?= htmlspecialchars($question['option_a']) ?></span>
                                        </label>
                                        
                                        <label class="option-label">
                                            <input type="radio" name="answers[<?= $question['question_id'] ?>]" value="B" required>
                                            <span class="ms-2">B. <?= htmlspecialchars($question['option_b']) ?></span>
                                        </label>
                                        
                                        <label class="option-label">
                                            <input type="radio" name="answers[<?= $question['question_id'] ?>]" value="C" required>
                                            <span class="ms-2">C. <?= htmlspecialchars($question['option_c']) ?></span>
                                        </label>
                                        
                                        <label class="option-label">
                                            <input type="radio" name="answers[<?= $question['question_id'] ?>]" value="D" required>
                                            <span class="ms-2">D. <?= htmlspecialchars($question['option_d']) ?></span>
                                        </label>
                                    </div>
                                    <?php $q_num++; ?>
                                <?php endwhile; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="submit_quiz" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>Submit Quiz
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No quizzes available at this time.</div>
    <?php endif; ?>

    <!-- Completed Quizzes -->
    <h5 class="mt-5 mb-3">Completed Quizzes</h5>
    <?php if ($completed_quizzes->num_rows > 0): ?>
        <?php while($completed = $completed_quizzes->fetch_assoc()): ?>
            <div class="quiz-card completed">
                <div class="quiz-header">
                    <div>
                        <div class="quiz-title"><?= htmlspecialchars($completed['quiz_title']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($completed['class_name']) ?> - <?= htmlspecialchars($completed['subject_name']) ?></small>
                    </div>
                    <div class="grade-display"><?= $completed['grade'] ?>%</div>
                </div>
                <small class="text-muted">
                    <i class="fas fa-calendar me-1"></i>Completed: <?= date('M d, Y H:i', strtotime($completed['student_quiz_time'])) ?>
                </small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No completed quizzes yet.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>