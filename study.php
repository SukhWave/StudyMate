<?php
session_start();
include 'database.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if (!$topic_id) {
    echo "Invalid topic.";
    exit;
}

$stmt = $conn->prepare("SELECT topic_name FROM topics WHERE id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$stmt->bind_result($topic_name);
$stmt->fetch();
$stmt->close();

if (!$topic_name) {
    echo "Topic not found.";
    exit;
}

$stmt = $conn->prepare("SELECT id, question_text, question_type, choices FROM questions WHERE topic_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Study: <?= htmlspecialchars($topic_name) ?></title>
</head>
<body>
<h2>Topic: <?= htmlspecialchars($topic_name) ?></h2>

<form method="post" action="submit_answers.php">
    <?php
    $qIndex = 1;
    while ($row = $result->fetch_assoc()):
        $question_id = $row['id'];
        $question_text = $row['question_text'];
        $question_type = $row['question_type'];
        $choices = $row['choices'];
    ?>
        <div style="margin-bottom:20px;">
            <p><strong>Q<?= $qIndex ?>:</strong> <?= htmlspecialchars($question_text) ?></p>
            
            <?php if ($question_type === 'multiple_choice' && $choices): ?>
                <?php
                $options = json_decode($choices, true);
                if ($options):
                    foreach ($options as $option): ?>
                        <label>
                            <input type="radio" name="answers[<?= $question_id ?>]" value="<?= htmlspecialchars($option) ?>" required>
                            <?= htmlspecialchars($option) ?>
                        </label><br>
                    <?php endforeach;
                endif;
                ?>

            <?php elseif ($question_type === 'fill_in_blank'): ?>
                <input type="text" name="answers[<?= $question_id ?>]" required>

            <?php elseif ($question_type === 'typing'): ?>
                <input type="text" name="answers[<?= $question_id ?>]" required>

            <?php else: ?>
                <p>Unknown question type.</p>
            <?php endif; ?>
        </div>
    <?php
        $qIndex++;
    endwhile;
    ?>

    <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
    <button type="submit">Submit Answers</button>
</form>

<a href="topics.php?grade_id=<?= $_GET['grade_id'] ?? '' ?>&subject_id=<?= $_GET['subject_id'] ?? '' ?>">Back to Topics</a>
</body>
</html>
