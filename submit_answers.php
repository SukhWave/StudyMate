<?php
session_start();
include 'database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
$answers = isset($_POST['answers']) && is_array($_POST['answers']) ? $_POST['answers'] : [];

if (!$topic_id || empty($answers)) {
    echo "<p style='text-align:center;'>Invalid submission.</p>";
    include 'footer.php';
    exit;
}

$question_ids = array_keys($answers);
$placeholders = implode(',', array_fill(0, count($question_ids), '?'));

$sql = "SELECT id, correct_answer FROM questions WHERE id IN ($placeholders)";
$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($question_ids));
$stmt->bind_param($types, ...$question_ids);
$stmt->execute();
$result = $stmt->get_result();

$correct_answers = [];
while ($row = $result->fetch_assoc()) {
    $correct_answers[$row['id']] = $row['correct_answer'];
}
$stmt->close();

$total_attempts = count($answers);
$correct_count = 0;

foreach ($answers as $qid => $user_answer) {
    $qid = intval($qid);
    $correct_answer = $correct_answers[$qid] ?? null;
    if ($correct_answer !== null) {
        if (strcasecmp(trim($user_answer), trim($correct_answer)) === 0) {
            $correct_count++;
        }
    }
}

$stmt = $conn->prepare("SELECT id, correct_answers, total_attempts FROM progress WHERE user_id = ? AND topic_id = ?");
$stmt->bind_param("ii", $user_id, $topic_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($progress_id, $existing_correct, $existing_attempts);
    $stmt->fetch();
    $stmt->close();

    $new_correct = $existing_correct + $correct_count;
    $new_attempts = $existing_attempts + $total_attempts;

    $update_stmt = $conn->prepare("UPDATE progress SET correct_answers = ?, total_attempts = ?, last_updated = CURRENT_TIMESTAMP WHERE id = ?");
    $update_stmt->bind_param("iii", $new_correct, $new_attempts, $progress_id);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    $stmt->close();

    $insert_stmt = $conn->prepare("INSERT INTO progress (user_id, topic_id, correct_answers, total_attempts) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("iiii", $user_id, $topic_id, $correct_count, $total_attempts);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$score_percentage = round(($correct_count / $total_attempts) * 100, 2);
?>

<div style="max-width: 700px; margin: 0 auto; text-align: center; padding: 30px; background-color: #fff; border-radius: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
    <h2>Your Results</h2>
    <p>You answered <strong><?= $correct_count ?></strong> out of <strong><?= $total_attempts ?></strong> questions correctly.</p>
    <p>Your score: <strong><?= $score_percentage ?>%</strong></p>

    <div style="margin-top: 30px;">
        <a href="study.php?topic_id=<?= $topic_id ?>" style="padding: 10px 20px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Try Again</a>
        <a href="dashboard.php" style="padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Back to Dashboard</a>
    </div>
</div>

<?php include 'footer.php'; ?>
