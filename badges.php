<?php
require_once('database.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// List of all possible badges (add descriptions if needed)
$all_badges = [
    "Beginner",
    "Star Performer",
    "Consistent",
    "Quick Thinker",
    "Master Learner"
];

// Fetch earned badges
$stmt = $conn->prepare("SELECT badge_name FROM user_badges WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$earned_badges = array_column($result->fetch_all(MYSQLI_ASSOC), 'badge_name');
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Badges</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .badge-gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 40px;
        }

        .badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.2s ease;
            text-align: center;
        }

        .badge img {
            width: 100px;
            height: 100px;
            border-radius: 12px;
        }

        .badge.earned:hover {
            transform: scale(1.1);
            cursor: pointer;
        }

        .badge.locked img {
            filter: grayscale(100%) blur(1px);
            opacity: 0.4;
        }

        .badge-name {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    <h2 style="text-align:center;">🎖️ Badge Gallery</h2>

    <div class="badge-gallery">
        <?php foreach ($all_badges as $badge): ?>
            <?php
                $is_earned = in_array($badge, $earned_badges);
                $class = $is_earned ? 'earned' : 'locked';
                $filename = strtolower(str_replace(' ', '_', $badge));
            ?>
            <div class="badge <?= $class ?>" title="<?= htmlspecialchars($badge) ?>">
                <img src="badges/<?= $filename ?>.png" alt="<?= htmlspecialchars($badge) ?>">
                <div class="badge-name"><?= htmlspecialchars($badge) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <p style="text-align:center;"><a href="dashboard.php">⬅ Back to Dashboard</a></p>
</body>
</html>
