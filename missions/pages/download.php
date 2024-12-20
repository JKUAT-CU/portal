<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $image_url = $row["images"];
  
        } else {
            $image_url = "No results";
           
        }
        $stmt->close();
    } else {
        echo "Query preparation failed: " . $mysqli->error;
        exit();
    }
} else {
    echo "User ID not set in session";
    header("Location: signin.php");
    exit();
}

// Close connection
$mysqli->close();
?>
