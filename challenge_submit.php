<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'header.php';
include 'database.php';

$user_id = $_SESSION['user_id'];

// XP earned this challenge
$earnedXP = 50;

// ✅ Update user XP in database
$conn->query("UPDATE users SET xp = xp + $earnedXP WHERE id = $user_id");

// ✅ Fetch updated total XP
$result = $conn->query("SELECT xp FROM users WHERE id = $user_id");
$row = $result->fetch_assoc();
$totalXP = $row['xp'];

// Next milestone (example: 1000 XP)
$milestone = 1000;
$progress = min(100, ($totalXP / $milestone) * 100);
?>

<div class="celebration-card">
    <h1>🎉 Hurray! 🎉</h1>
    <p class="earned">You just earned <strong><?= $earnedXP ?> XP</strong>!</p>
    <p class="total">🌟 Your total XP is now: <strong><?= $totalXP ?> XP</strong></p>

    <!-- Progress bar -->
    <div class="progress-container">
        <div class="progress-bar" style="width: <?= $progress ?>%"></div>
    </div>
    <p class="milestone">🏆 Next Badge at <?= $milestone ?> XP</p>

    <div class="celebration-buttons">
        <a href="challenge.php" class="start-btn">🔄 Try Another Challenge</a>
        <a href="dashboard.php" class="dashboard-btn">📊 Go to Dashboard</a>
    </div>
</div>

<?php include 'footer.php'; ?>
