<head>
    <!-- Custom fonts for this template -->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <div id="wrapper">
        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Account No</th>
                                    <th>Phone Number</th>
                                    <th>Evangelistic Team</th>
                                    <th>Amount Paid</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                        <?php
                        include 'db.php';
                        
                        // Query to retrieve data from the user table with the sum of TransAmount for each account_no
                        $userSql = "SELECT u.name, u.account_no, u.mobile_no, u.evangelistic_team, u.email, 
                                    SUM(m.TransAmount) AS totalAmountPaid
                                    FROM user u
                                    LEFT JOIN missions m ON u.account_no = m.BillRefNumber
                                    GROUP BY u.account_no";
                        
                        $result = $conn->query($userSql);
                        
                        if ($result) {
                            // Output data of each user
                            while ($row = $result->fetch_assoc()) {
                                // Initialize variables
                                $totalAmountPaid = $row["totalAmountPaid"];
                                $userEmail = $row["email"]; // Fixed missing semicolon
                                
                                // Calculate balance based on mission cost
                                $emailList = array("chegeperpetuah38@gmail.com", "allansaboke@gmail.com","nyamgeroesther@gmail.com","nkaranja484@gmail.com", "josehgichuhi2021@gmail.com","jairuskilimo2000@gmail.com","samuelkitanga20@gmail.com", "mutanurehema@gmail.com", "muthiniboniface782@gmail.com", "daisyedna687@gmail.com", "nditumurima@gmail.com", "Charityjebet75@gmail.com", "wendyseda86@gmail.com", "linus12601kiprop@gmail.com", "onesmusjoseph044@gmail.com", "masterpiecewale2000jw@gmail.com", "mutindar617@gmail.com", "jesseolomayiana@gmail.com");
                                $emailHlubi = array("hlubiolombo7@gmail.com");
                                if (in_array($userEmail, $emailList)) {
                                    $missionCost = 10000;
                                }elseif (in_array($userEmail, $emailHlubi)){
                                    $missionCost = 100000;
                                } else {
                                    $missionCost = 2750;
                                }
                                $balance = $missionCost - $totalAmountPaid;
                        
                                // Output user data
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['name']) . "</td>
                                        <td>" . htmlspecialchars($row['account_no']) . "</td>
                                        <td>" . htmlspecialchars($row['mobile_no']) . "</td>
                                        <td>" . htmlspecialchars($row['evangelistic_team']) . "</td>
                                        <td>" . htmlspecialchars($totalAmountPaid) . "</td>
                                        <td>" . htmlspecialchars($balance) . "</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>Error executing query: " . $conn->error . "</td></tr>";
                        }
                        
                        
                        $conn->close();
                        ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
