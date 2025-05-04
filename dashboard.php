<!-- filepath: c:\xampp\htdocs\cleaner\dashboard.php -->
<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli('sdb-o.hosting.stackcp.net', 'cleaner-313937c7c2', 'akk5hq2h61
', 'cleaner-313937c7c2');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user profile data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($profileImage);
$stmt->fetch();
$stmt->close();

// Fetch user bookings
$stmt = $conn->prepare("SELECT id, cleaning_type, rooms, parlors, toilets, kitchens, dining, others, description, created_at FROM bookings WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$totalBookings = count($bookings);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #A086A3;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-[var(--primary-color)] text-white w-64 p-6 hidden md:block">
            <div class="flex items-center space-x-4 mb-6">
                <?php if ($profileImage): ?>
                    <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="w-12 h-12 rounded-full object-cover">
                <?php else: ?>
                    <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                        <span class="text-sm">N/A</span>
                    </div>
                <?php endif; ?>
                <h2 class="text-lg font-bold">Welcome, User</h2>
            </div>
            <nav class="space-y-4">
                <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Dashboard</a>
                <a href="profile.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Profile</a>
                <a href="logout.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Logout</a>
            </nav>
        </aside>

        <!-- Mobile Navbar -->
        <nav class="bg-[var(--primary-color)] text-white p-4 md:hidden">
            <div class="flex justify-between items-center">
                <h1 class="text-lg font-bold">Cleaner</h1>
                <button id="menu-toggle" class="focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
            <div id="mobile-menu" class="hidden mt-4 space-y-4">
                <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Dashboard</a>
                <a href="profile.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Profile</a>
                <a href="logout.php" class="block py-2 px-4 rounded hover:bg-white hover:text-[var(--primary-color)]">Logout</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

            <!-- Total Bookings -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-bold">Total Bookings</h2>
                <p class="text-4xl font-bold text-[var(--primary-color)]"><?php echo $totalBookings; ?></p>
            </div>

            <!-- Booking History -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Booking History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 border">#</th>
                                <th class="px-4 py-2 border">Cleaning Type</th>
                                <th class="px-4 py-2 border">Rooms</th>
                                <th class="px-4 py-2 border">Parlors</th>
                                <th class="px-4 py-2 border">Toilets</th>
                                <th class="px-4 py-2 border">Kitchens</th>
                                <th class="px-4 py-2 border">Dining</th>
                                <th class="px-4 py-2 border">Others</th>
                                <th class="px-4 py-2 border">Description</th>
                                <th class="px-4 py-2 border">Date</th>
                                <th class="px-4 py-2 border">Chat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($bookings): ?>
                                <?php foreach ($bookings as $index => $booking): ?>
                                    <tr class="text-center">
                                        <td class="px-4 py-2 border"><?php echo $index + 1; ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($booking['cleaning_type']); ?></td>
                                        <td class="px-4 py-2 border"><?php echo $booking['rooms']; ?></td>
                                        <td class="px-4 py-2 border"><?php echo $booking['parlors']; ?></td>
                                        <td class="px-4 py-2 border"><?php echo $booking['toilets']; ?></td>
                                        <td class="px-4 py-2 border"><?php echo $booking['kitchens']; ?></td>
                                        <td class="px-4 py-2 border"><?php echo $booking['dining']; ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($booking['others']); ?></td>
                                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($booking['description']); ?></td>
                                        <td class="px-4 py-2 border"><?php echo date('Y-m-d', strtotime($booking['created_at'])); ?></td>
                                        <td class="px-4 py-2 border">
                                            <a href="https://wa.me/1234567890?text=<?php echo urlencode("Booking ID: " . $booking['id'] . "\nCleaning Type: " . $booking['cleaning_type'] . "\nRooms: " . $booking['rooms'] . "\nParlors: " . $booking['parlors'] . "\nToilets: " . $booking['toilets'] . "\nKitchens: " . $booking['kitchens'] . "\nDining: " . $booking['dining'] . "\nOthers: " . $booking['others'] . "\nDescription: " . $booking['description'] . "\nDate: " . date('Y-m-d', strtotime($booking['created_at']))); ?>" target="_blank" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                                                Chat
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="px-4 py-2 border text-center">No bookings found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle mobile menu
        document.getElementById('menu-toggle').addEventListener('click', function () {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>