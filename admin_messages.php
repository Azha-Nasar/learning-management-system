<?php
session_start();
include('dbcon.php');

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header("Location: admin_login.php");
    exit;
}

// Admin uses their user_id directly (admin_id IS the user_id)
$admin_user_id = $admin_id;

// Fetch all teachers with their user_id
$teachers_query = "SELECT t.teacher_id, t.name, t.email 
                   FROM teacher t 
                   ORDER BY t.name ASC";
$teachers = $conn->query($teachers_query);

if (!$teachers) {
    die("Teacher query failed: " . $conn->error);
}

// Fetch all students with their user_id
$students_query = "SELECT s.student_id, s.name, s.email 
                   FROM student s 
                   WHERE s.status='Active' 
                   ORDER BY s.name ASC";
$students = $conn->query($students_query);

if (!$students) {
    die("Student query failed: " . $conn->error);
}

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipient_type = $_POST['recipient_type'];
    $recipient_id = $_POST['recipient_id'];
    $message_content = trim($_POST['message']);

    if ($recipient_id && $message_content) {
        $stmt = $conn->prepare("INSERT INTO message (sender_id, reciever_id, content, date_sended) VALUES (?, ?, ?, NOW())");
        
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("iis", $admin_user_id, $recipient_id, $message_content);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Message sent successfully!";
        } else {
            $_SESSION['error'] = "Failed to send message: " . $stmt->error;
        }
        $stmt->close();
        header("Location: admin_messages.php");
        exit;
    }
}

// Fetch sent messages
$messages_query = "SELECT m.*, 
                   COALESCE(t.name, s.name) as recipient_name,
                   CASE 
                       WHEN t.teacher_id IS NOT NULL THEN 'Teacher'
                       WHEN s.student_id IS NOT NULL THEN 'Student'
                       ELSE 'User'
                   END as recipient_type
                   FROM message m
                   LEFT JOIN teacher t ON m.reciever_id = t.teacher_id
                   LEFT JOIN student s ON m.reciever_id = s.student_id
                   WHERE m.sender_id = ?
                   ORDER BY m.date_sended DESC";

$stmt = $conn->prepare($messages_query);

if (!$stmt) {
    die("Messages query failed: " . $conn->error);
}

$stmt->bind_param("i", $admin_user_id);
$stmt->execute();
$messages = $stmt->get_result();

include('admin_layout.php');
?>

<style>
.message-card {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid #667eea;
    transition: all 0.3s ease;
}

.message-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.recipient-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
}

.recipient-badge {
    padding: 0.25rem 0.65rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.recipient-badge.teacher {
    background: #e3f2fd;
    color: #1976d2;
}

.recipient-badge.student {
    background: #e8f5e9;
    color: #2e7d32;
}

.message-content {
    color: #495057;
    line-height: 1.6;
    margin-bottom: 0.75rem;
}

.message-time {
    font-size: 0.85rem;
    color: #6c757d;
}

.compose-section {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
</style>

<div class="page-header">
    <h4><i class="fas fa-envelope me-2"></i>Messages Management</h4>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="compose-section">
    <h5 class="mb-3">
        <i class="fas fa-paper-plane me-2"></i>Compose Message
    </h5>
    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Recipient Type *</label>
                <select name="recipient_type" id="recipientType" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <option value="teacher">Lecturer</option>
                    <option value="student">Student</option>
                </select>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Select Recipient *</label>
                <select name="recipient_id" id="recipientSelect" class="form-select" required disabled>
                    <option value="">-- Select recipient type first --</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Message *</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Type your message here..." required></textarea>
        </div>
        <button type="submit" name="send_message" class="btn btn-primary">
            <i class="fas fa-paper-plane me-2"></i>Send Message
        </button>
    </form>
</div>

<h5 class="mb-3">Sent Messages</h5>

<?php if ($messages->num_rows > 0): ?>
    <?php while($msg = $messages->fetch_assoc()): ?>
        <div class="message-card">
            <div class="message-header">
                <div>
                    <div class="recipient-name">
                        <?= htmlspecialchars($msg['recipient_name']) ?>
                    </div>
                    <span class="recipient-badge <?= strtolower($msg['recipient_type']) ?>">
                        <?= $msg['recipient_type'] ?>
                    </span>
                </div>
                <div class="message-time">
                    <i class="fas fa-clock me-1"></i>
                    <?= date('M d, Y h:i A', strtotime($msg['date_sended'])) ?>
                </div>
            </div>
            <div class="message-content">
                <?= nl2br(htmlspecialchars($msg['content'])) ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-inbox me-2"></i>No messages sent yet.
    </div>
<?php endif; ?>

<script>
const recipientType = document.getElementById('recipientType');
const recipientSelect = document.getElementById('recipientSelect');

const teachers = <?= json_encode($teachers->fetch_all(MYSQLI_ASSOC)) ?>;
const students = <?= json_encode($students->fetch_all(MYSQLI_ASSOC)) ?>;

recipientType.addEventListener('change', function() {
    recipientSelect.innerHTML = '<option value="">-- Select Recipient --</option>';
    recipientSelect.disabled = false;
    
    if (this.value === 'teacher') {
        teachers.forEach(teacher => {
            const option = document.createElement('option');
            option.value = teacher.teacher_id;
            option.textContent = `${teacher.name} (${teacher.email})`;
            recipientSelect.appendChild(option);
        });
    } else if (this.value === 'student') {
        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.student_id;
            option.textContent = `${student.name} (${student.email})`;
            recipientSelect.appendChild(option);
        });
    }
});
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>