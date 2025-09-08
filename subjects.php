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

<h2 style="text-align:center; font-size: 2em; margin-top: 30px;">Select Subject</h2>

<form action="topics.php" method="get" style="max-width: 500px; margin: 30px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
    <input type="hidden" name="grade_id" value="<?= $grade_id ?>">
    
    <label for="subject_id" style="font-weight: bold; font-size: 1.2em;">Choose a subject:</label>
    <select name="subject_id" id="subject_id" required 
            style="width: 100%; padding: 12px 15px; margin-top: 12px; font-size: 1em; border-radius: 4px; border: 1px solid #ccc;">
        <option value="">--Select Subject--</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['subject_name']) ?></option>
        <?php endwhile; ?>
    </select>
    
    <br><br>
    <button type="submit" 
            style="width: 100%; padding: 12px 0; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 1.1em; cursor: pointer;">
        Next
    </button>
</form>

<a href="index.php" style="display: block; text-align: center; font-size: 1em; margin-top: 20px; color: #004d00;">&larr; Back to Home</a>



<?php include 'footer.php'; ?>
