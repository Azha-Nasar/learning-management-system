<?php
include('dbcon.php'); 
include('teacher_layout.php');
$sql = "
  SELECT a.id, a.title, a.message, a.poster, a.created_at, a.role,
         u.firstname, u.lastname, u.username
  FROM announcements a
  JOIN users u ON a.posted_by = u.user_id
  ORDER BY a.created_at DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
    <style>
        .card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
        }
        .title { font-weight: bold; font-size: 18px; }
        .date { color: gray; font-size: 14px; margin-bottom: 8px; }
        .poster-img { max-width: 120px; max-height: 100px; display:block; margin-top:8px; }
    </style>
</head>
<body>
    <h2>📢 Latest Announcements</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>No announcements yet.</p>
    <?php else: ?>
        <?php while($row = $result->fetch_assoc()) { ?>
            <div class="card">
                <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                <div class="date">
                    Posted by <?= htmlspecialchars($row['firstname'] . " " . $row['lastname']) ?>
                    (<?= htmlspecialchars($row['username']) ?>, <?= htmlspecialchars($row['role']) ?>)
                    on <?= htmlspecialchars($row['created_at']) ?>
                </div>
                <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                <?php if ($row['poster']): ?>
                    <img src="<?= htmlspecialchars($row['poster']) ?>" class="poster-img">
                <?php endif; ?>
            </div>
        <?php } ?>
    <?php endif; ?>
</body>
</html>
