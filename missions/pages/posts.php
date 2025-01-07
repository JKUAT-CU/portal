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

        // Query to retrieve account number and existing images from the database using user_id
        $userSql = "SELECT account_number, images, amount FROM makueni WHERE member_id = ?";

        // Prepare and execute the SELECT query
        $stmt = $mysqli->prepare($userSql);
        if ($stmt === false) {
            echo "Error preparing SELECT query: " . $mysqli->error;
            exit();
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

 // Bind the result variables, including the amount
$stmt->bind_result($accountNo, $existingImages, $amount);

// Fetch the results
if ($stmt->fetch()) {
    // Define the default poster image path
    $defaultPosterPath = '../uploads/makueni.png';
    
    // Create a new true-color image with the dimensions of the default poster
    list($posterWidth, $posterHeight) = getimagesize($defaultPosterPath);
    $posterImage = imagecreatetruecolor($posterWidth, $posterHeight);
    
    // Fill the poster image with a specific background color (e.g., white)
    $backgroundColor = imagecolorallocate($posterImage, 255, 255, 255); // White background
    imagefill($posterImage, 0, 0, $backgroundColor);
    
    // Load the default poster image onto the new canvas
    $defaultPosterImage = imagecreatefrompng($defaultPosterPath);
    imagecopy($posterImage, $defaultPosterImage, 0, 0, 0, 0, $posterWidth, $posterHeight);

    // Create a new image from the uploaded image data
    $uploadedImage = imagecreatefromstring($imgData);

    // Get the dimensions of the uploaded image
    $uploadedImageWidth = imagesx($uploadedImage);
    $uploadedImageHeight = imagesy($uploadedImage);

    // Desired radius and center
    $radius = 220; // Radius of the circular area
    $centerX = 230; // X-coordinate of the center
    $centerY = 570; // Y-coordinate of the center

    // Calculate the diameter of the circle
    $diameter = $radius * 2;

    // Resize the uploaded image to fit within the circle's diameter
    $resizedImage = imagecreatetruecolor($diameter, $diameter);
    imagealphablending($resizedImage, true);
    imagesavealpha($resizedImage, true);
    $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
    imagefill($resizedImage, 0, 0, $transparent);

    imagecopyresampled(
        $resizedImage,
        $uploadedImage,
        0, 0, 0, 0,
        $diameter, $diameter,
        $uploadedImageWidth, $uploadedImageHeight
    );

    // Create a circular mask
    $mask = imagecreatetruecolor($diameter, $diameter);
    $maskColor = imagecolorallocate($mask, 0, 0, 0);
    $transparentMask = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $transparentMask);
    imagefilledellipse($mask, $radius, $radius, $diameter, $diameter, $maskColor);

    // Apply the mask to the resized image
    for ($x = 0; $x < $diameter; $x++) {
        for ($y = 0; $y < $diameter; $y++) {
            if (imagecolorat($mask, $x, $y) !== $maskColor) {
                imagesetpixel($resizedImage, $x, $y, $transparent);
            }
        }
    }

    // Position the circular image on the poster
    $x = $centerX - $radius;
    $y = $centerY - $radius;

    // Merge the circular image onto the poster
    imagecopy($posterImage, $resizedImage, $x, $y, 0, 0, $diameter, $diameter);

    // Define text color and font properties
    $textColor = imagecolorallocate($posterImage, 255, 255, 255); // White color
    $textColorAmount = imagecolorallocate($posterImage, 128, 0, 0); // Maroon color
    $font = realpath('../assets/fonts/Futura-Bold.ttf'); // Get the absolute path dynamically
    $fontSize = 38;
    $fontSizeAmount = 28;

    // Add account number to the poster at the specified coordinates
    imagettftext($posterImage, $fontSize, 0, 730, 940, $textColor, $font, $accountNo);
    // Add the amount to the poster at the specified coordinates (676, 674)
    // Format the amount as a string
$formattedAmount = number_format($amount);

// Get the bounding box of the amount text
$bbox = imagettfbbox($fontSizeAmount, 0, $font, $formattedAmount);
$textWidth = $bbox[2] - $bbox[0]; // Width of the text

// Set the minimum and maximum x-coordinates for the amount text
$minX = 666;
$maxX = 787;

// Check if the text width exceeds the maximum allowable width
if ($textWidth > ($maxX - $minX)) {
    // If the text is too wide, set x to $minX (it will be cut off if too wide)
    $xCoordinate = $minX;
} else {
    // Otherwise, center the text within the allowed range
    $xCoordinate = $minX + ($maxX - $minX - $textWidth) / 2;
}

// Add the amount to the poster at the calculated x-coordinate
imagettftext($posterImage, $fontSizeAmount, 0, $xCoordinate, 674, $textColorAmount, $font, $formattedAmount);

    // Define the path to save the merged image
    $mergedImagePath = '../uploads/' . $user_id . '.png';

    // Save the merged image as a new file, overwriting the existing file
    imagepng($posterImage, $mergedImagePath);

    // Free up memory
    imagedestroy($posterImage);
    imagedestroy($uploadedImage);
    imagedestroy($resizedImage);
    imagedestroy($mask);

    // Construct the new image link
    $newImageLink = '../uploads/' . $user_id . '.png';

    // Update the database with the new image URL
    $stmt->free_result();

    // Prepare the UPDATE query
    $query1 = "UPDATE makueni SET images = ? WHERE member_id = ?";
    $stmt1 = $mysqli->prepare($query1);

    if ($stmt1 === false) {
        echo "Error preparing UPDATE query: " . $mysqli->error;
        exit();
    }

    // Bind parameters and execute the UPDATE query
    $stmt1->bind_param("si", $newImageLink, $user_id);
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
        $stmt->close();
        $stmt1->close();
    } else {
        // Handle invalid request method or missing image data
        echo "Error: Invalid request or missing image data.";
    }
} else {
    // Handle the case where user_id is not set in session
    echo 'Error: User ID not set in session.';
}
?>
