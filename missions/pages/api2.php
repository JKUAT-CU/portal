<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define the fixed API key
define('API_KEY', '1d99e5708647f2a85298e64126d481a75654e69a2fd26a577d2ab0942a5240a8');

// Check for the API key in the headers
$headers = apache_request_headers();
$clientApiKey = $headers['X-API-KEY'] ?? null; // Fetch the key from the "X-API-KEY" header

// Validate the API key
if ($clientApiKey !== API_KEY) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Invalid or missing API key']);
    exit();
}

// Database connection details
$host = 'localhost';
$user = 'jkuatcu_devs';
$password = '#God@isAble!#';
$database = "jkuatcu_data";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');

// Fetch data from makueni table
$sqlMakueni = "SELECT member_id, account_number FROM makueni";
$resultMakueni = $conn->query($sqlMakueni);

if ($resultMakueni->num_rows > 0) {
    $response = [];

    while ($makueniRow = $resultMakueni->fetch_assoc()) {
        $memberId = $makueniRow['member_id'];
        $accountNumber = $makueniRow['account_number'];

        // Convert account_number to lowercase for case-insensitive comparison
        $accountNumberLower = strtolower($accountNumber);

        // Default first_name and surname values
        $firstName = "Unknown";
        $lastName = "Unknown";

        // Check for specific account numbers and override names
        if (strtoupper($accountNumber) === "MM001") {
            $firstName = "Missions";
            $lastName = "Carwash";
        } elseif (strtoupper($accountNumber) === "MM002") {
            $firstName = "Missions";
            $lastName = "Sales";
        } elseif (strtoupper($accountNumber) === "MM003") {
            $firstName = "Missions";
            $lastName = "Fundraiser";
        } elseif (stripos($accountNumberLower, 'makueni') !== false) {
            // Handle all variations of 'makueni'
            $firstName = "Missions";
            $lastName = "Organisations";
        } elseif (stripos($accountNumberLower, 'associates') !== false) {
            // Handle all variations of 'associates'
            $firstName = "Missions";
            $lastName = "Associates";
        } else {
            // Fetch user details from cu_members table for other accounts
            $sqlUser = "SELECT first_name, surname FROM cu_members WHERE id = $memberId";
            $resultUser = $conn->query($sqlUser);

            if ($resultUser->num_rows > 0) {
                $userRow = $resultUser->fetch_assoc();
                $firstName = $userRow['first_name'];
                $lastName = $userRow['surname'];
            }
        }

        // Fetch transaction data via API endpoint
        $apiUrl = "https://portal.jkuatcu.org/missions/pages/api1.php?account_number=" . urlencode($accountNumberLower);
        $transactionData = @file_get_contents($apiUrl);

        // If file_get_contents() fails, handle error
        $totalAmount = 0;
        if ($transactionData !== FALSE) {
            $transactionArray = json_decode($transactionData, true);

            if ($transactionArray && is_array($transactionArray)) {
                // Loop through the API response and sum up TransAmount where BillRefNumber matches account_number
                foreach ($transactionArray as $transaction) {
                    if (strtolower($transaction['BillRefNumber']) === $accountNumberLower) {
                        $totalAmount += (float) $transaction['TransAmount'];
                    }
                }
            }
        }

        // Add data to response
        $response[] = [
            'member_id' => $memberId,
            'account_number' => $accountNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'total_amount' => $totalAmount
        ];
    }

    echo json_encode($response);
} else {
    echo json_encode(['message' => 'No data found in makueni table']);
}

$conn->close();
?>
