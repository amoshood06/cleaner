<!-- filepath: c:\xampp\htdocs\cleaner\register.php -->
<?php
// Start session
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cleaner');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email is already registered.";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful. You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Register</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-[#A086A3] text-white py-2 px-4 rounded-lg hover:bg-primary-dark transition">Register</button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-4">
            Already have an account? <a href="login.php" class="text-primary hover:underline">Login here</a>.
        </p>
    </div>
</body>
</html>