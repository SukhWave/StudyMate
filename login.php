<?php include 'header.php'; ?>

<h2>Login</h2>

<form method="post" action="login_process.php">
    <label>Email:</label><br />
    <input type="email" name="email" required /><br />

    <label>Password:</label><br />
    <input type="password" name="password" required /><br />

    <button type="submit">Login</button>
</form>

<?php include 'footer.php'; ?>
