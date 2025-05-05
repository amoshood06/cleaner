<?php
// Start session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$bookingId = $_GET['id'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cleaner');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $profileImage);
$stmt->fetch();
$stmt->close();

// Handle status update
$statusMessage = '';
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $bookingId);
    
    if ($stmt->execute()) {
        $statusMessage = "Booking status updated to " . ucfirst($newStatus);
    } else {
        $statusMessage = "Error updating status: " . $conn->error;
    }
    
    $stmt->close();
}

// Fetch booking details with user information
$query = "SELECT b.*, u.username, u.email, u.phone, u.address 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          WHERE b.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found
    $conn->close();
    header("Location: bookings.php");
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Get booking statuses for dropdown
$statuses = ['pending', 'approved', 'in-progress', 'completed', 'cancelled'];

// Parse photos JSON if exists
$photos = [];
if (!empty($booking['photos'])) {
    $photos = json_decode($booking['photos'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // If not valid JSON, treat as single photo path
        $photos = [$booking['photos']];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking #<?php echo $bookingId; ?> | Cleaner Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#A086A3',
                        'primary-dark': '#8a7291',
                        sidebar: {
                            DEFAULT: '#f8f9fa',
                            foreground: '#333',
                            primary: '#A086A3',
                            'primary-foreground': '#fff',
                            accent: '#e9ecef',
                            'accent-foreground': '#333',
                            border: '#dee2e6'
                        }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .sidebar-width {
                width: 250px;
            }
            .sidebar-width-collapsed {
                width: 0;
            }
            .main-content {
                width: calc(100% - 250px);
                margin-left: 250px;
            }
            .main-content-full {
                width: 100%;
                margin-left: 0;
            }
            .sidebar-transition {
                transition: all 0.3s ease;
            }
            .status-pending {
                background-color: #FEF3C7;
                color: #92400E;
            }
            .status-approved {
                background-color: #D1FAE5;
                color: #065F46;
            }
            .status-in-progress {
                background-color: #DBEAFE;
                color: #1E40AF;
            }
            .status-completed {
                background-color: #E0E7FF;
                color: #3730A3;
            }
            .status-cancelled {
                background-color: #FEE2E2;
                color: #B91C1C;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-primary text-white p-4 z-20 relative">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="mr-4 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <a href="#" class="text-lg font-bold">Cleaner Admin</a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center space-x-1 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                            <?php if ($profileImage): ?>
                                <img src="<?php echo htmlspecialchars('../' . $profileImage); ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <span class="hidden md:inline"><?php echo htmlspecialchars($username ?? 'Admin'); ?></span>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block">
                        <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                        <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-1">
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-sidebar fixed h-full z-10 sidebar-width sidebar-transition overflow-hidden shadow-lg md:block hidden">
            <div class="p-4 border-b border-sidebar-border">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white">
                        <?php if ($profileImage): ?>
                            <img src="<?php echo htmlspecialchars('../' . $profileImage); ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-medium text-sidebar-foreground"><?php echo htmlspecialchars($username ?? 'Admin'); ?></p>
                        <p class="text-xs text-gray-500">Administrator</p>
                    </div>
                </div>
            </div>
            <nav class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-4">Administration</p>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="bookings.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="adminregister.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Register Admin</span>
                        </a>
                    </li>
                    <li>
                        <a href="settings.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Settings</span>
                        </a>
                    </li>
                </ul>
                <div class="mt-8 pt-4 border-t border-sidebar-border">
                    <a href="../index.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="text-sidebar-foreground">Main Site</span>
                    </a>
                    <a href="logout.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden md:hidden"></div>

        <!-- Main Content -->
        <main id="main-content" class="flex-1 p-4 md:p-6 md:main-content main-content-full md:main-content sidebar-transition">
            <div class="max-w-5xl mx-auto">
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Booking #<?php echo $bookingId; ?></h1>
                        <p class="text-gray-600 mt-1">View and manage booking details</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="bookings.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Bookings
                        </a>
                    </div>
                </div>

                <?php if (!empty($statusMessage)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($statusMessage); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Booking Status Card -->
                    <div class="md:col-span-3 bg-white rounded-lg shadow-md p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Booking Status</h2>
                                <?php
                                $statusClasses = [
                                    'pending' => 'status-pending',
                                    'approved' => 'status-approved',
                                    'in-progress' => 'status-in-progress',
                                    'completed' => 'status-completed',
                                    'cancelled' => 'status-cancelled'
                                ];
                                $statusClass = isset($statusClasses[$booking['status']]) ? $statusClasses[$booking['status']] : 'status-pending';
                                ?>
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full mt-2 <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="mt-4 md:mt-0">
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="flex items-center space-x-2">
                                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $booking['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                        Update Status
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Name</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['username']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['email']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['phone']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Address</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['address']); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Booking Details</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Booking ID</p>
                                <p class="font-medium">#<?php echo $booking['id']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Cleaning Type</p>
                                <p class="font-medium"><?php echo htmlspecialchars($booking['cleaning_type']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date Created</p>
                                <p class="font-medium"><?php echo date('F j, Y, g:i a', strtotime($booking['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Room Details -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Room Details</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Bedrooms</p>
                                <p class="font-medium"><?php echo $booking['rooms']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Living Rooms</p>
                                <p class="font-medium"><?php echo $booking['parlors']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Bathrooms</p>
                                <p class="font-medium"><?php echo $booking['toilets']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Kitchens</p>
                                <p class="font-medium"><?php echo $booking['kitchens']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dining Rooms</p>
                                <p class="font-medium"><?php echo $booking['dining']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Other Rooms</p>
                                <p class="font-medium"><?php echo $booking['others']; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-3 bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Description</h2>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <p class="whitespace-pre-line"><?php echo htmlspecialchars($booking['description'] ?? 'No description provided.'); ?></p>
                        </div>
                    </div>

                    <!-- Photos -->
                    <?php if (!empty($photos)): ?>
                    <div class="md:col-span-3 bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Photos</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            <?php foreach ($photos as $photo): ?>
                                <div class="relative group">
                                    <img src="<?php echo htmlspecialchars('../' . $photo); ?>" alt="Booking Photo" class="w-full h-48 object-cover rounded-md">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <a href="<?php echo htmlspecialchars('../' . $photo); ?>" target="_blank" class="text-white bg-primary hover:bg-primary-dark px-3 py-1 rounded-md">View Full</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="md:col-span-3 bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Actions</h2>
                        <div class="flex flex-wrap gap-3">
                            <a href="bookings.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                Back to Bookings
                            </a>
                            <?php if ($booking['status'] === 'pending'): ?>
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="inline">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                        Approve Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="inline">
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($booking['status'] === 'approved'): ?>
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="inline">
                                    <input type="hidden" name="status" value="in-progress">
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Mark In Progress
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if ($booking['status'] === 'in-progress'): ?>
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="inline">
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Mark Completed
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar visibility
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mainContent = document.getElementById('main-content');
        
        function toggleSidebar() {
            sidebar.classList.toggle('hidden');
            
            if (window.innerWidth < 768) {
                sidebarOverlay.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');
            } else {
                mainContent.classList.toggle('main-content');
                mainContent.classList.toggle('main-content-full');
            }
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                
                if (!sidebar.classList.contains('hidden')) {
                    mainContent.classList.add('main-content');
                    mainContent.classList.remove('main-content-full');
                }
            } else {
                if (!sidebar.classList.contains('hidden')) {
                    mainContent.classList.remove('main-content');
                    mainContent.classList.add('main-content-full');
                }
            }
        });
    </script>
</body>
</html>