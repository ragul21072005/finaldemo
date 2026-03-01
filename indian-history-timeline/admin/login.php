<?php
include '../config/database.php';
// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        // For demo, using simple check. In production, use password_verify with hashed passwords
        $stmt = $conn->prepare("SELECT id, username FROM admin WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid admin credentials';
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
    <title>Admin Login | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        
        .admin-login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            backdrop-filter: blur(10px);
        }
        
        .admin-login-header {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            color: white;
            padding: 40px;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        
        .admin-login-body {
            padding: 40px;
        }
        
        .admin-icon {
            font-size: 70px;
            color: #d32f2f;
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #d32f2f;
            box-shadow: 0 0 0 0.2rem rgba(211, 47, 47, 0.25);
        }
        
        .btn-admin-login {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            color: white;
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-admin-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);
            color: white;
        }
        
        .alert-danger {
            background: #fee;
            border: 2px solid #f5c6cb;
            border-radius: 10px;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #28a745;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-header">
            <h3><i class="fas fa-user-shield"></i> Admin Panel</h3>
            <p class="mb-0">Indian History Interactive Timeline</p>
        </div>
        
        <div class="admin-login-body">
            <div class="text-center mb-4">
                <div class="admin-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 style="color: #1a237e;">Administrator Login</h4>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-user text-muted"></i>
                        </span>
                        <input type="text" name="username" class="form-control" 
                               placeholder="Enter admin username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-key text-muted"></i>
                        </span>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Enter admin password" required>
                    </div>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-admin-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
                    </button>
                </div>
                
                <div class="demo-credentials">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Demo Credentials:</strong><br>
                        Username: <code>admin</code><br>
                        Password: <code>admin123</code>
                    </small>
                </div>
                
                <div class="back-link">
                    <a href="../index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Homepage
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Focus on username field
        document.querySelector('input[name="username"]').focus();
    </script>
</body>
</html>