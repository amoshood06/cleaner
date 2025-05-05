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

// Handle user status update
$statusMessage = '';
if (isset($_POST['update_status']) && isset($_POST['user_id']) && isset($_POST['status'])) {
    $updateUserId = $_POST['user_id'];
    $newStatus = $_POST['status'];
    
    // Don't allow admins to deactivate themselves
    if ($updateUserId == $_SESSION['user_id'] && $newStatus == 'inactive') {
        $statusMessage = "You cannot deactivate your own account.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $updateUserId);
        
        if ($stmt->execute()) {
            $statusMessage = "User #$updateUserId status updated to " . ucfirst($newStatus);
        } else {
            $statusMessage = "Error updating status: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Handle user role update
if (isset($_POST['update_role']) && isset($_POST['user_id']) && isset($_POST['role'])) {
    $updateUserId = $_POST['user_id'];
    $newRole = $_POST['role'] == 'admin' ? 1 : 0;
    
    // Don't allow admins to remove their own admin status
    if ($updateUserId == $_SESSION['user_id'] && $newRole == 0) {
        $statusMessage = "You cannot remove your own admin status.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->bind_param("ii", $newRole, $updateUserId);
        
        if ($stmt->execute()) {
            $roleText = $newRole ? "Administrator" : "Customer";
            $statusMessage = "User #$updateUserId role updated to $roleText";
        } else {
            $statusMessage = "Error updating role: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Handle user deletion
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    $deleteUserId = $_POST['user_id'];
    
    // Don't allow admins to delete themselves
    if ($deleteUserId == $_SESSION['user_id']) {
        $statusMessage = "You cannot delete your own account.";
    } else {
        // First check if user has bookings
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->bind_param("i", $deleteUserId);
        $stmt->execute();
        $stmt->bind_result($bookingCount);
        $stmt->fetch();
        $stmt->close();
        
        if ($bookingCount > 0) {
            $statusMessage = "Cannot delete user #$deleteUserId. User has $bookingCount bookings. Deactivate the account instead.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $deleteUserId);
            
            if ($stmt->execute()) {
                $statusMessage = "User #$deleteUserId deleted successfully";
            } else {
                $statusMessage = "Error deleting user: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// Handle batch actions
if (isset($_POST['batch_action']) && isset($_POST['selected_users']) && !empty($_POST['selected_users'])) {
    $action = $_POST['batch_action'];
    $selectedUsers = $_POST['selected_users'];
    $count = count($selectedUsers);
    
    // Remove current user from selection to prevent self-modification
    $selectedUsers = array_filter($selectedUsers, function($id) {
        return $id != $_SESSION['user_id'];
    });
    
    if (count($selectedUsers) < $count) {
        $statusMessage = "Your account was excluded from the batch action.";
    }
    
    if (!empty($selectedUsers)) {
        $placeholders = implode(',', array_fill(0, count($selectedUsers), '?'));
        
        if ($action === 'delete') {
            // Check if selected users have bookings
            $stmt = $conn->prepare("SELECT user_id, COUNT(*) as booking_count FROM bookings WHERE user_id IN ($placeholders) GROUP BY user_id");
            $types = str_repeat('i', count($selectedUsers));
            $stmt->bind_param($types, ...$selectedUsers);
            $stmt->execute();
            $result = $stmt->get_result();
            $usersWithBookings = [];
            
            while ($row = $result->fetch_assoc()) {
                $usersWithBookings[$row['user_id']] = $row['booking_count'];
            }
            $stmt->close();
            
            // Filter out users with bookings
            $usersToDelete = array_filter($selectedUsers, function($id) use ($usersWithBookings) {
                return !isset($usersWithBookings[$id]);
            });
            
            if (!empty($usersToDelete)) {
                $deletePlaceholders = implode(',', array_fill(0, count($usersToDelete), '?'));
                $stmt = $conn->prepare("DELETE FROM users WHERE id IN ($deletePlaceholders)");
                $types = str_repeat('i', count($usersToDelete));
                $stmt->bind_param($types, ...$usersToDelete);
                
                if ($stmt->execute()) {
                    $deletedCount = $stmt->affected_rows;
                    $statusMessage .= " $deletedCount users deleted successfully.";
                } else {
                    $statusMessage .= " Error deleting users: " . $conn->error;
                }
                
                $stmt->close();
            }
            
            if (count($usersWithBookings) > 0) {
                $statusMessage .= " " . count($usersWithBookings) . " users could not be deleted because they have bookings.";
            }
        } elseif ($action === 'activate' || $action === 'deactivate') {
            $status = $action === 'activate' ? 'active' : 'inactive';
            $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id IN ($placeholders)");
            $types = 's' . str_repeat('i', count($selectedUsers));
            $params = array_merge([$status], $selectedUsers);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $updatedCount = $stmt->affected_rows;
                $statusMessage .= " $updatedCount users " . ($status === 'active' ? 'activated' : 'deactivated') . " successfully.";
            } else {
                $statusMessage .= " Error updating users: " . $conn->error;
            }
            
            $stmt->close();
        } elseif ($action === 'make_admin' || $action === 'remove_admin') {
            $isAdmin = $action === 'make_admin' ? 1 : 0;
            $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id IN ($placeholders)");
            $types = 'i' . str_repeat('i', count($selectedUsers));
            $params = array_merge([$isAdmin], $selectedUsers);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $updatedCount = $stmt->affected_rows;
                $statusMessage .= " $updatedCount users " . ($isAdmin ? 'made administrators' : 'removed from administrators') . " successfully.";
            } else {
                $statusMessage .= " Error updating users: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// Set up pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$offset = ($page - 1) * $recordsPerPage;

// Set up filtering
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Validate sort parameters
$allowedSortFields = ['id', 'username', 'email', 'created_at', 'status', 'is_admin'];
if (!in_array($sortBy, $allowedSortFields)) {
    $sortBy = 'id';
}

$allowedSortOrders = ['ASC', 'DESC'];
if (!in_array(strtoupper($sortOrder), $allowedSortOrders)) {
    $sortOrder = 'ASC';
}

// Build the query
$whereClause = [];
$params = [];
$types = '';

if (!empty($roleFilter)) {
    if ($roleFilter === 'admin') {
        $whereClause[] = "is_admin = 1";
    } elseif ($roleFilter === 'customer') {
        $whereClause[] = "is_admin = 0";
    }
}

if (!empty($statusFilter)) {
    $whereClause[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($searchTerm)) {
    $whereClause[] = "(username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$whereString = '';
if (!empty($whereClause)) {
    $whereString = "WHERE " . implode(" AND ", $whereClause);
}

// Count total records for pagination
$countQuery = "SELECT COUNT(*) FROM users $whereString";

$stmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stmt->bind_result($totalRecords);
$stmt->fetch();
$stmt->close();

$totalPages = ceil($totalRecords / $recordsPerPage);

// Get users with pagination and filtering
$query = "SELECT * FROM users $whereString ORDER BY $sortBy $sortOrder LIMIT ?, ?";

$stmt = $conn->prepare($query);
$paramsPaginated = $params;
$paramsPaginated[] = $offset;
$paramsPaginated[] = $recordsPerPage;
$typesPaginated = $types . 'ii';

$stmt->bind_param($typesPaginated, ...$paramsPaginated);
$stmt->execute();
$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();

// Get user statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(is_admin) as admins FROM users");
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM users GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
$statusStats = [];
while ($row = $result->fetch_assoc()) {
    $statusStats[$row['status']] = $row['count'];
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Cleaner Admin</title>
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
            .status-active {
                background-color: #D1FAE5;
                color: #065F46;
            }
            .status-inactive {
                background-color: #FEE2E2;
                color: #B91C1C;
            }
            .status-pending {
                background-color: #FEF3C7;
                color: #92400E;
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
                        <a href="users.php" class="flex items-center space-x-2 p-2 rounded-md bg-sidebar-accent text-primary font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span>Users</span>
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
            <div class="max-w-7xl mx-auto">
                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Manage Users</h1>
                        <p class="text-gray-600 mt-1">View and manage all system users</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-2">
                        <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                        <a href="adminregister.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add New User
                        </a>
                    </div>
                </div>

                <?php if (!empty($statusMessage)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                              stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($statusMessage); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- User Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-2xl font-semibold"><?php echo $stats['total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Users</p>
                                <p class="text-2xl font-semibold"><?php echo isset($statusStats['active']) ? $statusStats['active'] : 0; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Administrators</p>
                                <p class="text-2xl font-semibold"><?php echo $stats['admins']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Filter Users</h2>
                    <form action="users.php" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Search by name, email, phone..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <select id="role" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Administrators</option>
                                <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customers</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                            <select id="sort_by" name="sort_by" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="id" <?php echo $sortBy === 'id' ? 'selected' : ''; ?>>ID</option>
                                <option value="username" <?php echo $sortBy === 'username' ? 'selected' : ''; ?>>Username</option>
                                <option value="email" <?php echo $sortBy === 'email' ? 'selected' : ''; ?>>Email</option>
                                <option value="created_at" <?php echo $sortBy === 'created_at' ? 'selected' : ''; ?>>Registration Date</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <select id="sort_order" name="sort_order" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="ASC" <?php echo $sortOrder === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                <option value="DESC" <?php echo $sortOrder === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="per_page" class="block text-sm font-medium text-gray-700 mb-1">Records Per Page</label>
                            <select id="per_page" name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="10" <?php echo $recordsPerPage === 10 ? 'selected' : ''; ?>>10</option>
                                <option value="25" <?php echo $recordsPerPage === 25 ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo $recordsPerPage === 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $recordsPerPage === 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-2 lg:col-span-4 flex justify-end space-x-2">
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                                Apply Filters
                            </button>
                            <a href="users.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
                                Clear Filters
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Batch Actions -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-lg font-semibold mb-4">Batch Actions</h2>
                    <form id="batch-form" action="users.php" method="POST">
                        <div class="flex flex-wrap gap-2">
                            <select name="batch_action" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="">Select Action</option>
                                <option value="activate">Activate Users</option>
                                <option value="deactivate">Deactivate Users</option>
                                <option value="make_admin">Make Administrators</option>
                                <option value="remove_admin">Remove Administrator Role</option>
                                <option value="delete">Delete Users</option>
                            </select>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Apply to Selected
                            </button>
                            <button type="button" id="select-all" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
                                Select All
                            </button>
                            <button type="button" id="deselect-all" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
                                Deselect All
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold">Users</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Showing <?php echo min(($page - 1) * $recordsPerPage + 1, $totalRecords); ?> to 
                            <?php echo min($page * $recordsPerPage, $totalRecords); ?> of 
                            <?php echo $totalRecords; ?> users
                        </p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3">
                                        <div class="flex items-center">
                                            <input type="checkbox" id="select-all-checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <input type="checkbox" name="selected_users[]" form="batch-form" value="<?php echo $user['id']; ?>" class="user-checkbox h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $user['id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <?php if ($user['profile_image']): ?>
                                                            <img class="h-10 w-10 rounded-full object-cover" src="<?php echo htmlspecialchars('../' . $user['profile_image']); ?>" alt="Profile">
                                                        <?php else: ?>
                                                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                            <div class="text-xs text-primary">(You)</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? 'No phone'); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                        Administrator
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Customer
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusClasses = [
                                                    'active' => 'status-active',
                                                    'inactive' => 'status-inactive',
                                                    'pending' => 'status-pending'
                                                ];
                                                $statusClass = isset($statusClasses[$user['status']]) ? $statusClasses[$user['status']] : 'status-pending';
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="flex items-center space-x-2">
                                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-primary hover:text-primary-dark">
                                                        Edit
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <button type="button" onclick="openStatusModal(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" class="text-blue-600 hover:text-blue-800">
                                                            Status
                                                        </button>
                                                        <button type="button" onclick="openRoleModal(<?php echo $user['id']; ?>, <?php echo $user['is_admin']; ?>)" class="text-purple-600 hover:text-purple-800">
                                                            Role
                                                        </button>
                                                        <button type="button" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" class="text-red-600 hover:text-red-800">
                                                            Delete
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex flex-col sm:flex-row items-center justify-between">
                                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                                </div>
                                <div class="flex space-x-1">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=1&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchTerm); ?>&sort_by=<?php echo urlencode($sortBy); ?>&sort_order=<?php echo urlencode($sortOrder); ?>&per_page=<?php echo $recordsPerPage; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                            First
                                        </a>
                                        <a href="?page=<?php echo $page - 1; ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchTerm); ?>&sort_by=<?php echo urlencode($sortBy); ?>&sort_order=<?php echo urlencode($sortOrder); ?>&per_page=<?php echo $recordsPerPage; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                            Previous
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php
                                    // Show page numbers with ellipsis for large page counts
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);
                                    
                                    if ($startPage > 1) {
                                        echo '<span class="px-3 py-1">...</span>';
                                    }
                                    
                                    for ($i = $startPage; $i <= $endPage; $i++) {
                                        $activeClass = $i === $page ? 'bg-primary text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300';
                                        echo '<a href="?page=' . $i . '&role=' . urlencode($roleFilter) . '&status=' . urlencode($statusFilter) . '&search=' . urlencode($searchTerm) . '&sort_by=' . urlencode($sortBy) . '&sort_order=' . urlencode($sortOrder) . '&per_page=' . $recordsPerPage . '" class="px-3 py-1 ' . $activeClass . ' rounded-md">' . $i . '</a>';
                                    }
                                    
                                    if ($endPage < $totalPages) {
                                        echo '<span class="px-3 py-1">...</span>';
                                    }
                                    ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchTerm); ?>&sort_by=<?php echo urlencode($sortBy); ?>&sort_order=<?php echo urlencode($sortOrder); ?>&per_page=<?php echo $recordsPerPage; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                            Next
                                        </a>
                                        <a href="?page=<?php echo $totalPages; ?>&role=<?php echo urlencode($roleFilter); ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchTerm); ?>&sort_by=<?php echo urlencode($sortBy); ?>&sort_order=<?php echo urlencode($sortOrder); ?>&per_page=<?php echo $recordsPerPage; ?>" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                            Last
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Status Update Modal -->
    <div id="status-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Update User Status</h3>
                <button type="button" onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="users.php" method="POST">
                <input type="hidden" id="user_id_status" name="user_id" value="">
                
                <div class="mb-4">
                    <label for="status_update" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status_update" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeStatusModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="update_status" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Role Update Modal -->
    <div id="role-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Update User Role</h3>
                <button type="button" onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="users.php" method="POST">
                <input type="hidden" id="user_id_role" name="user_id" value="">
                
                <div class="mb-4">
                    <label for="role_update" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="role_update" name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="admin">Administrator</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeRoleModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" name="update_role" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete User Form (Hidden) -->
    <form id="delete-form" action="users.php" method="POST" class="hidden">
        <input type="hidden" id="delete_user_id" name="user_id" value="">
        <input type="hidden" name="delete_user" value="1">
    </form>

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
        
        // Status modal functions
        const statusModal = document.getElementById('status-modal');
        const userIdStatusInput = document.getElementById('user_id_status');
        const statusSelect = document.getElementById('status_update');
        
        function openStatusModal(userId, currentStatus) {
            userIdStatusInput.value = userId;
            
            // Set current status as selected
            for (let i = 0; i < statusSelect.options.length; i++) {
                if (statusSelect.options[i].value === currentStatus) {
                    statusSelect.selectedIndex = i;
                    break;
                }
            }
            
            statusModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeStatusModal() {
            statusModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // Role modal functions
        const roleModal = document.getElementById('role-modal');
        const userIdRoleInput = document.getElementById('user_id_role');
        const roleSelect = document.getElementById('role_update');
        
        function openRoleModal(userId, isAdmin) {
            userIdRoleInput.value = userId;
            
            // Set current role as selected
            roleSelect.selectedIndex = isAdmin ? 0 : 1;
            
            roleModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        
        function closeRoleModal() {
            roleModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        // Delete user function
        function confirmDelete(userId, username) {
            if (confirm(`Are you sure you want to delete user "${username}"? This action cannot be undone.`)) {
                document.getElementById('delete_user_id').value = userId;
                document.getElementById('delete-form').submit();
            }
        }
        
        // Batch selection functions
        const selectAllBtn = document.getElementById('select-all');
        const deselectAllBtn = document.getElementById('deselect-all');
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const userCheckboxes = document.querySelectorAll('.user-checkbox:not([disabled])');
        
        selectAllBtn.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            selectAllCheckbox.checked = true;
        });
        
        deselectAllBtn.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllCheckbox.checked = false;
        });
        
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
        
        // Batch form validation
        document.getElementById('batch-form').addEventListener('submit', function(e) {
            const action = this.querySelector('[name="batch_action"]').value;
            const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
            
            if (!action) {
                e.preventDefault();
                alert('Please select an action to perform.');
                return;
            }
            
            if (selectedUsers.length === 0) {
                e.preventDefault();
                alert('Please select at least one user.');
                return;
            }
            
            if (action === 'delete' && !confirm('Are you sure you want to delete the selected users? This action cannot be undone.')) {
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>