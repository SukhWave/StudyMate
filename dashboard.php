<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
require_once('database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch progress data
$query = "
    SELECT s.subject_name, t.topic_name, 
           p.correct_answers, p.total_attempts,
           (p.correct_answers / NULLIF(p.total_attempts, 0)) * 100 AS score
    FROM progress p
    JOIN topics t ON p.topic_id = t.id
    JOIN subjects s ON t.subject_id = s.id
    WHERE p.user_id = :user_id
    ORDER BY s.subject_name, t.topic_name
";
$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$progress = $stmt->fetchAll();
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
            padding: 10px; border: 1px solid #ddd;
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
    </style>
</head>
<body>
    <?php include("header.php"); ?>
    <h2 style="text-align:center;">My Learning Progress</h2>

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
    <p style="text-align:center;"><a href="logout.php">Logout</a></p>
</body>
</html>

