<?php

// Database connection
$host = 'localhost';
$user = 'jkuatcu_devs';
$password = '#God@isAble!#';  // Ensure this is the correct password
$database = 'jkuatcu_admin';

// // Database connection
// $host = 'localhost';
// $user = 'portals';
// $password = 'I&Y*U&^(JN&Y Kjbkjn'; // Ensure this is the correct password
// $database = 'jkuatcu_data';

// Create connection
$mysqli = new mysqli($host, $user, $password, $database);

?>
<?php
// Start session
session_start();

// Database credentials
$host = 'localhost';
$user = 'portals';
$password = 'I&Y*U&^(JN&Y Kjbkjn';
$database = 'jkuatcu_data';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is set in session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query to fetch image URL from the user table
    $userSql = "SELECT images FROM makueni WHERE member_id = ?";
    $stmt = $conn->prepare($userSql);

    if ($stmt) {
        // Bind parameters and execute statement
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the query returned results
        if ($result->num_rows > 0) {
            // Fetch the image path from the database
            $row = $result->fetch_assoc();
            $image_path = $row['images'];

            // Include the appropriate script based on the image path
            if (!empty($image_path)) {
                include 'download.php';
            } else {
                include 'script.php';
            }
        } else {
            echo "No results found for user ID: $user_id";
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Prepared statement creation failed
        echo "Error: " . $conn->error;
    }
} else {
    // User ID not set in session
    echo "User ID not set in session";
    // Optionally redirect the user to the signin page
    // header("Location: signin.php");
    // exit();
}

// Close connection
$conn->close();
?>
