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

// Fetch user profile data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $profileImage);
$stmt->fetch();
$stmt->close();

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$bookingId = intval($_GET['id']);

// Fetch booking details
$booking = null;
$error = null;

$stmt = $conn->prepare("SELECT b.*, u.username, u.email, u.phone 
                       FROM bookings b 
                       JOIN users u ON b.user_id = u.id 
                       WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    // Unserialize photos array
    $booking['photos'] = !empty($booking['photos']) ? unserialize($booking['photos']) : [];
} else {
    $error = "Booking not found or you don't have permission to view it.";
}
$stmt->close();

// Handle status update if user is admin
$statusUpdated = false;
if (isset($_POST['update_status']) && isset($_POST['status'])) {
    // Check if user is admin
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($adminCount);
        $stmt->fetch();
        $isAdmin = ($adminCount > 0);
        $stmt->close();
    }
    
    if ($isAdmin > 0) {
        $newStatus = $_POST['status'];
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $bookingId);
        
        if ($stmt->execute()) {
            $statusUpdated = true;
            // Update the booking status in our current data
            $booking['status'] = $newStatus;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking Details</title>
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
                <a href="#" class="text-lg font-bold">Cleaner</a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center space-x-1 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                            <?php if ($profileImage): ?>
                                <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <span class="hidden md:inline"><?php echo htmlspecialchars($username ?? 'User'); ?></span>
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
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-medium text-sidebar-foreground"><?php echo htmlspecialchars($username ?? 'User'); ?></p>
                        <p class="text-xs text-gray-500">Member</p>
                    </div>
                </div>
            </div>
            <nav class="p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-4">Navigation</p>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="booking.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">New Booking</span>
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
            <div class="max-w-4xl mx-auto">
                <?php if ($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?php echo $error; ?></span>
                        </div>
                        <div class="mt-3">
                            <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-red-700 hover:underline">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                <?php elseif ($booking): ?>
                    <?php if ($statusUpdated): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Booking status updated successfully!</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-2xl font-bold">Booking Details</h1>
                            <p class="text-gray-600 mt-1">View and manage your booking information</p>
                        </div>
                        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b">
                                <div>
                                    <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($booking['cleaning_type']); ?></h2>
                                    <p class="text-gray-500 text-sm">Booking #<?php echo $booking['id']; ?></p>
                                </div>
                                <div class="mt-2 md:mt-0">
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
                                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-medium mb-4">Booking Information</h3>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Cleaning Type:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($booking['cleaning_type']); ?></span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Date Requested:</span>
                                            <span class="font-medium"><?php echo date('F j, Y', strtotime($booking['created_at'])); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($booking['others'])): ?>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Other Specifications:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($booking['others']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="text-lg font-medium mt-6 mb-4">Property Details</h3>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <span class="block text-sm text-gray-500">Rooms</span>
                                            <span class="text-lg font-medium"><?php echo $booking['rooms']; ?></span>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <span class="block text-sm text-gray-500">Parlors</span>
                                            <span class="text-lg font-medium"><?php echo $booking['parlors']; ?></span>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <span class="block text-sm text-gray-500">Toilets</span>
                                            <span class="text-lg font-medium"><?php echo $booking['toilets']; ?></span>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <span class="block text-sm text-gray-500">Kitchens</span>
                                            <span class="text-lg font-medium"><?php echo $booking['kitchens']; ?></span>
                                        </div>
                                        
                                        <div class="bg-gray-50 p-3 rounded-lg">
                                            <span class="block text-sm text-gray-500">Dining Areas</span>
                                            <span class="text-lg font-medium"><?php echo $booking['dining']; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-lg font-medium mb-4">Customer Information</h3>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Name:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($booking['username']); ?></span>
                                        </div>
                                        
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Email:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($booking['email']); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($booking['phone'])): ?>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Phone:</span>
                                            <span class="font-medium"><?php echo htmlspecialchars($booking['phone']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!empty($booking['description'])): ?>
                                    <div class="mt-6">
                                        <h3 class="text-lg font-medium mb-2">Additional Details</h3>
                                        <div class="bg-gray-50 p-4 rounded-lg">
                                            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($booking['description'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($booking['photos'])): ?>
                                    <div class="mt-6">
                                        <h3 class="text-lg font-medium mb-2">Photos</h3>
                                        <div class="grid grid-cols-2 gap-2">
                                            <?php foreach ($booking['photos'] as $photo): ?>
                                            <div class="relative group">
                                                <img src="<?php echo htmlspecialchars($photo); ?>" alt="Booking Photo" class="w-full h-32 object-cover rounded-lg">
                                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-lg">
                                                    <a href="<?php echo htmlspecialchars($photo); ?>" target="_blank" class="text-white hover:underline">View Full Size</a>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php
                            // Check if user is admin to show status update form
                            $stmt = $conn = new mysqli('localhost', 'root', '', 'cleaner');
                            $isAdmin = false;
                            
                            if (!$conn->connect_error) {
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE user_id = ?");
                                if ($stmt) {
                                    $stmt->bind_param("i", $userId);
                                    $stmt->execute();
                                    $stmt->bind_result($adminCount);
                                    $stmt->fetch();
                                    $isAdmin = ($adminCount > 0);
                                    $stmt->close();
                                }
                                $conn->close();
                            }
                            
                            if ($isAdmin):
                            ?>
                            <div class="mt-8 pt-6 border-t">
                                <h3 class="text-lg font-medium mb-4">Update Booking Status</h3>
                                <form action="view_booking.php?id=<?php echo $bookingId; ?>" method="POST" class="flex items-center space-x-4">
                                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $booking['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="in-progress" <?php echo $booking['status'] == 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:outline-none">
                                        Update Status
                                    </button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
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