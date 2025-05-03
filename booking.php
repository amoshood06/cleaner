<!-- filepath: c:\xampp\htdocs\cleaner\booking.php -->
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
    $userId = $_SESSION['user_id'];
    $cleaningType = $_POST['cleaning_type'];
    $rooms = intval($_POST['rooms']);
    $parlors = intval($_POST['parlors']);
    $toilets = intval($_POST['toilets']);
    $kitchens = intval($_POST['kitchens']);
    $dining = intval($_POST['dining']);
    $others = trim($_POST['others']);
    $description = trim($_POST['description']);
    $photos = [];

    // Handle photo uploads
    if (!empty($_FILES['photos']['name'][0])) {
        $targetDir = "uploads/";
        foreach ($_FILES['photos']['name'] as $key => $name) {
            $filePath = $targetDir . basename($name);
            if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $filePath)) {
                $photos[] = $filePath;
            }
        }
    }

    // Save booking to database
    $photosSerialized = serialize($photos);
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, cleaning_type, rooms, parlors, toilets, kitchens, dining, others, description, photos) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiiiisss", $userId, $cleaningType, $rooms, $parlors, $toilets, $kitchens, $dining, $others, $description, $photosSerialized);

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
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl">
        <h2 class="text-2xl font-bold text-center mb-6">Book a Cleaning Session</h2>
        <?php if (isset($success)): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="booking.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="cleaning_type" class="block text-sm font-medium text-gray-700">Cleaning Type</label>
                <select name="cleaning_type" id="cleaning_type" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
                    <option value="Hall Cleaning">Hall Cleaning</option>
                    <option value="Dusting">Dusting</option>
                    <option value="Industrial Cleaning">Industrial Cleaning</option>
                    <option value="Others">Others (Please Specify)</option>
                </select>
            </div>
            <div>
                <label for="rooms" class="block text-sm font-medium text-gray-700">Number of Rooms</label>
                <input type="number" name="rooms" id="rooms" min="0" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="parlors" class="block text-sm font-medium text-gray-700">Number of Parlors</label>
                <input type="number" name="parlors" id="parlors" min="0" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="toilets" class="block text-sm font-medium text-gray-700">Number of Toilets</label>
                <input type="number" name="toilets" id="toilets" min="0" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="kitchens" class="block text-sm font-medium text-gray-700">Number of Kitchens</label>
                <input type="number" name="kitchens" id="kitchens" min="0" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="dining" class="block text-sm font-medium text-gray-700">Number of Dining Areas</label>
                <input type="number" name="dining" id="dining" min="0" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="others" class="block text-sm font-medium text-gray-700">Others (Please Specify)</label>
                <input type="text" name="others" id="others" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary"></textarea>
            </div>
            <div>
                <label for="photos" class="block text-sm font-medium text-gray-700">Upload Photos</label>
                <input type="file" name="photos[]" id="photos" multiple class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-primary focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-[#A086A3] text-white py-2 px-4 rounded-lg hover:bg-primary-dark transition">Submit Booking</button>
        </form>
    </div>
</body>
</html>