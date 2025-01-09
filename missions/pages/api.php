<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// // Define the fixed API key
// define('API_KEY', '1d99e5708647f2a85298e64126d481a75654e69a2fd26a577d2ab0942a5240a8');

// // Check for the API key in the headers
// $headers = apache_request_headers();
// $clientApiKey = $headers['X-API-KEY'] ?? null; // Fetch the key from the "X-API-KEY" header

// Validate the API key
// if ($clientApiKey !== API_KEY) {
//     http_response_code(403); // Forbidden
//     echo json_encode(['error' => 'Invalid or missing API key']);
//     exit();
// }

// Check if the accountNumber parameter is provided
if (!isset($_GET['accountNumber']) || empty(trim($_GET['accountNumber']))) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing or invalid account number']);
    exit();
}

$accountNumber = $_GET['accountNumber']; // Sanitize the input

// Include the database connection file
include "db.php"; // Ensure this file has the correct $db variable

// SQL query to fetch data for the specific account number
$query = "SELECT TRIM(`BillRefNumber`) AS `BillRefNumber`, `TransAmount`, `TransTime`, `BusinessShortCode`, `TransID`
          FROM `finance`
          WHERE UPPER(TRIM(`BillRefNumber`)) = ?";

// Prepare the statement using $db
$stmt = $db->prepare($query);

$data = []; // Initialize an array to hold the JSON data

if ($stmt) {
    // Bind the parameter to the query
    $stmt->bind_param("s", $accountNumber);

    // Execute the query
    $stmt->execute();

    // Bind the result columns
    $stmt->bind_result($billRefNumber, $transAmount, $transTime, $businessShortCode, $transID);

    // Fetch the results and process each row
    while ($stmt->fetch()) {
        // Add the fetched data to the JSON array
        $data[] = [
            'BillRefNumber' => $billRefNumber,
            'TransAmount' => $transAmount,
            'TransTime' => $transTime,
            'BusinessShortCode' => $businessShortCode,
            'TransID' => $transID,
        ];
    }

    // Close the statement
    $stmt->close();
}

// Output the JSON data
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);

// Close the database connection
$db->close(); // Close the connection using $db
?>
