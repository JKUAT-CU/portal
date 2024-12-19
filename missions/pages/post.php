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

        // Query to retrieve first_name, surname, and account_no from the database using user_id
        $userSql = "
        SELECT cm.first_name, cm.surname, m.account_number
        FROM cu_members cm
        JOIN makueni m ON cm.id = m.member_id
        WHERE cm.id = ?
        ";

        // Prepare and execute the SELECT query
        $stmt = $mysqli->prepare($userSql);
        if ($stmt === false) {
            echo "Error preparing SELECT query: " . $mysqli->error;
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Bind the result variables
        $stmt->bind_result($firstName, $surname, $accountNo);

        // Fetch the results
        if ($stmt->fetch()) {
            // Define the default poster image paths
            $defaultPosterPath = '../uploads/makueni.png';

            // Create a new image from the default poster image
            $posterImage = imagecreatefrompng($defaultPosterPath);

            // Create a new image from the uploaded image data
            $uploadedImage = imagecreatefromstring($imgData);

            // Get the dimensions of the uploaded image
            $uploadedImageWidth = imagesx($uploadedImage);
            $uploadedImageHeight = imagesy($uploadedImage);

            // Define the coordinates of the center of the uploaded image for the first poster
            $centerX = 336; // X-coordinate
            $centerY = 597; // Y-coordinate

            // Calculate the top-left corner coordinates for positioning the image based on its center
            $x = $centerX - ($uploadedImageWidth / 2);
            $y = $centerY - ($uploadedImageHeight / 2);

            // Merge the uploaded image with the poster image
            imagecopy($posterImage, $uploadedImage, $x, $y, 0, 0, $uploadedImageWidth, $uploadedImageHeight);

            // Define text color and font properties for the first poster
            $textColor = imagecolorallocate($posterImage, 0x00, 0x68, 0x38); // Hex color #006838
            $font = realpath('../assets/fonts/Futura-Bold.ttf'); // Get the absolute path dynamically
            $fontSize = 30;

            // Add account number to the first poster
            imagettftext($posterImage, $fontSize, 0, 942, 1086, $textColor, $font, $accountNo);

            // Define the path to save the merged image for the first poster
            $mergedImagePath = '../uploads/' . $user_id . '.png';

            // Save the merged image as a new file for the first poster
            imagepng($posterImage, $mergedImagePath);

            // Free up memory for the first poster
            imagedestroy($posterImage);

            // Update the database with the image link for the first poster
            $imageLink1 = '../' . $mergedImagePath;

            // Prepare the UPDATE query
            $query1 = "UPDATE makueni SET images = ? WHERE member_id = ?";
            $stmt1 = $mysqli->prepare($query1);

            if ($stmt1 === false) {
                echo "Error preparing UPDATE query: " . $mysqli->error;
                exit();
            }

            // Bind parameters and execute the UPDATE query
            $stmt1->bind_param("si", $imageLink1, $user_id);
            if (!$stmt1->execute()) {
                echo "Error executing the UPDATE query: " . $stmt1->error;
                exit();
            }

            // Successfully updated image link
            echo "Image uploaded and database updated successfully.";
        } else {
            // Handle the case where no account information is found
            echo "Error: Unable to retrieve account information.";
        }

        // Free result and close the prepared statement
        $stmt->free_result();
        $stmt->close();
    } else {
        // Handle invalid request method or missing image data
        echo "Error: Invalid request or missing image data.";
    }
} else {
    // Handle the case where user_id is not set in session
    echo 'Error: User ID not set in session.';
}
?>
