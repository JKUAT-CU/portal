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
                            <input type="hidden" name="token" value="<?= $_GET['token'] ?>" />
                            <div class="mb-4 pb-2">
                                <div class="form-outline">
                                    <input type="password" id="new_password" name="new_password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="new_password">New Password</label>
                                </div>
                            </div>
                            <div class="mb-4 pb-2">
                                <div class="form-outline">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg" required />
                                    <label class="form-label" for="confirm_password">Confirm Password</label>
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
</body>
</html>
