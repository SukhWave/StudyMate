<?php
session_start();
include 'database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

if (!$grade_id || !$subject_id) {
    echo "<p style='text-align:center;'>Invalid grade or subject selection.</p>";
    include 'footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT id, topic_name FROM topics WHERE grade_id = ? AND subject_id = ?");
$stmt->bind_param("ii", $grade_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2 style="text-align:center;">Select a Topic</h2>

<?php if ($result->num_rows > 0): ?>
    <ul style="max-width: 500px; margin: 20px auto; padding-left: 0;">
        <?php while ($row = $result->fetch_assoc()): ?>
            <li style="list-style: none; margin-bottom: 10px;">
                <a href="study.php?topic_id=<?= $row['id'] ?>&grade_id=<?= $grade_id ?>&subject_id=<?= $subject_id ?>"
                   style="display: block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; text-align: center;">
                    <?= htmlspecialchars($row['topic_name']) ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p style="text-align:center;">No topics available for this grade and subject.</p>
<?php endif; ?>

<p style="text-align:center;">
    <a href="subjects.php?grade_id=<?= $grade_id ?>" style="text-decoration: none; color: #004d00;">&larr; Back to Subjects</a>
</p>

<?php include 'footer.php'; ?>
