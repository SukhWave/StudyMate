<?php
session_start();
include 'header.php';
include 'database.php';

?>

<h1>Welcome to StudyMate</h1>
<p>Your free interactive learning app for Grades 1–8.</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <p>Welcome back! Select your grade to start studying:</p>

    <?php
    // Fetch grades from DB
    $result = $conn->query("SELECT id, grade_level FROM grades ORDER BY grade_level");
    if ($result->num_rows > 0):
    ?>
    <form action="subjects.php" method="get">
        <label for="grade">Select Grade:</label>
        <select name="grade_id" id="grade" required>
            <option value="">--Choose Grade--</option>
            <?php while ($row = $result->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>">Grade <?= $row['grade_level'] ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Next</button>
    </form>
    <?php else: ?>
        <p>No grades found in database.</p>
    <?php endif; ?>

    <p><a href="dashboard.php">📊 Progress Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>

<?php else: ?>
    <p>Please <a href="register.php">Register</a> or <a href="login.php">Login</a> to get started.</p>
<?php endif; ?>

<?php include 'footer.php'; ?>

