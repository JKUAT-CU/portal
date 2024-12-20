<?php
// Enable error logging (useful for production)
ini_set('log_errors', 1);
ini_set('error_log', '../../backend/logs/error.log'); // Adjust the path
error_reporting(E_ALL);

include "../../backend/db.php"; // Ensure this file has the correct $mysqli variable
include "../session.php";

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Prepare and execute query securely
    $stmt = $mysqli->prepare("SELECT images FROM makueni WHERE member_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Fetch results using bind_result
        $stmt->bind_result($image_url);

        if ($stmt->fetch()) {
            // $image_url is now populated with the fetched value
        } else {
            $image_url = "default_image_url_here.jpg"; // Use a default image
        }

        $stmt->close();
    } else {
        error_log("Query preparation failed: " . $mysqli->error);
        echo "An error occurred. Please try again later.";
        exit();
    }
} else {
    error_log("Access denied: User ID not set in session");
    header("Location: signin.php");
    exit();
}

// Close connection
$mysqli->close();
?>
