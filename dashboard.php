<?php
require_once('database.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// List of all possible badges in order (update as needed)
$all_badges = [
    "Beginner",
    "Star Performer",
    "Consistent",
    "Quick Thinker",
    "Master Learner"
];

// Fetch progress
$query = "
    SELECT s.subject_name, t.topic_name, 
           p.correct_answers, p.total_attempts,
           (p.correct_answers / NULLIF(p.total_attempts, 0)) * 100 AS score
    FROM progress p
    JOIN topics t ON p.topic_id = t.id
    JOIN subjects s ON t.subject_id = s.id
    WHERE p.user_id = ?
    ORDER BY s.subject_name, t.topic_name
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$progress = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's earned badges with award dates
$badge_stmt = $conn->prepare("SELECT badge_name, awarded_at FROM user_badges WHERE user_id = ?");
$badge_stmt->bind_param("i", $user_id);
$badge_stmt->execute();
$badge_result = $badge_stmt->get_result();
$earned_badges_data = $badge_result->fetch_all(MYSQLI_ASSOC);

// Map earned badges by badge_name for lookup
$earned_badges = [];
foreach ($earned_badges_data as $bd) {
    $earned_badges[$bd['badge_name']] = $bd['awarded_at'];
}

// Calculate progress percentage
$total_badges = count($all_badges);
$earned_count = count($earned_badges);
$progress_percent = $total_badges > 0 ? round(($earned_count / $total_badges) * 100) : 0;

// Find all indexes of earned badges
$earned_indexes = [];
foreach ($all_badges as $index => $badge_name) {
    if (isset($earned_badges[$badge_name])) {
        $earned_indexes[] = $index;
    }
}

$max_earned_index = -1;
if (!empty($earned_indexes)) {
    $max_earned_index = max($earned_indexes);
}


foreach ($all_badges as $index => $badge_name) {
    if (isset($earned_badges[$badge_name])) {
        $last_earned_index = $index;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Progress</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        .score-bar {
            background-color: #ddd;
            height: 16px;
            position: relative;
        }
        .score-bar span {
            display: block;
            height: 100%;
            background-color: #4CAF50;
        }

        /* Badge section styles */
        .badges-section {
            width: 80%;
            margin: 40px auto;
            text-align: center;
        }

        /* Progress bar container */
        .badge-progress-container {
            width: 100%;
            background: #ddd;
            border-radius: 10px;
            height: 20px;
            margin-bottom: 25px;
            overflow: hidden;
        }

        /* Progress bar fill */
        .badge-progress-fill {
            height: 100%;
            width: <?= $progress_percent ?>%;
            background: #4CAF50;
            border-radius: 10px 0 0 10px;
            transition: width 0.5s ease-in-out;
        }

        .badge-progress-text {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 16px;
        }

        .badge {
            display: inline-block;
            margin: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 8px;
            padding: 10px;
            position: relative;
            width: 110px;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .badge:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .badge img {
            width: 80px;
            height: 80px;
        }

        .badge-name {
            font-size: 14px;
            margin-top: 5px;
            font-weight: bold;
        }

        .badge-date {
            font-size: 12px;
            color: gray;
        }

        /* Locked badges */
        .locked {
            filter: grayscale(100%);
            opacity: 0.4;
            cursor: default;
            box-shadow: none !important;
        }

        /* Next badge unlocked but not earned yet */
        .next-unlocked {
            filter: none;
            opacity: 1;
            box-shadow: 0 0 10px #ffa500; /* orange glow */
        }
    </style>
</head>

<?php if (isset($_SESSION['new_badge'])): ?>
    <style>
        body {
            overflow: hidden;
        }
        #badge-popup-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }
        #badge-popup {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 3px solid #4CAF50;
            padding: 20px;
            z-index: 9999;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
    </style>
    <div id="badge-popup-overlay"></div>
    <div id="badge-popup">
        <h3>🎉 You’ve earned a new badge!</h3>
        <?php $image_name = strtolower(str_replace(' ', '_', $_SESSION['new_badge'])); ?>
        <img src="badges/<?= $image_name ?>.png" alt="<?= htmlspecialchars($_SESSION['new_badge']) ?>" style="width:100px; height:100px;">
        <p><strong><?= htmlspecialchars($_SESSION['new_badge']) ?></strong></p>
        <button onclick="document.getElementById('badge-popup').style.display='none'; document.getElementById('badge-popup-overlay').style.display='none'; document.body.style.overflow = 'auto';">Close</button>
    </div>
    <?php unset($_SESSION['new_badge']); ?>
<?php endif; ?>

<body>
    <?php include("header.php"); ?>
    <h2 style="text-align:center;">My Learning Progress</h2>

    <!-- PROGRESS TABLE -->
    <table>
        <tr>
            <th>Subject</th>
            <th>Topic</th>
            <th>Correct</th>
            <th>Attempts</th>
            <th>Score</th>
        </tr>
        <?php foreach ($progress as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['subject_name']) ?></td>
            <td><?= htmlspecialchars($row['topic_name']) ?></td>
            <td><?= $row['correct_answers'] ?></td>
            <td><?= $row['total_attempts'] ?></td>
            <td>
                <?php 
                    $score = $row['score'] ?? 0;
                    echo round($score, 1) . '%';
                ?>
                <div class="score-bar">
                    <span style="width:<?= min(100, $score) ?>%"></span>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- BADGE PROGRESS BAR -->
    <div class="badges-section">
        <div class="badge-progress-text">
            Badges Earned: <?= $earned_count ?> / <?= $total_badges ?> (<?= $progress_percent ?>%)
        </div>
        <div class="badge-progress-container" aria-label="Badge completion progress">
            <div class="badge-progress-fill"></div>
        </div>
    </div>

    <!-- BADGES DISPLAY -->
    <div class="badges-section">
        <h3>🎖️ Badge Gallery</h3>
        <?php foreach ($all_badges as $index => $badge): 
            $earned = isset($earned_badges[$badge]);
            $awarded_at = $earned ? $earned_badges[$badge] : null;

            $image_name = strtolower(str_replace(' ', '_', $badge));
            $image_name = preg_replace('/[^a-z0-9_]/', '', $image_name);

            if ($earned) {
                $badge_class = '';
            } elseif ($index === $max_earned_index + 1) {
                $badge_class = 'next-unlocked';
            } else {
                $badge_class = 'locked';
            }
        ?>
            <div class="badge <?= $badge_class ?>" title="<?= htmlspecialchars($badge) ?>">
                <img src="badges/<?= $image_name ?>.png" alt="<?= htmlspecialchars($badge) ?>">
                <div class="badge-name"><?= htmlspecialchars($badge) ?></div>
                <?php if ($earned): ?>
                    <div class="badge-date">Earned: <?= date('M j, Y', strtotime($awarded_at)) ?></div>
                <?php elseif ($badge_class === 'next-unlocked'): ?>
                    <div class="badge-date">Unlocked</div>
                <?php else: ?>
                    <div class="badge-date">Locked</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    </div>

    <p style="text-align:center;">
        <a href="badges.php">View All Badges</a> | 
        <a href="logout.php">Logout</a>
    </p>
</body>
</html>
