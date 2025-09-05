<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'header.php';
include 'database.php';
?>

<div class="welcome-section">
    <h1>🎉 Welcome to <span class="brand">StudyMate</span>!</h1>
    <p class="tagline">"Learning is your superpower!" 🌟</p>
</div>

<div class="user-card">
    <img src="assets/images/bagpack.png" alt="User Avatar" class="avatar">
    <h2>📚 Choose Your Grade</h2>
    <div class="grade-buttons">
        <?php
        $result = $conn->query("SELECT id, grade_level FROM grades ORDER BY grade_level");
        while ($row = $result->fetch_assoc()):
        ?>
            <a href="subjects.php?grade_id=<?= $row['id'] ?>" class="grade-btn">Grade <?= $row['grade_level'] ?></a>
        <?php endwhile; ?>
    </div>
</div>

<div class="links-section">
    <a href="dashboard.php" class="dashboard-link">📊 Progress Dashboard</a>
    <a href="logout.php" class="logout-link">🚪 Logout</a>
</div>

<div class="challenge-card">
    <h3>🎯 Daily Challenge</h3>
    <p>Answer 5 math questions to earn <strong>50 XP</strong>!</p>
    <a href="challenge.php" class="start-btn">Start Now</a>
</div>

<div class="funfact-card">
    <h3>🌟 Fun Fact</h3>
    <?php
    // Array of fun facts
    $funFacts = [
        "Did you know? A group of flamingos is called a flamboyance!",
        "Octopuses have three hearts ❤️.",
        "Bananas are berries, but strawberries aren’t 🍓.",
        "Sharks existed before trees 🌊.",
        "Your stomach gets a new lining every 3–4 days!",
        "Sloths can hold their breath longer than dolphins 🦥.",
        "Honey never spoils 🍯.",
        "A day on Venus is longer than a year on Venus 🌌."
    ];

    // Pick a random fun fact
    $randomFact = $funFacts[array_rand($funFacts)];

    echo "<p>$randomFact</p>";
    ?>
</div>

<?php include 'footer.php'; ?>
