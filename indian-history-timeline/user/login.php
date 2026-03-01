<?php
include '../config/database.php';
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $stmt = $conn->prepare("SELECT id, username, name, email FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // For simplicity, using plain text (In production, use password_verify)
            $check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND password = ?");
            $check->bind_param("sss", $username, $username, $password);
            $check->execute();
            
            if ($check->get_result()->num_rows === 1) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
            $check->close();
        } else {
            $error = 'User not found';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            margin: 0 auto;
            backdrop-filter: blur(10px);
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-title {
            color: #1a237e;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: #1a237e;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .links a:hover {
            color: #ffab00;
            text-decoration: underline;
        }
        
        .password-toggle {
            cursor: pointer;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-left: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <h3><i class="fas fa-history"></i> HistoryTimeline</h3>
                        <p class="mb-0">User Login Portal</p>
                    </div>
                    
                    <div class="login-body">
                        <h4 class="form-title">Sign In to Your Account</h4>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" name="username" class="form-control" placeholder="Enter username or email" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>
                        
                        <div class="links">
                            <p>Don't have an account? <a href="register.php">Create one here</a></p>
                            <p>
                                <a href="../index.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Homepage
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
        
        // Focus on username field
        document.querySelector('input[name="username"]').focus();
    </script>
</body>
</html>