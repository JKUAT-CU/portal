<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #8B4513; /* Olden Brown */
            color: #800000; /* Maroon */
        }
        .card {
            background-color: #800000; /* Maroon */
            color: #FFFFFF; /* White text */
            border-radius: 15px;
        }
        .btn-custom {
            background-color: #8B4513; /* Olden Brown */
            color: #FFFFFF; /* White text */
            border: none;
        }
        .btn-custom:hover {
            background-color: #A0522D; /* Slightly lighter shade of Olden Brown */
            color: #FFFFFF; /* White text */
        }
        .form-label {
            color: #FFFFFF; /* White for labels */
        }
        .form-control {
            background-color: #F5F5DC; /* Beige for input background */
            color: #800000; /* Maroon for input text */
        }
        .password-toggle {
            cursor: pointer;
            color: #800000; /* Maroon for the toggle icon */
        }
        .password-toggle:hover {
            color: #A0522D; /* Slightly lighter Maroon for hover */
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5 text-center">
                        <h3 class="fw-bold mb-5">Reset Password</h3>

                        <!-- Status Alerts -->
                        <?php if (isset($_SESSION['toast_message'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= $_SESSION['toast_message']; unset($_SESSION['toast_message']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="resetpassword.php" method="POST" id="resetPasswordForm">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>" />
                            <div class="mb-4 pb-2 position-relative">
                                <div class="form-outline">
                                    <input type="password" id="new_password" name="new_password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="new_password">New Password</label>
                                    <span class="password-toggle position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePasswordVisibility('new_password')">
                                        <i class="bi bi-eye" id="new_password_icon"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-4 pb-2 position-relative">
                                <div class="form-outline">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="confirm_password">Confirm Password</label>
                                    <span class="password-toggle position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePasswordVisibility('confirm_password')">
                                        <i class="bi bi-eye" id="confirm_password_icon"></i>
                                    </span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom btn-lg btn-block">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility(inputId) {
            const inputField = document.getElementById(inputId);
            const icon = document.getElementById(`${inputId}_icon`);
            if (inputField.type === "password") {
                inputField.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                inputField.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }
        }
    </script>
</body>
</html>
