<?php
session_start();
require_once __DIR__ . '/models/user_model.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: views/dashboard.php');
    exit();
}

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $userModel = new UserModel();
        $user = $userModel->login($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role']; // Set role from database
            
            header('Location: views/dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MealPrep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-utensils fa-3x text-primary mb-3"></i>
                                <h1 class="h3 mb-3 font-weight-normal">Sign In</h1>
                                <p class="text-muted">Welcome back to MealPrep</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username or Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-0">Don't have an account? 
                                    <a href="register.php" class="text-primary">Sign up here</a>
                                </p>
                                <p class="mt-2">
                                    <a href="index.php" class="text-muted">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Home
                                    </a>
                                </p>
                            </div>
                            
                            <!-- Demo Credentials -->
                            <div class="mt-4">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <h6 class="card-title mb-2"><i class="fas fa-info-circle me-1"></i>Demo Credentials</h6>
                                        <small class="text-muted">
                                            <strong>Admin:</strong> admin / admin123<br>
                                            <strong>User:</strong> john_doe / password123
                                        </small>
                                    </div>
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
