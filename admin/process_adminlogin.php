<?php
// Start session
session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: adminlogin.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cleaner');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$username = trim($_POST['username']);
$password = $_POST['password'];
$remember = isset($_POST['remember_me']) ? true : false;

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Username and password are required.";
    header("Location: adminlogin.php");
    exit();
}

// First check if the user exists
$stmt = $conn->prepare("SELECT id, password, username FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: adminlogin.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Invalid username or password.";
    header("Location: adminlogin.php");
    exit();
}

// Check if user is an admin
$stmt = $conn->prepare("SELECT id FROM admin_users WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['login_error'] = "You do not have admin privileges.";
    header("Location: adminlogin.php");
    exit();
}

$admin = $result->fetch_assoc();
$stmt->close();

// Set session variables
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['is_admin'] = true;

// Set remember me cookie if requested
if ($remember) {
    $token = bin2hex(random_bytes(32));
    $expires = time() + (86400 * 30); // 30 days
    
    // Store token in database (you would need to create a remember_tokens table)
    // This is a simplified example - in production, you'd want to store the token securely
    setcookie('admin_remember', $token, $expires, '/', '', true, true);
}

// Redirect to admin dashboard
header("Location: dashboard.php");
exit();

$conn->close();
?>