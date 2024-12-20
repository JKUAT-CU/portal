<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include db.php and session.php (assumes $mysqli is available)
include "../../backend/db.php"; // Ensure this file has the correct $mysqli variable
include "../session.php"; // Ensure session handling is implemented properly

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Prepare and execute query securely
    if ($stmt = $mysqli->prepare("SELECT images FROM makueni WHERE member_id = ?")) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Fetch results using bind_result
        $stmt->bind_result($image_url);

        if ($stmt->fetch()) {
            // $image_url is now populated with the fetched value
        } else {
            $image_url = "default.jpeg"; // Use a default image if no result
        }

        // Close statement after execution
        $stmt->close();
    } else {
        error_log("Query preparation failed: " . $mysqli->error);
        echo "An error occurred. Please try again later.";
        exit();
    }
} else {
    error_log("Access denied: User ID not set in session");
    header("Location: signin.php"); // Redirect to signin.php if no user_id in session
    exit();
}

// Close connection if valid (check if $mysqli is initialized properly)
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $mysqli->close();
}
?>
