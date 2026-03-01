<?php
include '../config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($gender) || empty($phone) || empty($address) || empty($username) || empty($password)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if username or email exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (name, email, gender, phone, address, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $gender, $phone, $address, $username, $password);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                $_POST = array(); // Clear form
            } else {
                $error = 'Registration failed: ' . $conn->error;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            max-width: 800px;
            margin: 40px auto;
            backdrop-filter: blur(10px);
        }
        
        .registration-left {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 50px 40px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .registration-right {
            padding: 50px 40px;
        }
        
        .registration-left h2 {
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .registration-left ul {
            list-style: none;
            padding: 0;
        }
        
        .registration-left li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .registration-left i {
            color: #ffab00;
            margin-right: 10px;
            font-size: 18px;
        }
        
        .form-title {
            color: #1a237e;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .gender-options {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }
        
        .gender-option {
            flex: 1;
            text-align: center;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .gender-option:hover {
            border-color: #1a237e;
        }
        
        .gender-option.active {
            border-color: #1a237e;
            background: rgba(26, 35, 126, 0.05);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            border: none;
            padding: 14px;
            font-weight: 600;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
        }
        
        @media (max-width: 768px) {
            .registration-left {
                padding: 30px;
            }
            
            .registration-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <div class="row g-0">
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="registration-left">
                        <h2>Create Your Account</h2>
                        <p>Join our community of history enthusiasts and start exploring India's rich heritage today.</p>
                        
                        <ul class="mt-4">
                            <li><i class="fas fa-check-circle"></i> Access interactive timeline</li>
                            <li><i class="fas fa-check-circle"></i> Save favorite events</li>
                            <li><i class="fas fa-check-circle"></i> Track learning progress</li>
                            <li><i class="fas fa-check-circle"></i> Get personalized recommendations</li>
                            <li><i class="fas fa-check-circle"></i> Join discussions</li>
                        </ul>
                        
                        <div class="mt-5">
                            <p>Already have an account? <a href="login.php" class="text-warning">Login here</a></p>
                            <p><a href="../index.php" class="text-warning"><i class="fas fa-arrow-left me-2"></i>Back to Homepage</a></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="registration-right">
                        <h4 class="form-title">User Registration</h4>
                        
                        <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Gender *</label>
                                <div class="gender-options">
                                    <label class="gender-option <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'active' : ''; ?>">
                                        <input type="radio" name="gender" value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'checked' : ''; ?> required>
                                        <i class="fas fa-male fa-2x my-2"></i><br>
                                        Male
                                    </label>
                                    <label class="gender-option <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'active' : ''; ?>">
                                        <input type="radio" name="gender" value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'checked' : ''; ?>>
                                        <i class="fas fa-female fa-2x my-2"></i><br>
                                        Female
                                    </label>
                                    <label class="gender-option <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'active' : ''; ?>">
                                        <input type="radio" name="gender" value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'checked' : ''; ?>>
                                        <i class="fas fa-user fa-2x my-2"></i><br>
                                        Other
                                    </label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" name="username" class="form-control" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address *</label>
                                <textarea name="address" class="form-control" rows="2" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" class="form-control" required>
                                    <small class="text-muted">Min. 6 characters</small>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p>Already registered? <a href="login.php" class="text-decoration-none">Sign in here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Gender selection styling
        document.querySelectorAll('.gender-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.gender-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
        
        // Password match validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirm = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                document.querySelector('input[name="confirm_password"]').focus();
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                document.querySelector('input[name="password"]').focus();
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>