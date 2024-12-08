<?php
// Start session
session_start();
if(isset($_GET['refresh']) && $_GET['refresh'] == 1) {
    echo '<script>window.location.reload(true);</script>';
}

// Database credentials
$servername = "localhost";
$username = "jkuatcu_daraja";
$password = "K@^;daY0*j(n";
$database = "jkuatcu_daraja";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user_id is set in session
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Query to retrieve the account number from the user table using user_id
    $userSql = "SELECT account_no FROM user WHERE id = '$user_id'";
    $userResult = $conn->query($userSql);

    // Initialize total amount
    $totalAmount = 0;

    if ($userResult->num_rows > 0) {
        // Output data of each row
        while($userRow = $userResult->fetch_assoc()) {
            $accountNumber = $userRow["account_no"];
            
            // Query to sum the amounts from the missions table where BillRefNumber matches the account number
            $missionSql = "SELECT SUM(TransAmount) AS totalAmount FROM missions WHERE BillRefNumber = '$accountNumber'";
            $missionResult = $conn->query($missionSql);
            
            if ($missionResult->num_rows > 0) {
                // Output data of each row
                while($missionRow = $missionResult->fetch_assoc()) {
                    $totalAmount += $missionRow["totalAmount"];
                }
            }
        }
    } else {
        echo "No account numbers found for this user";
    }
} else {
    echo "User ID not set in session";
    header("Location: signin.php");
    exit();
}

$conn->close();
 $balance = 2750 - $totalAmount;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" >
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropme@1.4.3/dist/cropme.min.css">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-touch-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>User Dashboard</title>
  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.1.0" rel="stylesheet" />
  <!-- Nepcha Analytics (nepcha.com) -->
  <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
  <script defer data-site="missions.jkuatcu.org" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
  <style>
    body {
      background-color: white; /* Change body background to white */
    }

    .bg-gray-200,
    .bg-gradient-dark,
    .bg-gradient-primary {
      background-color: white !important; /* Change specific background classes to white */
    }
  </style>
   <style>
    /* Increase font size for card text */
    .card-text {
      font-weight:bold; /* Adjust the font size as needed */
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-200">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/material-dashboard/pages/dashboard " target="_blank">
        <img src="logo.jpeg" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold text-white">User Dashboard</span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white active bg-gradient-primary" href="../pages/dashboard.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">dashboard</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <div class="container mt-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Missions</h5>
          <p class="card-text">Date: 20th April - 29th April 2024</p>
          <p class="card-text">Mission cost: KES 2750</p>
          <p class="card-text">Paybill Number: 921961</p>
          <!-- Display the account number -->
          <p class="card-text">Account number: <?php echo $accountNumber; ?></p>
          <!-- Display the total amount -->
          <p class="card-text">Amount Raised: <?php echo $totalAmount; ?></p>
          <p class="card-text">Balance: <?php echo $balance; ?></p>
        </div>
      </div>
    </div>
  </main>
</html>
<?php
    // Database credentials
    $servername = "localhost";
    $username = "jkuatcu_daraja";
    $password = "K@^;daY0*j(n";
    $database = "jkuatcu_daraja";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if user_id is set in session
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Query to fetch image URL from the user table
        $userSql = "SELECT image FROM user WHERE id = '$user_id'";
        $result = $conn->query($userSql);

        // Check if the query was successful
        if ($result) {
            if ($result->num_rows > 0) {
                // Fetch the image path from the database
                $row = $result->fetch_assoc();
                $image_path = $row['image'];
                // Check if the image path is empty
                if (!empty($image_path)) {
                    include 'download.php';
                } else {
                    include 'script.php';
                }
            } else {
                echo "No results found for user ID: $user_id";
            }
        } else {
            // Query execution failed
            echo "Error: " . $conn->error;
        }
    } else {
        // User ID not set in session
        echo "User ID not set in session";
        // Optionally redirect the user to the signin page
        // header("Location: signin.php");
        // exit();
    }

    // Close connection
    $conn->close();
?>
