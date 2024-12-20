<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include "../../backend/db.php"; // Ensure this file has the correct $db variable
include "../session.php";

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Query to fetch image URL from the database
    $sql = "SELECT images, attend FROM makueni WHERE member_id = $user_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Output data of the first row
        $row = $result->fetch_assoc();
        $image_url = $row["images"]; 
        $attend_image = $row["attend"];
    } else {
        $image_url = "No results";
        $attend_image = "No results";
    }
} else {
    echo "User ID not set in session";
    header("Location: signin.php"); // Redirect to signin.php if user ID is not set
    exit();
}

// Close connection
$conn->close();
?>
