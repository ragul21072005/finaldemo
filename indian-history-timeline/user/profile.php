<?php
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $gender = $_POST['gender'];
        
        // Validation
        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            $error = 'All fields are required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else {
            // Check if email exists for other users
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $user_id);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                $update = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=?, gender=? WHERE id=?");
                $update->bind_param("sssssi", $name, $email, $phone, $address, $gender, $user_id);
                
                if ($update->execute()) {
                    $success = 'Profile updated successfully!';
                    // Refresh user data
                    $user_query->execute();
                    $user = $user_query->get_result()->fetch_assoc();
                } else {
                    $error = 'Failed to update profile: ' . $conn->error;
                }
                $update->close();
            }
            $check->close();
        }
    }
    
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $verify = $conn->prepare("SELECT id FROM users WHERE id = ? AND password = ?");
        $verify->bind_param("is", $user_id, $current_password);
        $verify->execute();
        $verify->store_result();
        
        if ($verify->num_rows === 0) {
            $error = 'Current password is incorrect';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } else {
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_password, $user_id);
            
            if ($update->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password: ' . $conn->error;
            }
            $update->close();
        }
        $verify->close();
    }
}

// Get user statistics
$fav_count = $conn->prepare("SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?");
$fav_count->bind_param("i", $user_id);
$fav_count->execute();
$favorites_count = $fav_count->get_result()->fetch_assoc()['count'];

$hist_count = $conn->prepare("SELECT COUNT(*) as count FROM user_history WHERE user_id = ?");
$hist_count->bind_param("i", $user_id);
$hist_count->execute();
$history_count = $hist_count->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #283593 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 280px;
            box-shadow: 3px 0 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-profile {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-pic {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            color: var(--secondary);
            border: 3px solid var(--secondary);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            display: block;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--secondary);
        }
        
        .sidebar-menu i {
            width: 25px;
            margin-right: 10px;
        }
        
        .logout-section {
            margin-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }
        
        .logout-link {
            color: #ff9999 !important;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
        }
        
        .top-navbar {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            font-size: 24px;
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .stat-badge {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
        }
        
        .nav-tabs {
            border-bottom: 3px solid #e0e0e0;
            margin-bottom: 25px;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 25px;
            border-radius: 10px 10px 0 0;
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary);
            background: rgba(26, 35, 126, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }
        
        .gender-option {
            display: inline-block;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-history"></i> HistoryTimeline</h4>
        </div>
        
        <div class="user-profile">
            <div class="profile-pic">
                <?php
                $gender_icon = ($user['gender'] == 'Female') ? 'fa-female' : 'fa-male';
                echo "<i class='fas $gender_icon'></i>";
                ?>
            </div>
            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
            <p class="mb-0 small">@<?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
            <a href="search.php"><i class="fas fa-search"></i> Search Events</a>
            <a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a>
            <a href="history.php"><i class="fas fa-history"></i> View History</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            
            <div class="logout-section">
                <a href="logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user text-primary me-2"></i>My Profile</h5>
                <div>
                    <span class="me-3 text-muted"><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?></span>
                    <span class="stat-badge"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
            </div>
        </nav>

        <div class="profile-container">
            <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="profile-card">
                <!-- Profile Tabs -->
                <ul class="nav nav-tabs" id="profileTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal">
                            <i class="fas fa-user me-2"></i>Personal Info
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">
                            <i class="fas fa-lock me-2"></i>Security
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#stats">
                            <i class="fas fa-chart-bar me-2"></i>Statistics
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Personal Info Tab -->
                    <div class="tab-pane fade show active" id="personal">
                        <h5 class="section-title"><i class="fas fa-edit"></i>Edit Personal Information</h5>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" readonly disabled>
                                    <small class="text-muted">Username cannot be changed</small>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Gender</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" value="Male" 
                                               <?php echo ($user['gender'] == 'Male') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" value="Female" 
                                               <?php echo ($user['gender'] == 'Female') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" value="Other" 
                                               <?php echo ($user['gender'] == 'Other') ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Other</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="update_profile" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <button type="reset" class="btn btn-cancel ms-2">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security">
                        <h5 class="section-title"><i class="fas fa-key"></i>Change Password</h5>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                Use a strong password with letters, numbers, and special characters for better security.
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="change_password" class="btn btn-save">
                                    <i class="fas fa-sync-alt me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Statistics Tab -->
                    <div class="tab-pane fade" id="stats">
                        <h5 class="section-title"><i class="fas fa-chart-pie"></i>Account Statistics</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-box text-center">
                                    <i class="fas fa-calendar-check fa-3x text-primary mb-3"></i>
                                    <h3><?php echo $history_count; ?></h3>
                                    <p class="mb-0">Events Viewed</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box text-center">
                                    <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                                    <h3><?php echo $favorites_count; ?></h3>
                                    <p class="mb-0">Favorite Events</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box text-center">
                                    <i class="fas fa-clock fa-3x text-success mb-3"></i>
                                    <h3><?php 
                                        $created = new DateTime($user['created_at']);
                                        $now = new DateTime();
                                        echo $created->diff($now)->days;
                                    ?></h3>
                                    <p class="mb-0">Days Active</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-box text-center">
                                    <i class="fas fa-trophy fa-3x text-warning mb-3"></i>
                                    <h3><?php 
                                        $achievements = 0;
                                        if ($history_count >= 10) $achievements++;
                                        if ($favorites_count >= 5) $achievements++;
                                        if ($created->diff($now)->days >= 30) $achievements++;
                                        echo $achievements;
                                    ?></h3>
                                    <p class="mb-0">Achievements</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-box mt-3">
                            <h6>Account Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Member Since:</td>
                                    <td><strong><?php echo date('F j, Y', strtotime($user['created_at'])); ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Last Updated:</td>
                                    <td><strong><?php echo date('F j, Y', strtotime($user['updated_at'] ?? 'now')); ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password match validation
        document.querySelector('form[action=""]').addEventListener('submit', function(e) {
            const newPass = document.querySelector('input[name="new_password"]');
            const confirmPass = document.querySelector('input[name="confirm_password"]');
            
            if (newPass && confirmPass && newPass.value !== confirmPass.value) {
                e.preventDefault();
                alert('New passwords do not match!');
                confirmPass.focus();
            }
        });
    </script>
</body>
</html>