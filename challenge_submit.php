<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'header.php';
include 'database.php';

$user_id = $_SESSION['user_id'];

// Check if answers were submitted
if (!isset($_POST['answers']) || empty($_POST['answers'])) {
    echo "<p style='text-align:center;'>No answers submitted. <a href='challenge.php'>Try Again</a></p>";
    include 'footer.php';
    exit();
}

$answers = $_POST['answers'];
$score = 0;

// Calculate correct answers
foreach ($answers as $question_id => $user_answer) {
    $stmt = $conn->prepare("SELECT correct_answer FROM questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->bind_result($correct_answer);
    $stmt->fetch();
    $stmt->close();

    if (trim(strtolower($user_answer)) === trim(strtolower($correct_answer))) {
        $score++;
    }
}

// Award XP (example: 50 XP for completing challenge)
$xp_awarded = 50;

// Insert XP into user_xp table
$stmt = $conn->prepare("INSERT INTO user_xp (user_id, xp, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("ii", $user_id, $xp_awarded);
$stmt->execute();
$stmt->close();
?>

<div class="challenge-result" style="max-width:700px; margin:50px auto; padding:20px; background:#f8f8f8; border-radius:10px; text-align:center; box-shadow:0 0 10px rgba(0,0,0,0.2);">
    <h2>🎉 Hurray!</h2>
    <p>You answered <strong><?php echo $score; ?> / <?php echo count($answers); ?></strong> questions correctly.</p>
    <p>💎 You earned <strong><?php echo $xp_awarded; ?> XP</strong>!</p>
    <a href="index.php" class="start-btn" style="display:inline-block; margin-top:20px; padding:10px 20px; background-color:#4CAF50; color:white; text-decoration:none; border-radius:5px;">Back to Home</a>
</div>

<?php include 'footer.php'; ?>
