<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'header.php';
include 'database.php';

$user_id = $_SESSION['user_id'];
$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;

// Fetch 5 random math questions for daily challenge
// Example: topic_id 2 = Multiplication
$stmt = $conn->prepare("SELECT id, question_text, question_type, correct_answer, choices FROM questions WHERE topic_id = 2 ORDER BY RAND() LIMIT 5");
$stmt->execute();
$questions_result = $stmt->get_result();
?>

<div class="challenge-card">
    <h2>🎯 Daily Challenge</h2>
    <?php if ($questions_result->num_rows > 0): ?>
        <form method="post" action="challenge_submit.php">
            <?php $i = 1; while ($row = $questions_result->fetch_assoc()): ?>
                <div class="question-box">
                    <p><strong>Q<?= $i ?>:</strong> <?= htmlspecialchars($row['question_text']) ?></p>
                    <?php if ($row['question_type'] === 'multiple_choice' && $row['choices']): 
                        $options = json_decode($row['choices'], true);
                        foreach ($options as $option): ?>
                            <label>
                                <input type="radio" name="answers[<?= $row['id'] ?>]" value="<?= htmlspecialchars($option) ?>" required>
                                <?= htmlspecialchars($option) ?>
                            </label><br>
                        <?php endforeach;
                    else: ?>
                        <input type="text" name="answers[<?= $row['id'] ?>]" required>
                    <?php endif; ?>
                </div>
            <?php $i++; endwhile; ?>

            <input type="hidden" name="topic_id" value="2">
            <button type="submit" class="start-btn">Submit Answers</button>
        </form>
    <?php else: ?>
        <p>No daily challenge questions available today.</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
