<?php
session_start();
include './backend/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Prevent SQL Injection
    $email = $mysqli->real_escape_string($email);

    // Check if the email exists
    $sql = "SELECT * FROM cu_members WHERE email = '$email' LIMIT 1";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Store the token in the password_resets table
        $sql = "INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$token', '$expiry')";
        $mysqli->query($sql);

        // Send reset email (Assuming mail configuration is set)
        $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
        mail($email, "Password Reset", "Click the following link to reset your password: $reset_link");

        $_SESSION['toast_message'] = "Password reset link has been sent to your email.";
    } else {
        $_SESSION['toast_message'] = "No user found with that email.";
    }

    header("Location: forgot_password.php");
    exit();
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <!-- Include styles and scripts as in the sign-in page -->
</head>
<body>
    <section class="h-100 h-custom gradient-custom-2 d-flex-center">
        <div class="container py-5 h-100 d-flex-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-indigo text-white" style="border-radius: 15px;">
                    <div class="card-body p-5 text-center">
                        <h3 class="fw-bold mb-5">Forgot Password</h3>

                        <!-- Status Alerts -->
                        <?php if (isset($_SESSION['toast_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $_SESSION['toast_message']; unset($_SESSION['toast_message']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="forgotten.php" method="POST" id="forgotPasswordForm">
                            <div class="mb-4 pb-2">
                                <div class="form-outline form-white">
                                    <input type="email" id="email" name="email" class="form-control form-control-lg" required />
                                    <label class="form-label" for="email">Your Email</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom btn-lg btn-block">Send Reset Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
