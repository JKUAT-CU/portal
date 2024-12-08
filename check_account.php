<?php
session_start();

// Database connection
$db = new mysqli('localhost', 'root', '', 'jkuatcu_data');

if ($db->connect_error) {
    echo json_encode(['hasAccount' => false, 'message' => 'Database connection failed.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasAccount' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if the user already has an account
$query = "SELECT account_number FROM makueni WHERE member_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['hasAccount' => true]);
} else {
    echo json_encode(['hasAccount' => false]);
}

$stmt->close();
$db->close();
?>
