<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../../backend/db.php"; // This file defines $mysqli
require_once "../session.php";

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check if the request method is POST and if an image base64 data is sent
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imgBase64'])) {
        // Define the maximum file size (5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

        // Get the image base64 data
        $imgBase64 = $_POST['imgBase64'];

        // Decode the base64 data
        $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imgBase64));

        // Check if the image data is valid
        if (!$imgData || strlen($imgData) > $maxFileSize) {
            echo 'Error: Invalid image data.';
            exit();
        }

        // Query to retrieve account number, existing images, and amount from the database
        $userSql = "SELECT account_number, images, amount FROM makueni WHERE member_id = ?";

        // Prepare and execute the SELECT query
        $stmt = $mysqli->prepare($userSql);
        if ($stmt === false) {
            echo "Error preparing SELECT query: " . $mysqli->error;
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Bind the result variables
        $accountNo = $existingImages = $amount = null; // Initialize the variables
        $stmt->bind_result($accountNo, $existingImages, $amount);

        // Fetch the results
        if ($stmt->fetch()) {
            // Use $amount safely here (e.g., format it)
            $amount = isset($amount) ? $amount : 0; // Set a default value if it's NULL

            // Process image upload and poster creation
            // ...

            // Format amount and add it to the image
            $formattedAmount = number_format($amount);
            // Add text to the poster here...
        } else {
            echo "Error: Unable to retrieve account information.";
        }

        // Free result and close the prepared statement
        $stmt->close();
    } else {
        echo "Error: Invalid request or missing image data.";
    }
} else {
    echo 'Error: User ID not set in session.';
}
?>
