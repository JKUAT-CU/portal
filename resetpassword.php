<?php
require 'backend/db.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $token = $_POST['token'] ?? '';

    // Validate password and confirm password
    if (empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = "Password fields cannot be empty";
        header("Location: reset.php?token=$token");
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: reset.php?token=$token");
        exit();
    }

    // Check if token exists and is valid
    $checkTokenQuery = "SELECT email, used, TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS minutes_passed 
                        FROM password_reset WHERE token = ?";
    $stmtCheckToken = $mysqli->prepare($checkTokenQuery);

    if (!$stmtCheckToken) {
        $_SESSION['error'] = "Error preparing token check statement: " . $mysqli->error;
        header("Location: reset.php?token=$token");
        exit();
    }

    $stmtCheckToken->bind_param("s", $token);
    $stmtCheckToken->execute();
    $stmtCheckToken->store_result();

    if ($stmtCheckToken->num_rows === 0) {
        $_SESSION['error'] = "Invalid or expired token";
        header("Location: reset.php?token=$token");
        exit();
    }

    $stmtCheckToken->bind_result($email, $used, $minutesPassed);
    $stmtCheckToken->fetch();

    // Validate token usage and expiration
    if ($used || $minutesPassed > 60) { // Token is marked as used or expired
        $_SESSION['error'] = "Token has expired or already been used";
        header("Location: reset.php?token=$token");
        exit();
    }
    $stmtCheckToken->close();

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update the user's password
    $updatePasswordQuery = "UPDATE user SET password = ? WHERE email = ?";
    $stmtUpdatePassword = $mysqli->prepare($updatePasswordQuery);

    if (!$stmtUpdatePassword) {
        $_SESSION['error'] = "Error preparing password update statement: " . $mysqli->error;
        header("Location: reset.php?token=$token");
        exit();
    }

    $stmtUpdatePassword->bind_param("ss", $hashedPassword, $email);
    $stmtUpdatePassword->execute();

    if ($stmtUpdatePassword->affected_rows === 1) {
        // Mark the token as used
        $markTokenUsedQuery = "UPDATE password_reset SET used = 1 WHERE token = ?";
        $stmtMarkTokenUsed = $mysqli->prepare($markTokenUsedQuery);

        if ($stmtMarkTokenUsed) {
            $stmtMarkTokenUsed->bind_param("s", $token);
            $stmtMarkTokenUsed->execute();
            $stmtMarkTokenUsed->close();
        }

        // Success
        $_SESSION['success'] = "Password reset successful. You can now log in with your new password.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update password";
        header("Location: reset.php?token=$token");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request method";
    header("Location: reset.php");
    exit();
}

$mysqli->close();
?>
