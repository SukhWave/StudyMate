<?php
// Enable error reporting for development (optional)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP has no password
$dbname = "study_mate";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = intval($_POST['role_id']);

    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role_id)) {
        die("Please fill in all fields.");
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    die("This email is already registered. Please use a different email.");
}
$checkStmt->close();

    // Prepare the SQL statement to insert the new user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("sssi", $name, $email, $hashedPassword, $role_id);
    
    if ($stmt->execute()) {
        // Registration successful, redirect to login
        header("Location: login.php");
        exit();
    } else {
        // Handle error (e.g., duplicate email)
        echo "Error: " . $stmt->error;
    }

    // Close statement
    $stmt->close();
}

// Close DB connection
$conn->close();
?>
