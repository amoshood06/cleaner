<!-- filepath: c:\xampp\htdocs\cleaner\login.php -->
<?php
// Start session
session_start();

// Database connection
$conn = new mysqli('sdb-o.hosting.stackcp.net', 'cleaner-313937c7c2', 'akk5hq2h61
', 'cleaner-313937c7c2');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if user exists
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user_id'] = $userId;

            // Check if profile is updated
            $profileStmt = $conn->prepare("SELECT state, location, phone, address FROM users WHERE id = ?");
            $profileStmt->bind_param("i", $userId);
            $profileStmt->execute();
            $profileStmt->bind_result($state, $location, $phone, $address);
            $profileStmt->fetch();

            if (empty($state) || empty($location) || empty($phone) || empty($address)) {
                // Redirect to profile page if profile is incomplete
                header("Location: profile.php");
                exit();
            }

            $profileStmt->close();

            $_SESSION['success'] = "Login successful!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
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
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Toast Notification -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php elseif (isset($error)): ?>
        <div id="toast" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-[#A086A3] text-white py-2 px-4 rounded-lg hover:bg-primary-dark transition">Login</button>
        </form>
        <p class="text-center text-sm text-gray-600 mt-4">
            Don't have an account? <a href="register.php" class="text-primary hover:underline">Register here</a>.
        </p>
    </div>

    <script>
        // Automatically hide the toast notification after 3 seconds
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500); // Remove after fade-out
            }
        }, 3000);
    </script>
</body>
</html>