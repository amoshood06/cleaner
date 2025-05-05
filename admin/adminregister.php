<?php
// Start session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cleaner');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$username = '';
$email = '';
$password = '';
$confirm_password = '';
$phone = '';
$state = '';
$location = '';
$address = '';
$errors = [];
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $state = trim($_POST['state']);
    $location = trim($_POST['location']);
    $address = trim($_POST['address']);
    
    // Validate input
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    if (empty($state)) {
        $errors[] = "State is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    // Process profile image if uploaded
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Generate unique filename
        $new_file_name = time() . '_' . $file_name;
        $upload_path = $upload_dir . $new_file_name;
        
        // Check file extension
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_exts)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed";
        } else {
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $profile_image = 'uploads/' . $new_file_name;
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    // If no errors, insert new admin user
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, state, location, phone, address, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $state, $location, $phone, $address, $profile_image);
            $stmt->execute();
            $user_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert into admin_users table
            $stmt = $conn->prepare("INSERT INTO admin_users (user_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            $success = "Admin user created successfully";
            
            // Clear form data
            $username = '';
            $email = '';
            $password = '';
            $confirm_password = '';
            $phone = '';
            $state = '';
            $location = '';
            $address = '';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}

// Fetch admin user data for the header
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($admin_username, $admin_profile_image);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin | Cleaner</title>
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
                <a href="#" class="text-lg font-bold">Cleaner Admin</a>
            </div>
            <div class="flex items-center space-x-4">
                <div class="relative group">
                    <button class="flex items-center space-x-1 focus:outline-none">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center overflow-hidden">
                            <?php if ($admin_profile_image): ?>
                                <img src="<?php echo htmlspecialchars('../' . $admin_profile_image); ?>" alt="Profile" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <span class="hidden md:inline"><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></span>
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
                        <?php if ($admin_profile_image): ?>
                            <img src="<?php echo htmlspecialchars('../' . $admin_profile_image); ?>" alt="Profile" class="w-full h-full rounded-full object-cover">
                        <?php else: ?>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="font-medium text-sidebar-foreground"><?php echo htmlspecialchars($admin_username ?? 'Admin'); ?></p>
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
                        <a href="bookings.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sidebar-foreground">Bookings</span>
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
                        <a href="adminregister.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            <span>Register Admin</span>
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
            <div class="max-w-4xl mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Register New Admin</h1>
                    <p class="text-gray-600 mt-1">Create a new administrator account</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                        <div class="font-medium">Please fix the following errors:</div>
                        <ul class="mt-1 ml-5 list-disc">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold">Admin Information</h2>
                        <p class="text-sm text-gray-500 mt-1">Fill in the details for the new admin account</p>
                    </div>
                    
                    <form action="adminregister.php" method="POST" enctype="multipart/form-data" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                                <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters</p>
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                                <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($state); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary" required><?php echo htmlspecialchars($address); ?></textarea>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                                <input type="file" id="profile_image" name="profile_image" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <p class="text-xs text-gray-500 mt-1">Allowed formats: JPG, JPEG, PNG, GIF</p>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                                Register Admin
                            </button>
                        </div>
                    </form>
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