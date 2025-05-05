<?php
// Start session
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

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

// Initialize status message
$statusMessage = '';

// Check if settings table exists, if not create it
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_group VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Check if service_types table exists, if not create it
$conn->query("CREATE TABLE IF NOT EXISTS service_types (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Handle general settings update
if (isset($_POST['update_general_settings'])) {
    $companyName = $_POST['company_name'];
    $companyEmail = $_POST['company_email'];
    $companyPhone = $_POST['company_phone'];
    $companyAddress = $_POST['company_address'];
    $websiteTitle = $_POST['website_title'];
    $websiteDescription = $_POST['website_description'];
    
    // Update or insert settings
    $settings = [
        'company_name' => $companyName,
        'company_email' => $companyEmail,
        'company_phone' => $companyPhone,
        'company_address' => $companyAddress,
        'website_title' => $websiteTitle,
        'website_description' => $websiteDescription
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                               VALUES (?, ?, 'general') 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    
    $statusMessage = "General settings updated successfully.";
}

// Handle booking settings update
if (isset($_POST['update_booking_settings'])) {
    $bookingLeadTime = $_POST['booking_lead_time'];
    $maxBookingDays = $_POST['max_booking_days'];
    $minBookingHours = $_POST['min_booking_hours'];
    $allowWeekends = isset($_POST['allow_weekends']) ? 1 : 0;
    $allowHolidays = isset($_POST['allow_holidays']) ? 1 : 0;
    $autoApprove = isset($_POST['auto_approve']) ? 1 : 0;
    
    // Update or insert settings
    $settings = [
        'booking_lead_time' => $bookingLeadTime,
        'max_booking_days' => $maxBookingDays,
        'min_booking_hours' => $minBookingHours,
        'allow_weekends' => $allowWeekends,
        'allow_holidays' => $allowHolidays,
        'auto_approve' => $autoApprove
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                               VALUES (?, ?, 'booking') 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    
    $statusMessage = "Booking settings updated successfully.";
}

// Handle email settings update
if (isset($_POST['update_email_settings'])) {
    $smtpHost = $_POST['smtp_host'];
    $smtpPort = $_POST['smtp_port'];
    $smtpUsername = $_POST['smtp_username'];
    $smtpPassword = $_POST['smtp_password'];
    $smtpEncryption = $_POST['smtp_encryption'];
    $fromEmail = $_POST['from_email'];
    $fromName = $_POST['from_name'];
    
    // Update or insert settings
    $settings = [
        'smtp_host' => $smtpHost,
        'smtp_port' => $smtpPort,
        'smtp_username' => $smtpUsername,
        'smtp_password' => $smtpPassword,
        'smtp_encryption' => $smtpEncryption,
        'from_email' => $fromEmail,
        'from_name' => $fromName
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                               VALUES (?, ?, 'email') 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    
    $statusMessage = "Email settings updated successfully.";
}

// Handle notification settings update
if (isset($_POST['update_notification_settings'])) {
    $notifyNewBooking = isset($_POST['notify_new_booking']) ? 1 : 0;
    $notifyBookingUpdate = isset($_POST['notify_booking_update']) ? 1 : 0;
    $notifyBookingCancel = isset($_POST['notify_booking_cancel']) ? 1 : 0;
    $notifyNewUser = isset($_POST['notify_new_user']) ? 1 : 0;
    $adminEmails = $_POST['admin_emails'];
    
    // Update or insert settings
    $settings = [
        'notify_new_booking' => $notifyNewBooking,
        'notify_booking_update' => $notifyBookingUpdate,
        'notify_booking_cancel' => $notifyBookingCancel,
        'notify_new_user' => $notifyNewUser,
        'admin_emails' => $adminEmails
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) 
                               VALUES (?, ?, 'notification') 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param("sss", $key, $value, $value);
        $stmt->execute();
        $stmt->close();
    }
    
    $statusMessage = "Notification settings updated successfully.";
}

// Handle service type addition
if (isset($_POST['add_service_type'])) {
    $name = $_POST['service_name'];
    $description = $_POST['service_description'];
    $price = $_POST['service_price'];
    $duration = $_POST['service_duration'];
    $isActive = isset($_POST['service_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO service_types (name, description, price, duration, is_active) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdii", $name, $description, $price, $duration, $isActive);
    
    if ($stmt->execute()) {
        $statusMessage = "Service type added successfully.";
    } else {
        $statusMessage = "Error adding service type: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle service type update
if (isset($_POST['update_service_type'])) {
    $id = $_POST['service_id'];
    $name = $_POST['service_name'];
    $description = $_POST['service_description'];
    $price = $_POST['service_price'];
    $duration = $_POST['service_duration'];
    $isActive = isset($_POST['service_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE service_types SET name = ?, description = ?, price = ?, duration = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssdiii", $name, $description, $price, $duration, $isActive, $id);
    
    if ($stmt->execute()) {
        $statusMessage = "Service type updated successfully.";
    } else {
        $statusMessage = "Error updating service type: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle service type deletion
if (isset($_POST['delete_service_type'])) {
    $id = $_POST['service_id'];
    
    // Check if service type is used in any bookings
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE cleaning_type = (SELECT name FROM service_types WHERE id = ?)");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($bookingCount);
    $stmt->fetch();
    $stmt->close();
    
    if ($bookingCount > 0) {
        $statusMessage = "Cannot delete service type. It is used in $bookingCount bookings.";
    } else {
        $stmt = $conn->prepare("DELETE FROM service_types WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $statusMessage = "Service type deleted successfully.";
        } else {
            $statusMessage = "Error deleting service type: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Fetch all settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value, setting_group FROM settings");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fetch all service types
$serviceTypes = [];
$result = $conn->query("SELECT * FROM service_types ORDER BY name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $serviceTypes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | Cleaner Admin</title>
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
                        <a href="bookings.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-sidebar-accent group">
                            <svg class="w-5 h-5 text-gray-500 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                        <a href="settings.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Settings</span>
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
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">System Settings</h1>
                        <p class="text-gray-600 mt-1">Configure system settings and preferences</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white