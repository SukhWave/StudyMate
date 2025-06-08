<?php include 'header.php'; ?>

<h2>Register</h2>

<form method="post" action="register_process.php">
    <label>Name:</label><br />
    <input type="text" name="name" required /><br />

    <label>Email:</label><br />
    <input type="email" name="email" required /><br />

    <label>Password:</label><br />
    <input type="password" name="password" required /><br />

    <label>Role:</label><br />
    <select name="role_id" required>
        <option value="1">Student</option>
        <option value="2">Parent</option>
        <option value="3">Admin</option>
    </select><br /><br />

    <button type="submit">Register</button>
</form>

<?php include 'footer.php'; ?>
