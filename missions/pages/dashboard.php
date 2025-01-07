<?php
// Enable error reporting (for debugging during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// API Key constant
define('API_KEY', '1d99e5708647f2a85298e64126d481a75654e69a2fd26a577d2ab0942a5240a8');

// Include database connection
include "../../backend/db.php"; // Ensure this file initializes the $mysqli object
include "../session.php";

// Fetch logged-in user ID
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

// Handle POST request for updating the amount
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['newAmount']) || !is_numeric($_POST['newAmount'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
        exit();
    }

    $newAmount = floatval($_POST['newAmount']);

    // Validate the new amount (must be greater than 1700)
    if ($newAmount <= 1700) {
        echo json_encode(['status' => 'error', 'message' => 'Amount must be greater than 1700.']);
        exit();
    }

    try {
        // Prepare the SQL query to update the amount
        $updateQuery = $mysqli->prepare("UPDATE makueni SET amount = ? WHERE member_id = ?");
        
        if ($updateQuery === false) {
            echo json_encode(['status' => 'error', 'message' => 'Error preparing the update query.']);
            error_log('Error preparing query: ' . $mysqli->error);
            exit();
        }

        $updateQuery->bind_param("ds", $newAmount, $user_id);

        if ($updateQuery->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Amount updated successfully.',
            ]);
            // Ensure the JSON response is sent before redirecting
            flush();
            // Redirect to dashboard.php
            header("Location: dashboard.php");
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error executing the update query.']);
            error_log('Error executing query: ' . $updateQuery->error);
            exit();
        }
        
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while updating the amount.']);
    }
    exit();
}

// Handle GET request for fetching user and mission details
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userQuery = $mysqli->prepare("SELECT account_number, amount FROM makueni WHERE member_id = ?");
    
    if ($userQuery === false) {
        echo json_encode(['status' => 'error', 'message' => 'Error preparing the select query.']);
        error_log('Error preparing query: ' . $mysqli->error);
        exit();
    }

    $userQuery->bind_param("s", $user_id);
    $userQuery->execute();
    $userQuery->bind_result($accountNumber, $collectionAmount);

    if (!$userQuery->fetch()) {
        die("No account details found for this user.");
    }

    // Default mission cost
    $missionCost = isset($collectionAmount) ? $collectionAmount : 1700;

    // Fetch contributions data from API
    $apiUrl = "https://portal.jkuatcu.org/missions/pages/api.php?accountNumber=" . urlencode($accountNumber);
    $options = [
        "http" => [
            "header" => "X-API-KEY: " . API_KEY
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($apiUrl, false, $context);

    $totalAmount = 0; // Default total amount raised
    if ($response !== false) {
        $data = json_decode($response, true);

        if (!empty($data)) {
            foreach ($data as $entry) {
                $normalizedAccountNumber = strtoupper(preg_replace('/\s+/', '', $accountNumber));
                $normalizedBillRefNumber = strtoupper(preg_replace('/\s+/', '', $entry['BillRefNumber']));

                if ($normalizedBillRefNumber === $normalizedAccountNumber) {
                    $totalAmount += $entry['TransAmount'];
                }
            }
        }
    } else {
        die("Error fetching API data.");
    }

    // Render the HTML page
    include "template/dashboard.php"; // Move the HTML to a separate file for clarity
    exit();
}

// Fallback for unsupported methods
echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
exit();
