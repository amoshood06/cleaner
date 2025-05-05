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
$stmt = $conn->prepare("SELECT username, state, location, phone, address, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($username, $state, $location, $phone, $address, $profileImage);
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
                        <a href="profile.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>Profile</span>
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
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="md:flex">
                        <!-- Profile Sidebar -->
                        <div class="md:w-1/3 bg-gray-50 p-6 border-r border-gray-200">
                            <div class="flex flex-col items-center">
                                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg mb-4">
                                    <?php if ($profileImage): ?>
                                        <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary flex items-center justify-center text-white text-4xl">
                                            <?php echo strtoupper(substr($username ?? 'U', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($username ?? 'User'); ?></h3>
                                <p class="text-gray-500 text-sm mt-1"><?php echo htmlspecialchars($location ?? 'No location set'); ?></p>
                            </div>
                            <div class="mt-6">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase mb-2">Contact Information</h4>
                                <ul class="space-y-2">
                                    <li class="flex items-center text-sm">
                                        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <?php echo htmlspecialchars($phone ?? 'No phone number'); ?>
                                    </li>
                                    <li class="flex items-start text-sm">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span><?php echo htmlspecialchars($address ?? 'No address set'); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="md:w-2/3 p-6">
                            <h2 class="text-2xl font-bold mb-6">Edit Profile</h2>
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
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                                        <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($state ?? ''); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                                    </div>
                                    <div>
                                        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                        <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($location ?? ''); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                                    </div>
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="address" id="address" rows="3" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                                    <input type="file" name="profile_image" id="profile_image" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                                </div>
                                <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded-lg hover:bg-primary-dark transition">Save Changes</button>
                            </form>
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