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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cleaningType = $_POST['cleaning_type'];
    $rooms = intval($_POST['rooms']);
    $parlors = intval($_POST['parlors']);
    $toilets = intval($_POST['toilets']);
    $kitchens = intval($_POST['kitchens']);
    $dining = intval($_POST['dining']);
    $others = trim($_POST['others']);
    $description = trim($_POST['description']);
    $photos = [];
    $status = 'pending'; // Default status for new bookings

    // Handle photo uploads
    if (!empty($_FILES['photos']['name'][0])) {
        $targetDir = "uploads/";
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        foreach ($_FILES['photos']['name'] as $key => $name) {
            if ($_FILES['photos']['error'][$key] === 0) {
                $fileName = time() . '_' . basename($name); // Add timestamp to prevent overwriting
                $filePath = $targetDir . $fileName;
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $filePath)) {
                    $photos[] = $filePath;
                }
            }
        }
    }

    // Save booking to database
    $photosSerialized = serialize($photos);
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, cleaning_type, rooms, parlors, toilets, kitchens, dining, others, description, photos, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiiisssss", $userId, $cleaningType, $rooms, $parlors, $toilets, $kitchens, $dining, $others, $description, $photosSerialized, $status);

    if ($stmt->execute()) {
        $success = "Booking submitted successfully!";
    } else {
        $error = "Error submitting booking: " . $stmt->error;
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
    <title>Book a Cleaning Session</title>
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
            .form-input-focus:focus {
                --tw-ring-color: #A086A3;
                --tw-ring-opacity: 0.5;
                --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
                --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
                box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
                border-color: #A086A3;
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
                        <a href="booking.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>New Booking</span>
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
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Book a Cleaning Session</h1>
                    <p class="text-gray-600 mt-1">Fill out the form below to request a cleaning service</p>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if (isset($success)): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?php echo $success; ?></span>
                            </div>
                            <div class="mt-3">
                                <a href="dashboard.php" class="inline-flex items-center text-sm font-medium text-green-700 hover:underline">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Return to Dashboard
                                </a>
                            </div>
                        </div>
                    <?php elseif (isset($error)): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?php echo $error; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-6">
                        <form action="booking.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label for="cleaning_type" class="block text-sm font-medium text-gray-700 mb-1">Cleaning Type</label>
                                    <select name="cleaning_type" id="cleaning_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm form-input-focus">
                                        <option value="">Select a cleaning type</option>
                                        <option value="Hall Cleaning">Hall Cleaning</option>
                                        <option value="Dusting">Dusting</option>
                                        <option value="Industrial Cleaning">Industrial Cleaning</option>
                                        <option value="Office Cleaning">Office Cleaning</option>
                                        <option value="Home Cleaning">Home Cleaning</option>
                                        <option value="Others">Others (Please Specify)</option>
                                    </select>
                                </div>
                                
                                <div id="others_container" class="hidden">
                                    <label for="others" class="block text-sm font-medium text-gray-700 mb-1">Specify Other Cleaning Type</label>
                                    <input type="text" name="others" id="others" class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm form-input-focus">
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h3 class="text-md font-medium text-gray-700 mb-3">Property Details</h3>
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="rooms" class="block text-sm font-medium text-gray-700 mb-1">Number of Rooms</label>
                                        <div class="flex">
                                            <button type="button" class="decrement-btn bg-gray-200 px-3 py-2 rounded-l-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">-</button>
                                            <input type="number" name="rooms" id="rooms" min="0" value="0" required class="w-full text-center border-y border-gray-300 py-2 form-input-focus">
                                            <button type="button" class="increment-btn bg-gray-200 px-3 py-2 rounded-r-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="parlors" class="block text-sm font-medium text-gray-700 mb-1">Number of Parlors</label>
                                        <div class="flex">
                                            <button type="button" class="decrement-btn bg-gray-200 px-3 py-2 rounded-l-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">-</button>
                                            <input type="number" name="parlors" id="parlors" min="0" value="0" required class="w-full text-center border-y border-gray-300 py-2 form-input-focus">
                                            <button type="button" class="increment-btn bg-gray-200 px-3 py-2 rounded-r-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="toilets" class="block text-sm font-medium text-gray-700 mb-1">Number of Toilets</label>
                                        <div class="flex">
                                            <button type="button" class="decrement-btn bg-gray-200 px-3 py-2 rounded-l-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">-</button>
                                            <input type="number" name="toilets" id="toilets" min="0" value="0" required class="w-full text-center border-y border-gray-300 py-2 form-input-focus">
                                            <button type="button" class="increment-btn bg-gray-200 px-3 py-2 rounded-r-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="kitchens" class="block text-sm font-medium text-gray-700 mb-1">Number of Kitchens</label>
                                        <div class="flex">
                                            <button type="button" class="decrement-btn bg-gray-200 px-3 py-2 rounded-l-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">-</button>
                                            <input type="number" name="kitchens" id="kitchens" min="0" value="0" required class="w-full text-center border-y border-gray-300 py-2 form-input-focus">
                                            <button type="button" class="increment-btn bg-gray-200 px-3 py-2 rounded-r-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="dining" class="block text-sm font-medium text-gray-700 mb-1">Number of Dining Areas</label>
                                        <div class="flex">
                                            <button type="button" class="decrement-btn bg-gray-200 px-3 py-2 rounded-l-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">-</button>
                                            <input type="number" name="dining" id="dining" min="0" value="0" required class="w-full text-center border-y border-gray-300 py-2 form-input-focus">
                                            <button type="button" class="increment-btn bg-gray-200 px-3 py-2 rounded-r-lg border border-gray-300 hover:bg-gray-300 focus:outline-none">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Additional Details</label>
                                <textarea name="description" id="description" rows="4" placeholder="Please provide any additional details about your cleaning needs..." class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm form-input-focus"></textarea>
                            </div>

                            <div>
                                <label for="photos" class="block text-sm font-medium text-gray-700 mb-1">Upload Photos</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="photos" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none">
                                                <span>Upload files</span>
                                                <input id="photos" name="photos[]" type="file" multiple class="sr-only">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                </div>
                                <div id="file-preview" class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                            </div>

                            <div class="flex items-center justify-between pt-4">
                                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Cancel
                                </a>
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Submit Booking
                                </button>
                            </div>
                        </form>
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

        // Handle increment/decrement buttons
        document.querySelectorAll('.increment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                input.value = parseInt(input.value) + 1;
            });
        });

        document.querySelectorAll('.decrement-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                }
            });
        });

        // Show/hide others field based on cleaning type selection
        const cleaningTypeSelect = document.getElementById('cleaning_type');
        const othersContainer = document.getElementById('others_container');

        cleaningTypeSelect.addEventListener('change', function() {
            if (this.value === 'Others') {
                othersContainer.classList.remove('hidden');
            } else {
                othersContainer.classList.add('hidden');
            }
        });

        // File preview functionality
        const fileInput = document.getElementById('photos');
        const filePreview = document.getElementById('file-preview');

        fileInput.addEventListener('change', function() {
            filePreview.innerHTML = '';
            
            for (let i = 0; i < this.files.length; i++) {
                const file = this.files[i];
                if (!file.type.startsWith('image/')) continue;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'h-24 w-full object-cover rounded-md';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'absolute top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center -mt-2 -mr-2';
                    removeBtn.innerHTML = '×';
                    removeBtn.addEventListener('click', function() {
                        div.remove();
                    });
                    
                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    filePreview.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>