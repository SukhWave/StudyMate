<?php
session_start();
include 'database.php';
include 'header.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>

<main>
    <h2 style="text-align:center;">Login to StudyMate</h2>

    <?php if ($error): ?>
        <p style="color: red; text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" style="max-width: 400px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
        <label style="display: block; margin-bottom: 10px;">
            Email:
            <input type="email" name="email" required style="width: 100%; padding: 8px; margin-top: 4px;">
        </label>

        <label style="display: block; margin-bottom: 20px;">
            Password:
            <input type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 4px;">
        </label>

        <button type="submit" style="width: 100%; padding: 10px; background: #166126; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
            Login
        </button>
    </form>

    <p style="text-align:center; margin-top: 15px;">
        Don't have an account? <a href="register.php">Register here</a>.
    </p>
</main>

<?php include 'footer.php'; ?>
