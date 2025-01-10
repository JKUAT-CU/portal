<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Constants
define('API_KEY', '1d99e5708647f2a85298e64126d481a75654e69a2fd26a577d2ab0942a5240a8');

// Fetch headers safely
$clientApiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

// Validate API key
if ($clientApiKey !== API_KEY) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Invalid or missing API key']);
    exit();
}

// Validate accountNumber parameter
$accountNumber = isset($_GET['accountNumber']) ? strtoupper(trim($_GET['accountNumber'])) : null;
if (!$accountNumber) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing or invalid account number']);
    exit();
}

// Include database connection
include "db.php"; // Ensure this initializes the `$db` variable

// SQL query
$query = "SELECT TRIM(`BillRefNumber`) AS `BillRefNumber`, `TransAmount`, `TransTime`, `BusinessShortCode`, `TransID`
          FROM `finance`
          WHERE UPPER(TRIM(`BillRefNumber`)) = ?";

$responseData = [];

if ($stmt = $db->prepare($query)) {
    $stmt->bind_param("s", $accountNumber);

    if ($stmt->execute()) {
        $stmt->bind_result($billRefNumber, $transAmount, $transTime, $businessShortCode, $transID);

        while ($stmt->fetch()) {
            $responseData[] = [
                'BillRefNumber' => $billRefNumber,
                'TransAmount' => $transAmount,
                'TransTime' => $transTime,
                'BusinessShortCode' => $businessShortCode,
                'TransID' => $transID,
            ];
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Database query execution failed']);
        exit();
    }
    $stmt->close();
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database query preparation failed']);
    exit();
}

// Close database connection
$db->close();

// Response handling
if (empty($responseData)) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'No records found for the provided account number']);
} else {
    header('Content-Type: application/json');
    echo json_encode($responseData, JSON_PRETTY_PRINT);
}
?>
