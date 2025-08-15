<?php
session_start();
include 'database.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

if (!$topic_id) {
    echo "<p style='text-align:center;'>Invalid topic selected.</p>";
    include 'footer.php';
    exit;
}

// Get topic name
$stmt = $conn->prepare("SELECT topic_name FROM topics WHERE id = ? AND subject_id = ?");
$stmt->bind_param("ii", $topic_id, $subject_id);
$stmt->execute();
$stmt->bind_result($topic_name);
$stmt->fetch();
$stmt->close();

if (!$topic_name) {
    echo "<p style='text-align:center;'>Topic not found.</p>";
    include 'footer.php';
    exit;
}

echo "<h2 style='text-align:center;'>Topic: " . htmlspecialchars($topic_name) . "</h2>";

// Try to get passage (if any) for this topic and grade (subject filter included)
$passage_stmt = $conn->prepare("
    SELECT p.id, p.passage_text 
    FROM passages p
    INNER JOIN topics t ON p.topic_id = t.id
    WHERE p.topic_id = ? AND p.grade_level = ? AND t.subject_id = ?
    ORDER BY RAND() LIMIT 1
");
$passage_stmt->bind_param("iii", $topic_id, $grade_id, $subject_id);
$passage_stmt->execute();
$passage_result = $passage_stmt->get_result();

if ($passage_result->num_rows > 0) {
    // Passage exists
    $passage = $passage_result->fetch_assoc();
    $passage_id = $passage['id'];
    $passage_text = $passage['passage_text'];

    echo "<div style='max-width: 700px; margin: 20px auto; background: #f8f8f8; padding: 15px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);'>";
    echo "<p style='font-size: 18px; line-height: 1.5;'>" . nl2br(htmlspecialchars($passage_text)) . "</p>";
    echo "</div>";

    // Get passage questions (with subject filter)
    $question_stmt = $conn->prepare("
        SELECT q.id, q.question_text, q.question_type, q.choices 
        FROM questions q
        INNER JOIN passages p ON q.passage_id = p.id
        INNER JOIN topics t ON p.topic_id = t.id
        WHERE q.passage_id = ? AND t.subject_id = ?
        ORDER BY RAND() 
        LIMIT 10
    ");
    $question_stmt->bind_param("ii", $passage_id, $subject_id);
} else {
    // No passage — get topic questions (with subject filter)
    $question_stmt = $conn->prepare("
        SELECT q.id, q.question_text, q.question_type, q.choices 
        FROM questions q
        INNER JOIN topics t ON q.topic_id = t.id
        WHERE q.topic_id = ? AND t.subject_id = ?
        ORDER BY RAND() 
        LIMIT 10
    ");
    $question_stmt->bind_param("ii", $topic_id, $subject_id);
}

$question_stmt->execute();
$question_result = $question_stmt->get_result();

if ($question_result->num_rows > 0):
?>
<form method="post" action="submit_answers.php" style="max-width: 700px; margin: 20px auto;">
    <?php
    $qIndex = 1;
    while ($row = $question_result->fetch_assoc()):
        $question_id = $row['id'];
        $question_text = $row['question_text'];
        $question_type = $row['question_type'];
        $choices = $row['choices'];
    ?>
    <div style="margin-bottom: 20px; padding: 15px; background-color: #fff; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.1);">
        <p><strong>Q<?= $qIndex ?>:</strong> <?= htmlspecialchars($question_text) ?></p>

        <?php if ($question_type === 'multiple_choice' && $choices): ?>
            <?php
            $options = json_decode($choices, true);
            if ($options):
                foreach ($options as $option): ?>
                    <label style="display: block; margin: 5px 0;">
                        <input type="radio" name="answers[<?= $question_id ?>]" value="<?= htmlspecialchars($option) ?>" required>
                        <?= htmlspecialchars($option) ?>
                    </label>
                <?php endforeach;
            endif;
            ?>
        <?php elseif ($question_type === 'fill_in_blank' || $question_type === 'typing'): ?>
            <input type="text" name="answers[<?= $question_id ?>]" required style="width: 100%; padding: 8px;">
        <?php else: ?>
            <p>Unknown question type.</p>
        <?php endif; ?>
    </div>
    <?php
    $qIndex++;
    endwhile;
    ?>

    <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
    <?php if (isset($passage_id)): ?>
        <input type="hidden" name="passage_id" value="<?= $passage_id ?>">
    <?php endif; ?>
    <div style="text-align:center;">
        <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; border: none; color: white; font-size: 16px; cursor: pointer; border-radius: 4px;">Submit Answers</button>
    </div>
</form>

<?php else: ?>
    <p style="text-align:center;">No questions available for this topic.</p>
<?php endif; ?>

<p style="text-align:center; margin-top: 30px;">
    <a href="topics.php?grade_id=<?= $grade_id ?>&subject_id=<?= $subject_id ?>" style="text-decoration: none; color: #004d00;">&larr; Back to Topics</a>
</p>

<?php include 'footer.php'; ?>
