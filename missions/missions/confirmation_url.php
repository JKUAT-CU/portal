<?php
// Include the database connection file
include 'db.php';

// Parse the incoming JSON data
$mpesaResponse = json_decode(file_get_contents('php://input'), true);

try {
    // Check if the database connection is established
    if (!$db) {
        throw new Exception("Database connection failed");
    }

    // Check if BillRefNumber starts with "SM24"
    if (substr($mpesaResponse['BillRefNumber'], 0, 4) === "SM24") {
        // Insert data into the missions table
        $stmt = $db->prepare("INSERT INTO missions (TransID, TransTime, TransAmount, BusinessShortCode, BillRefNumber) VALUES (?, ?, ?, ?, ?)");
    } else {
        // Insert data into the finance table
        $stmt = $db->prepare("INSERT INTO finance (TransID, TransTime, TransAmount, BusinessShortCode, BillRefNumber) VALUES (?, ?, ?, ?, ?)");
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("sssss", $mpesaResponse['TransID'], $mpesaResponse['TransTime'], $mpesaResponse['TransAmount'], $mpesaResponse['BusinessShortCode'], $mpesaResponse['BillRefNumber']);
    $stmt->execute();

    echo "Data inserted successfully";

    // Close statement
    $stmt->close();
} catch (Exception $e) {
    // Log the error
    error_log("Error: " . $e->getMessage());

    // Display a friendly error message to the user
    echo "An error occurred. Please try again later.";
}

// Close database connection
$db->close();
?>
