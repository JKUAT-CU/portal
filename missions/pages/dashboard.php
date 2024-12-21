<?php
// Enable error reporting (uncomment during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the fixed API key
define('API_KEY', '1d99e5708647f2a85298e64126d481a75654e69a2fd26a577d2ab0942a5240a8');

// Include the database connection file
include "../../backend/db.php"; // Ensure this file has the correct $db variable
include "../session.php";
$user_id = $_SESSION['user_id'];

// Fetch account number for the logged-in user securely
$userQuery = $mysqli->prepare("SELECT account_number FROM makueni WHERE member_id = ?");
$userQuery->bind_param("s", $user_id);  // "s" means the parameter is a string
$userQuery->execute();

// Bind result variables
$userQuery->bind_result($accountNumber);

// Check if a result is returned
if (!$userQuery->fetch()) {
    echo "No account numbers found for this user.";
    exit();
}

// Default mission cost
$missionCost = 1700;

// Define email groups
$emailList = ["chegeperpetuah38@gmail.com", "test1@test.com", "samuelkitanga20@gmail.com", "nyamgeroesther@gmail.com"];
$emailHlubi = ["hlubiolombo7@gmail.com", "mutindar617@gmail.com"];

// Determine mission cost based on user email
if (isset($_SESSION['email'])) {
    if (in_array($_SESSION['email'], $emailList)) {
        $missionCost = 10000;
    } elseif (in_array($_SESSION['email'], $emailHlubi)) {
        $missionCost = 100000;
    }
}

$apiUrl = "https://portal.jkuatcu.org/missions/pages/api.php?accountNumber=" . urlencode($accountNumber);

// Create a stream context with the API key in the header
$options = [
    "http" => [
        "header" => "X-API-KEY: " . API_KEY
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo json_encode(['error' => 'Error fetching API data.']);
    exit();
}

$data = json_decode($response, true);

// Set total amount raised to 0 if no data exists
$totalAmount = 0;
if (!empty($data)) {
    // Loop through the API data to calculate the total amount raised
    foreach ($data as $entry) {
        // Normalize both account numbers for comparison (case and spacing)
        $normalizedAccountNumber = strtoupper(preg_replace('/\s+/', '', $accountNumber));
        $normalizedBillRefNumber = strtoupper(preg_replace('/\s+/', '', $entry['BillRefNumber']));

        if ($normalizedBillRefNumber === $normalizedAccountNumber) {
            $totalAmount += $entry['TransAmount'];
        }
    }
}
?>
