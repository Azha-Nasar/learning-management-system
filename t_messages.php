<?php
session_start();
include('teacher_layout.php');
include("dbcon.php");

// PHPMailer files
require __DIR__ . '/PHPMailer-master/src/Exception.php';
require __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if teacher is logged in
$teacher_id = $_SESSION['teacher_id'] ?? null;
if (!$teacher_id) {
    echo "<div class='p-4'>You are not logged in.</div>";
    exit;
}

$success_msg = '';
$error_msg = '';

// Handle "Delete All" messages
if (isset($_POST['delete_all'])) {
    $stmt = $conn->prepare("DELETE FROM message WHERE sender_id = ?");
    $stmt->bind_param("i", $teacher_id);
    if ($stmt->execute()) {
        $success_msg = "All your sent messages have been deleted.";
    } else {
        $error_msg = "Failed to delete messages.";
    }
    $stmt->close();
}

// Handle send message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content']) && isset($_POST['student_id'])) {
    $receiver_id = (int)$_POST['student_id'];
    $content = trim($_POST['content']);

    if (empty($content)) {
        $error_msg = "Message content cannot be empty.";
    } else {
        $stmt = $conn->prepare("SELECT name, email FROM student WHERE student_id = ?");
        
        if (!$stmt) {
            $error_msg = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("i", $receiver_id);
            $stmt->execute();
            $student_result = $stmt->get_result();

            if ($student_result->num_rows == 0) {
                $error_msg = "Invalid student selected.";
            } else {
                $student = $student_result->fetch_assoc();
                $receiver_email = $student['email'];
                $receiver_name = $student['name'];

                // Save message
                $stmt2 = $conn->prepare("INSERT INTO message (sender_id, reciever_id, content, date_sended) VALUES (?, ?, ?, NOW())");
                
                if (!$stmt2) {
                    $error_msg = "Database error: " . $conn->error;
                } else {
                    $stmt2->bind_param("iis", $teacher_id, $receiver_id, $content);
                    if ($stmt2->execute()) {
                        $message_id = $stmt2->insert_id;

                        // Log message sent (check if message_sent table has message_id column)
                        $stmt3 = $conn->prepare("INSERT INTO message_sent (message_id) VALUES (?)");
                        if ($stmt3) {
                            $stmt3->bind_param("i", $message_id);
                            $stmt3->execute();
                            $stmt3->close();
                        }

                        // Send email
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'littledevilpeak@gmail.com';
                            $mail->Password   = 'mfbrkurfeufcqlay';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;

                            $mail->setFrom('littledevilpeak@gmail.com', 'LMS System');
                            $mail->addAddress($receiver_email, $receiver_name);
                            $mail->isHTML(true);
                            $mail->Subject = 'New Feedback from Your Lecturer';
                            $mail->Body    = "Dear {$receiver_name},<br><br>You have received a new message from your lecturer:<br><br><b>{$content}</b><br><br>Regards,<br> EduHub LMS";

                            $mail->send();
                            $success_msg = "Message sent successfully and email notification delivered.";
                        } catch (Exception $e) {
                            $success_msg = "Message saved successfully but email failed: {$mail->ErrorInfo}";
                        }
                    } else {
                        $error_msg = "Database error while saving message: " . $stmt2->error;
                    }
                    $stmt2->close();
                }
            }
            $stmt->close();
        }
    }
}

// Fetch students
$students_query = "SELECT student_id, name FROM student ORDER BY name ASC";
$students = $conn->query($students_query);

if (!$students) {
    die("Error fetching students: " . $conn->error);
}

// FIXED: Fetch messages with correct column names (using 'name' and 'reciever_id' - note the typo in your DB)
$messages_query = "
    SELECT m.*, 
           t.name AS sender_name, 
           s.name AS receiver_name
    FROM message m
    LEFT JOIN teacher t ON m.sender_id = t.teacher_id
    LEFT JOIN student s ON m.reciever_id = s.student_id
    WHERE m.sender_id = ? OR m.reciever_id = ?
    ORDER BY m.date_sended DESC
";

$stmt_messages = $conn->prepare($messages_query);
if (!$stmt_messages) {
    die("Error preparing messages query: " . $conn->error);
}

$stmt_messages->bind_param("ii", $teacher_id, $teacher_id);
$stmt_messages->execute();
$messages = $stmt_messages->get_result();
?>

<div class="container mt-4">
    <h4 class="mb-3">📩 Send Feedback to Students</h4>
    <hr>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error_msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Send Message Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Select Student:</label>
                    <select name="student_id" id="student_id" class="form-select" required>
                        <option value="">-- Select Student --</option>
                        <?php while ($s = $students->fetch_assoc()): ?>
                            <option value="<?= $s['student_id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Message:</label>
                    <textarea name="content" id="content" class="form-control" rows="5" placeholder="Write your feedback here..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <!-- Messages List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0">Your Messages</h5>
            <?php if ($messages->num_rows > 0): ?>
                <form method="post" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete all your sent messages?');">
                    <button type="submit" name="delete_all" class="btn btn-danger btn-sm">
                        Delete All Messagers
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($msg = $messages->fetch_assoc()): ?>
                    <div class="message-item mb-3 p-3" style="background: #f8f9fa; border-left: 4px solid <?= ($msg['sender_id'] == $teacher_id) ? '#0d6efd' : '#6c757d' ?>; border-radius: 4px;">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>From:</strong>
                                <span class="<?= ($msg['sender_id'] == $teacher_id) ? 'text-primary fw-bold' : '' ?>">
                                    <?= ($msg['sender_id'] == $teacher_id) ? "You" : htmlspecialchars($msg['sender_name']) ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>To:</strong>
                                <span class="<?= ($msg['reciever_id'] == $teacher_id) ? 'text-primary fw-bold' : '' ?>">
                                    <?= ($msg['reciever_id'] == $teacher_id) ? "You" : htmlspecialchars($msg['receiver_name']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <strong>Message:</strong> <?= nl2br(htmlspecialchars($msg['content'])) ?>
                        </div>
                        <div class="text-muted small text-end">
                            <?= date('Y-m-d H:i:s', strtotime($msg['date_sended'])) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted mb-0">No messages found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$stmt_messages->close();
?>

</div> <!-- close .content from layout -->
</body>
</html>