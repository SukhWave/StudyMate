<?php
session_start();
include 'database.php';
include 'header.php';

$grade_id = intval($_GET['grade_id'] ?? 0);
if (!$grade_id) {
    echo "<p>Please select a valid grade.</p>";
    include 'footer.php';
    exit;
}

$sql = "SELECT DISTINCT s.id, s.subject_name
        FROM subjects s
        JOIN topics t ON s.id = t.subject_id
        WHERE t.grade_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $grade_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Select Subject</h2>

<form action="topics.php" method="get" style="max-width: 400px; margin: 20px auto;">
    <input type="hidden" name="grade_id" value="<?= $grade_id ?>">
    <label for="subject_id" style="font-weight: bold;">Choose a subject:</label>
    <select name="subject_id" id="subject_id" required style="width: 100%; padding: 10px; margin-top: 10px;">
        <option value="">--Select Subject--</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['subject_name']) ?></option>
        <?php endwhile; ?>
    </select>
    <br><br>
    <button type="submit" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">
        Next
    </button>
</form>

<a href="index.php" style="display: block; text-align: center;">&larr; Back to Home</a>

<?php include 'footer.php'; ?>
