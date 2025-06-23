<?php
include 'database.php';

$grade_id = intval($_GET['grade_id'] ?? 0);
if (!$grade_id) {
    die("Please select a valid grade.");
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
<form action="topics.php" method="get">
  <input type="hidden" name="grade_id" value="<?= $grade_id ?>">
  <select name="subject_id" required>
    <option value="">--Select Subject--</option>
    <?php while ($row = $result->fetch_assoc()): ?>
      <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['subject_name']) ?></option>
    <?php endwhile; ?>
  </select>
  <button type="submit">Next</button>
</form>
