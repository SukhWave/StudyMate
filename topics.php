<?php
session_start();
include 'database.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

if (!$grade_id || !$subject_id) {
    echo "Invalid grade or subject selection.";
    exit;
}

$stmt = $conn->prepare("SELECT id, topic_name FROM topics WHERE grade_id = ? AND subject_id = ?");
$stmt->bind_param("ii", $grade_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Topic</title>
</head>
<body>
<h2>Select a Topic</h2>

<ul>
    <?php while ($row = $result->fetch_assoc()): ?>
        <li>
            <a href="study.php?topic_id=<?= $row['id'] ?>">
                <?= htmlspecialchars($row['topic_name']) ?>
            </a>
        </li>
    <?php endwhile; ?>
</ul>

<a href="subjects.php?grade_id=<?= $grade_id ?>">Back to Subjects</a>
</body>
</html>
