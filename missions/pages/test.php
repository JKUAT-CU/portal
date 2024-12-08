<?php
// Include the database connection file
include "db.php";

// Fetch all rows from the `finance` table
$query = "SELECT `id` FROM `finance`"; // Assuming there's a unique `id` column
$result = $db->query($query); // Use $db instead of $mysqli

if ($result->num_rows > 0) {
    // Generate a set of IDs to update with MM128
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }

    shuffle($ids); // Randomize the array of IDs
    $updateMM128 = array_slice($ids, 0, 100); // Select 100 IDs for MM128

    // Case variations for MM
    $cases = ['mm', 'Mm', 'mM', 'MM'];

    // Update each row
    foreach ($ids as $id) {
        if (in_array($id, $updateMM128)) {
            // Assign MM128 with random case
            $randomCase = $cases[array_rand($cases)];
            $newValue = $randomCase . "128";
        } else {
            // Assign a random MMx value
            $randomCase = $cases[array_rand($cases)];
            $randomNumber = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT); // Generate random 3-digit number
            $newValue = $randomCase . $randomNumber;
        }

        // Update the row in the database
        $updateQuery = $db->prepare("UPDATE `finance` SET `BillRefNumber` = ? WHERE `id` = ?");
        $updateQuery->bind_param("si", $newValue, $id);
        $updateQuery->execute();
    }

    echo "BillRefNumber column updated successfully.";
} else {
    echo "No rows found in the `finance` table.";
}

// Close the database connection
$db->close();
?>
