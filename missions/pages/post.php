<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../../backend/db.php"; // This file defines $mysqli
require_once "../session.php";
session_start();

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

        // Query to retrieve account number and name from the database using user_id
        $userSql = "SELECT name, account_no FROM makueni WHERE id = ?";
        $stmt = $mysqli->prepare($userSql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the query was successful
        if ($result && $result->num_rows > 0) {
            // Fetch the data as an associative array
            $row = $result->fetch_assoc();
            $accountNo = $row['account_no'];
            $name = $row['name'];

            // Define the default poster image paths
            $defaultPosterPath = 'uploads/makueni.png';
           

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
            $font = realpath('Futura-Bold.ttf'); // Get the absolute path dynamically
            $fontSize = 30;

            // Add account number to the first poster
            imagettftext($posterImage, $fontSize, 0, 942, 1086, $textColor, $font, $accountNo);

            // Define the path to save the merged image for the first poster
            $mergedImagePath = 'uploads/' . $user_id . '.png';

            // Save the merged image as a new file for the first poster
            imagepng($posterImage, $mergedImagePath);

            // Free up memory for the first poster
            imagedestroy($posterImage);

            // Update the database with the image link for the first poster
            $imageLink1 = '../' . $mergedImagePath;
            $query1 = "UPDATE user SET image = ? WHERE id = ?";
            $stmt1 = $mysqli->prepare($query1);
            $stmt1->bind_param("si", $imageLink1, $user_id);
            if (!$stmt1->execute()) {
                echo "Error updating image link in the database for the first poster.";
                exit();
            }

            // Create a new image from the second poster template
            $posterImage = imagecreatefrompng($poster2);

            // Merge the uploaded image with the second poster image
            imagecopy($posterImage, $uploadedImage, $x, $y, 0, 0, $uploadedImageWidth, $uploadedImageHeight);

            // Define the path to save the merged image for the second poster
            $mergedImagePath2 = 'uploads/attending_' . $user_id . '.png';

            // Save the merged image as a new file for the second poster
            imagepng($posterImage, $mergedImagePath2);

            // Free up memory for the second poster
            imagedestroy($posterImage);
            imagedestroy($uploadedImage);

            // Update the database with the image link for the second poster
            $imageLink2 = '../' . $mergedImagePath2;
            $query2 = "UPDATE user SET attend = ? WHERE id = ?";
            $stmt2 = $mysqli->prepare($query2);
            $stmt2->bind_param("si", $imageLink2, $user_id);
            if (!$stmt2->execute()) {
                echo "Error updating image link in the database for the second poster.";
                exit();
            }

        } else {
            // Handle the case where no account information is found
            echo "Error: Unable to retrieve account information.";
        }

    } else {
        // Handle invalid request method or missing image data
        echo "Error: Invalid request or missing image data.";
    }
} else {
    // Handle the case where user_id is not set in session
    echo 'Error: User ID not set in session.';
}
?>
