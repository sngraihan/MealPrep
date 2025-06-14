<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: views/dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealPrep - Healthy Meals Delivered Daily</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-utensils fa-3x text-primary mb-3"></i>
                                <h1 class="h3 mb-3 font-weight-normal">Welcome to MealPrep</h1>
                                <p class="text-muted">Healthy meals delivered daily to your doorstep</p>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="login.php" class="btn btn-primary w-100 py-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="register.php" class="btn btn-outline-primary w-100 py-3">
                                        <i class="fas fa-user-plus me-2"></i>Register
                                    </a>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="row text-center">
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-leaf fa-2x text-success mb-2"></i>
                                    <h6>Fresh & Healthy</h6>
                                    <small class="text-muted">Nutritious meals prepared daily</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-truck fa-2x text-info mb-2"></i>
                                    <h6>Fast Delivery</h6>
                                    <small class="text-muted">Quick delivery to your location</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                    <h6>Quality Service</h6>
                                    <small class="text-muted">Excellent customer satisfaction</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
