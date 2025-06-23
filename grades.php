<?php
include 'database.php';

$sql = "SELECT id, grade_level FROM grades ORDER BY grade_level ASC";
$result = $conn->query($sql);
?>

<h2>Select Your Grade</h2>
<form action="subjects.php" method="get">
  <select name="grade_id" required>
    <option value="">--Select Grade--</option>
    <?php while ($row = $result->fetch_assoc()): ?>
      <option value="<?= $row['id'] ?>">Grade <?= $row['grade_level'] ?></option>
    <?php endwhile; ?>
  </select>
  <button type="submit">Next</button>
</form>
