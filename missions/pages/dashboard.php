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
        if ($entry['BillRefNumber'] === $accountNumber) {
            $totalAmount += $entry['TransAmount'];
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- FontAwesome -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <style>
    .card-text {
      font-weight: bold;
    }
    .alert-success {
      font-size: 1.2rem;
    }
    .btn-custom {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
    }
  </style>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card">
      <div class="card-body">
        <!-- Success Message -->
        <div id="success-message"></div>

        <!-- Mission Details -->
        <h5 class="card-title text-primary">Mission Details</h5>
        <p class="card-text text-muted">Date: <strong>17th May - 25th May 2025</strong></p>
        <p class="card-text">Mission Cost: <span class="badge badge-primary" id="mission-cost"></span></p>
        <p class="card-text">Paybill Number: <strong>921961</strong></p>
        <p class="card-text">Account Number: <strong id="account-number"></strong></p>
        <p class="card-text text-success">Amount Raised: <span id="total-amount">KES 0</span></p>
        <p class="card-text text-danger">Balance: <span id="balance">KES 0</span></p>
        
        <!-- Add Proforma Button -->
        <a href="proforma.php" class="btn btn-primary btn-custom">Get Proforma</a>
      </div>
    </div>
  </div>

  <script>
    // Default fallback values for PHP variables
    const accountNumber = '<?php echo $accountNumber ?? "Unknown"; ?>';
    const missionCost = <?php echo $missionCost ?? 0; ?>;
    const totalAmount = <?php echo $totalAmount ?? 0; ?>;

    // Calculate the balance
    const balance = missionCost - totalAmount;

    // Update the UI
    document.getElementById('account-number').innerText = accountNumber === "SM24395" ? "Fundraiser" : accountNumber;
    document.getElementById('mission-cost').innerText = `KES ${missionCost.toLocaleString()}`;
    document.getElementById('total-amount').innerText = `KES ${totalAmount.toLocaleString()}`;
    document.getElementById('balance').innerText = `KES ${balance.toLocaleString()}`;

    // Display success message if balance is zero or less
    if (balance <= 0) {
      document.getElementById('success-message').innerHTML = `
        <div class="alert alert-success" role="alert">
          Congratulations 🎉🎉🎉! You have completed payment.
        </div>`;
    }
  </script>
</body>
</html>
