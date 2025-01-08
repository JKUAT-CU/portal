<?php
// Include database connection
include 'backend/db.php'; // Ensure this file contains the correct database connection code

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch counties, courses, DETs, and ministries for dropdowns
$counties = $mysqli->query("SELECT id, name FROM counties");
$courses = $mysqli->query("SELECT id, name FROM cu_courses");
$dets = $mysqli->query("SELECT id, name FROM cu_dets");
$ministries = $mysqli->query("SELECT id, name FROM cu_ministries");

// Fetching years for dropdown
$years = range(1, 6);

// Initialize log array
$log = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and initialize variables
    $registration_number = $_POST['registration_number'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $county_id = $_POST['county_id'] ?? '';
    $course_id = $_POST['course_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile_number = $_POST['phone'] ?? '';
    $year_of_study = $_POST['year_of_study'] ?? '';
    $ministry_id = $_POST['ministry_id'] ?? '';
    $det_id = $_POST['det_id'] ?? '';
    $password = $_POST['password'] ?? ''; // Raw password
    $password_confirmation = $_POST['confirm_password'] ?? ''; // Confirm password
    $avatar = 'default.png'; // Default avatar
    $role = 'member'; // Default role
    

    $response = [
        'valid' => true,
    ];

    // // Log the form data
    // $log[] = "Form submitted with the following data:";
    // $log[] = "Registration Number: $registration_number";
    // $log[] = "First Name: $first_name";
    // $log[] = "Surname: $surname";
    // $log[] = "Gender: $gender";
    // $log[] = "County ID: $county_id";
    // $log[] = "Course ID: $course_id";
    // $log[] = "Email: $email";
    // $log[] = "Mobile Number: $mobile_number";
    // $log[] = "Year of Study: $year_of_study";
    // $log[] = "Ministry ID: $ministry_id";
    // $log[] = "DET ID: $det_id";
    // $log[] = "Password: $password";
    // $log[] = "Password Confirmation: $password_confirmation";

    // Check if email already exists
    $emailCheckSql = "SELECT id FROM cu_members WHERE email = ? LIMIT 1";
    $emailCheckStmt = $mysqli->prepare($emailCheckSql);
    $emailCheckStmt->bind_param("s", $email);
    $emailCheckStmt->execute();
    $emailCheckStmt->bind_result($emailResult);
    $emailExists = $emailCheckStmt->fetch();
    $emailCheckStmt->close();

    if ($emailExists) {
        $response['valid'] = false;
        $response['message'] = "The email address is already registered.";
    }

    // Check if registration number already exists
    $regNumberCheckSql = "SELECT id FROM cu_members WHERE registration_number = ? LIMIT 1";
    $regNumberCheckStmt = $mysqli->prepare($regNumberCheckSql);
    $regNumberCheckStmt->bind_param("s", $registration_number);
    $regNumberCheckStmt->execute();
    $regNumberCheckStmt->bind_result($regNumberResult);
    $regNumberExists = $regNumberCheckStmt->fetch();
    $regNumberCheckStmt->close();

    if ($regNumberExists) {
        $response['valid'] = false;
        $response['message'] .= " Also, the registration number is already registered.";
    }


    // // Check password confirmation
    // if ($password !== $password_confirmation) {
    //     $response['valid'] = false;
    //     $response['message'] = "Passwords do not match.";
    //     $log[] = $response['message'];
    // }

    // // Check password strength
    // if (strlen($password) < 8) {
    //     $response['valid'] = false;
    //     $response['message'] = "Password must be at least 8 characters long.";
    //     $log[] = $response['message'];
    // }

    // If no errors, proceed with registration
    if ($response['valid']) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing password
        // Prepare and bind the insert statement
        $stmt = $mysqli->prepare("INSERT INTO cu_members (registration_number, first_name, surname, gender, county_id, course_id, email, mobile_number, year_of_study, password, avatar, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiissssss", $registration_number, $first_name, $surname, $gender, $county_id, $course_id, $email, $mobile_number, $year_of_study, $hashed_password, $avatar, $role, );

        // Execute the statement and check for success
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful! Please sign in.";
            $_SESSION['alert_type'] = "success"; // Success type
            $log[] = "Registration successful!";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['alert_type'] = "danger"; // Error type
            $log[] = "Error during registration: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // If the response is not valid, set the session message
        $_SESSION['message'] = $response['message'];
        $_SESSION['alert_type'] = "danger"; // Error type
        $log[] = "Validation failed: " . $response['message'];
    }
}

// Close the database connection
$mysqli->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>

    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- MDB CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.3.0/mdb.min.css" rel="stylesheet">

    <style>
        /* General styling */
        .container {
            background-color: #f7a306;
            height: 100%;
        }
        .gradient-custom-2 {
            background-color: #f7a306;
        }
        .bg-indigo {
            background-color: #800000;
            border-top-right-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .bg-secondary {
            background-color: #089000;
        }
        .h-custom {
            height: 100vh !important;
        }
        .form-label {
            color: #800000;
        }
        .btn-custom {
            background-color: #f7a306;
            color: white;
        }
        .dark-bg .form-control {
            background-color: #f7f7f7;
            color: #000;
            border: 1px solid #ddd;
        }
        .light-bg .form-control {
            background-color: #fff;
            color: #000;
            border: 1px solid #ccc;
        }
        /* Media Queries */
        @media (max-width: 991px) {
            .bg-indigo {
                border-bottom-left-radius: 15px;
                border-bottom-right-radius: 15px;
            }
        }
    </style>
</head>
<body>

<section class="h-100 h-custom gradient-custom-2">

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['alert_type']; ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php unset($_SESSION['message']); endif; ?>

    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12">
                <div class="card card-registration card-registration-2" style="border-radius: 15px;">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <!-- Light Background Section -->
                            <div class="col-lg-6 light-bg">
                                <div class="p-5">
                                    <p>Have an account? <a href="login.php" style="color: #f7a306;">Login here</a></p>
                                    <h3 class="fw-normal mb-5" style="color: #800000;">General Information</h3>
                                    <form method="POST" action="registration.php" onsubmit="return validateEmail();">
                                        <!-- Registration Number -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="text" id="registration_number" name="registration_number" class="form-control form-control-lg" required />
                                                <label class="form-label" for="registration_number">Registration Number</label>
                                            </div>
                                        </div>
                                        <!-- First Name -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="text" id="first_name" name="first_name" class="form-control form-control-lg" required />
                                                <label class="form-label" for="first_name">First Name</label>
                                            </div>
                                        </div>
                                        <!-- Surname -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="text" id="surname" name="surname" class="form-control form-control-lg" required />
                                                <label class="form-label" for="surname">Surname</label>
                                            </div>
                                        </div>
                                        <!-- Gender -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="gender">Gender</label>
                                            <select class="form-select form-control-lg" name="gender" required>
                                                <option value="" disabled selected>Select Gender</option>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                               
                                            </select>
                                        </div>
                                        <!-- County -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="county_id">County</label>
                                            <select class="form-select form-control-lg" name="county_id" required>
                                                <option value="" disabled selected>Select County</option>
                                                <?php while ($row = $counties->fetch_assoc()) : ?>
                                                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <!-- Course -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="course_id">Course</label>
                                            <select class="form-select form-control-lg" name="course_id" required>
                                                <option value="" disabled selected>Select Course</option>
                                                <?php while ($row = $courses->fetch_assoc()) : ?>
                                                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <!-- Year of Study -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="year_of_study">Year of Study</label>
                                            <select class="form-select form-control-lg" name="year_of_study" required>
                                                <option value="" disabled selected>Select Year</option>
                                                <?php foreach ($years as $year): ?>
                                                    <option value="<?= $year; ?>"><?= $year; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="pt-2">
                                            <button type="submit" class="btn btn-custom btn-lg">Register</button>
                                        </div>
                                   
                        <!-- Display logs -->
                        <!-- <h4>Logs:</h4>
                        <ul>
                            <?php foreach ($log as $log_message): ?>
                                <li><?= htmlspecialchars($log_message) ?></li>
                            <?php endforeach; ?>
                        </ul> -->
                                </div>
                            </div>
                            <!-- Dark Background Section -->
                            <div class="col-lg-6 bg-indigo dark-bg">
                                <div class="p-5">
                                    <h3 class="fw-normal mb-5" style="color: white;">Contact Information</h3>
                                                                            <!-- Email -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="email" id="email" name="email" class="form-control form-control-lg" required />
                                                <label class="form-label" for="email">Email Address</label>
                                            </div>
                                        </div>
                                        <!-- Phone Number -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="tel" id="phone" name="phone" class="form-control form-control-lg" required />
                                                <label class="form-label" for="phone">Phone Number</label>
                                            </div>
                                        </div>
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="password" id="password" name="password" class="form-control form-control-lg" required />
                                                <label class="form-label" for="password">Password</label>
                                            </div>
                                            <button type="button" class="btn btn-outline-light" onclick="togglePasswordVisibility()">Show Password</button>
                                        </div>
                                        <!-- Confirm Password -->
                                        <div class="mb-4 pb-2">
                                            <div class="form-outline">
                                                <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg" required />
                                                <label class="form-label" for="confirm_password">Confirm Password</label>
                                            </div>
                                        </div>
                                        <!-- Ministry -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="ministry_id">Ministry</label>
                                            <select class="form-select form-control-lg" name="ministry_id" required>
                                                <option value="" disabled selected>Select Ministry</option>
                                                <?php while ($row = $ministries->fetch_assoc()) : ?>
                                                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <!-- DET -->
                                        <div class="mb-4 pb-2">
                                            <label class="form-label" for="det_id">EVANGELISTIC TEAM</label>
                                            <select class="form-select form-control-lg" name="det_id" required>
                                                <option value="" disabled selected>Select ET</option>
                                                <?php while ($row = $dets->fetch_assoc()) : ?>
                                                    <option value="<?= htmlspecialchars($row['id']); ?>"><?= htmlspecialchars($row['name']); ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> <!-- End Row -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<!-- MDB UI Kit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.3.0/mdb.min.js"></script>

<!-- Custom JS -->
<script>
function togglePasswordVisibility() {
    var passwordField = document.getElementById("password");
    var confirmPasswordField = document.getElementById("confirm_password");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        confirmPasswordField.type = "text";
    } else {
        passwordField.type = "password";
        confirmPasswordField.type = "password";
    }
}

function validateEmail() {
    var emailField = document.getElementById('email');
    var email = emailField.value;
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address.');
        emailField.focus();
        return false;
    }
    return true;
}

function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecialChars = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (password.length < minLength) {
        alert("Password must be at least 8 characters long.");
        return false;
    }
    if (!hasUpperCase) {
        alert("Password must contain at least one uppercase letter.");
        return false;
    }
    if (!hasLowerCase) {
        alert("Password must contain at least one lowercase letter.");
        return false;
    }
    if (!hasNumbers) {
        alert("Password must contain at least one number.");
        return false;
    }
    if (!hasSpecialChars) {
        alert("Password must contain at least one special character.");
        return false;
    }

    return true;
}

function validateForm() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');

    // Validate Email first
    if (!validateEmail()) {
        return false;
    }

    // Validate Password
    const password = passwordField.value;
    if (!validatePassword(password)) {
        passwordField.focus();
        return false;
    }

    // Check if passwords match
    if (password !== confirmPasswordField.value) {
        alert("Passwords do not match.");
        confirmPasswordField.focus();
        return false;
    }

    return true; // All validations passed
}
</script>
</body>
</html>
