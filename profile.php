<!-- filepath: c:\xampp\htdocs\cleaner\profile.php -->
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cleaner');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $state = trim($_POST['state']);
    $location = trim($_POST['location']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $userId = $_SESSION['user_id'];

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($_FILES['profile_image']['name']);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $imagePath);
    }

    // Update user profile
    if ($imagePath) {
        $stmt = $conn->prepare("UPDATE users SET state = ?, location = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $state, $location, $phone, $address, $imagePath, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET state = ?, location = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $state, $location, $phone, $address, $userId);
    }

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch user profile data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT state, location, phone, address, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($state, $location, $phone, $address, $profileImage);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-primary text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-lg font-bold">Cleaner</a>
            <button id="menu-toggle" class="block md:hidden focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
            <div id="menu" class="hidden md:flex space-x-4">
                <a href="dashboard.php" class="hover:underline">Dashboard</a>
                <a href="profile.php" class="hover:underline">Profile</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Profile Form -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Profile</h2>
            <?php if (isset($success)): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                    <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($state); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($location); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                </div>
                <div></div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <textarea name="address" id="address" rows="3" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($address); ?></textarea>
                </div>
                <div></div>
                    <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                    <input type="file" name="profile_image" id="profile_image" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                    <?php if ($profileImage): ?>
                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="mt-4 w-24 h-24 rounded-full object-cover">
                    <?php endif; ?>
                </div>
                <button type="submit" class="w-full bg-[#A086A3] text-white py-2 px-4 rounded-lg hover:bg-primary-dark transition">Save</button>
            </form>
        </div>
    </div>

    <script>
        // Toggle menu visibility on mobile
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const menu = document.getElementById('menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>