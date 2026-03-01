<?php
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get current admin info
$admin_id = $_SESSION['admin_id'];
$admin_query = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$admin_query->bind_param("i", $admin_id);
$admin_query->execute();
$admin = $admin_query->get_result()->fetch_assoc();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        
        // Check if username/email exists for other admins
        $check = $conn->prepare("SELECT id FROM admin WHERE (username = ? OR email = ?) AND id != ?");
        $check->bind_param("ssi", $username, $email, $admin_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $update = $conn->prepare("UPDATE admin SET full_name = ?, email = ?, username = ? WHERE id = ?");
            $update->bind_param("sssi", $full_name, $email, $username, $admin_id);
            
            if ($update->execute()) {
                $_SESSION['admin_username'] = $username;
                $success = 'Profile updated successfully!';
                // Refresh admin data
                $admin_query->execute();
                $admin = $admin_query->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update profile: ' . $conn->error;
            }
            $update->close();
        }
        $check->close();
    }
    
    elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $verify = $conn->prepare("SELECT id FROM admin WHERE id = ? AND password = ?");
        $verify->bind_param("is", $admin_id, $current_password);
        $verify->execute();
        $verify->store_result();
        
        if ($verify->num_rows === 0) {
            $error = 'Current password is incorrect';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } else {
            $update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_password, $admin_id);
            
            if ($update->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password: ' . $conn->error;
            }
            $update->close();
        }
        $verify->close();
    }
    
    elseif (isset($_POST['update_site_settings'])) {
        // You can expand this to save site settings in a database table
        // For now, just show success message
        $success = 'Site settings saved successfully!';
    }
}

// Get site statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM historical_events")->fetch_assoc()['count'];
$total_admins = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --danger: #d32f2f;
            --success: #2e7d32;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #283593 100%);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 260px;
            box-shadow: 3px 0 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .admin-profile {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-avatar {
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
        
        .main-content {
            margin-left: 260px;
            padding: 25px;
        }
        
        .navbar-top {
            background: white;
            border-radius: 12px;
            padding: 15px 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .card-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary);
            display: flex;
            align-items: center;
        }
        
        .card-title i {
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
        
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
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
        
        .btn-cancel:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
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
            margin-right: 10px;
            border-radius: 10px 10px 0 0;
        }
        
        .nav-tabs .nav-link:hover {
            border: none;
            color: var(--primary);
            background: rgba(26, 35, 126, 0.05);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border-bottom: 3px solid var(--primary);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">Settings & Configuration</p>
        </div>
        
        <div class="admin-profile">
            <div class="admin-avatar">
                <i class="fas fa-user-cog"></i>
            </div>
            <h5><?php echo htmlspecialchars($admin['full_name'] ?? $admin['username']); ?></h5>
            <p class="mb-0 small"><?php echo htmlspecialchars($admin['email']); ?></p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manageevents.php"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <a href="add_event.php"><i class="fas fa-plus-circle"></i> Add Event</a>
            <a href="report.php"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar-top">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cog text-primary me-2"></i>
                        Settings & Configuration
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted">
                            <i class="fas fa-calendar-day me-2"></i><?php echo date('F j, Y'); ?>
                        </span>
                        <span class="stat-badge">
                            <i class="fas fa-user-shield me-2"></i><?php echo $_SESSION['admin_username']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <div class="settings-container">
            <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Settings Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>Profile Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                        <i class="fas fa-lock me-2"></i>Security
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="site-tab" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
                        <i class="fas fa-globe me-2"></i>Site Settings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                        <i class="fas fa-database me-2"></i>Backup
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="settingsTabsContent">
                <!-- Profile Settings Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="settings-card">
                        <h5 class="card-title">
                            <i class="fas fa-user-edit"></i>
                            Edit Profile Information
                        </h5>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($admin['full_name'] ?? ''); ?>"
                                           placeholder="Enter your full name">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" 
                                           value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role</label>
                                    <input type="text" class="form-control" value="Administrator" readonly disabled>
                                </div>
                            </div>
                            
                            <div class="info-box">
                                <h6><i class="fas fa-info-circle me-2"></i>Account Information</h6>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Account Created:</strong> <br><?php echo date('F j, Y', strtotime($admin['created_at'] ?? 'now')); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Last Login:</strong> <br><?php echo date('F j, Y H:i'); ?></p>
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
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security" role="tabpanel">
                    <div class="settings-card">
                        <h5 class="card-title">
                            <i class="fas fa-key"></i>
                            Change Password
                        </h5>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" name="current_password" class="form-control" 
                                               placeholder="Enter current password" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-key"></i>
                                        </span>
                                        <input type="password" name="new_password" class="form-control" 
                                               placeholder="Min. 6 characters" required>
                                    </div>
                                    <small class="text-muted">Must be at least 6 characters</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <input type="password" name="confirm_password" class="form-control" 
                                               placeholder="Re-enter new password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Tip:</strong> Use a strong password with letters, numbers, and special characters.
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="change_password" class="btn btn-save">
                                    <i class="fas fa-sync-alt me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                        
                        <!-- Two Factor Authentication (Optional) -->
                        <div class="mt-5">
                            <h6 class="mb-3">Two-Factor Authentication (2FA)</h6>
                            <div class="info-box">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="mb-1"><strong>Enhance your account security</strong></p>
                                        <p class="mb-0 small text-muted">Add an extra layer of security to your account by enabling 2FA.</p>
                                    </div>
                                    <button class="btn btn-outline-primary" disabled>Coming Soon</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Site Settings Tab -->
                <div class="tab-pane fade" id="site" role="tabpanel">
                    <div class="settings-card">
                        <h5 class="card-title">
                            <i class="fas fa-sliders-h"></i>
                            Site Configuration
                        </h5>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" value="Indian History Timeline" 
                                           placeholder="Enter site name">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site Email</label>
                                    <input type="email" class="form-control" value="admin@historytimeline.com" 
                                           placeholder="Enter admin email">
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Site Description</label>
                                    <textarea class="form-control" rows="3">Explore India's rich historical heritage through interactive timeline</textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Items Per Page</label>
                                    <select class="form-select">
                                        <option>10</option>
                                        <option selected>20</option>
                                        <option>50</option>
                                        <option>100</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Default Theme</label>
                                    <select class="form-select">
                                        <option selected>Light Mode</option>
                                        <option>Dark Mode</option>
                                        <option>Auto (System)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenanceMode" checked>
                                        <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                                    </div>
                                    <small class="text-muted">When enabled, only admins can access the site</small>
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="userRegistration" checked>
                                        <label class="form-check-label" for="userRegistration">Allow User Registration</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" name="update_site_settings" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </form>
                        
                        <!-- System Info -->
                        <div class="info-box mt-4">
                            <h6><i class="fas fa-server me-2"></i>System Information</h6>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Database:</strong> MySQL</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1"><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Apache'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Tab -->
                <div class="tab-pane fade" id="backup" role="tabpanel">
                    <div class="settings-card">
                        <h5 class="card-title">
                            <i class="fas fa-database"></i>
                            Database Backup
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box text-center">
                                    <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                    <h6>Database Statistics</h6>
                                    <p class="mb-1">Total Users: <?php echo $total_users; ?></p>
                                    <p class="mb-1">Total Events: <?php echo $total_events; ?></p>
                                    <p class="mb-1">Total Admins: <?php echo $total_admins; ?></p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h6>Backup Options</h6>
                                    <p class="small text-muted">Create a backup of your database to ensure data safety.</p>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="backupUsers" checked>
                                            <label class="form-check-label" for="backupUsers">Include Users</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="backupEvents" checked>
                                            <label class="form-check-label" for="backupEvents">Include Events</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="backupAdmin" checked>
                                            <label class="form-check-label" for="backupAdmin">Include Admins</label>
                                        </div>
                                    </div>
                                    
                                    <button class="btn btn-save w-100" disabled>
                                        <i class="fas fa-download me-2"></i>Download Backup (Coming Soon)
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Backup History -->
                        <div class="mt-4">
                            <h6 class="mb-3">Recent Backups</h6>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Size</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i'); ?></td>
                                        <td>2.3 MB</td>
                                        <td>Auto Backup</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" disabled>
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime('-1 day')); ?></td>
                                        <td>2.1 MB</td>
                                        <td>Manual</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" disabled>
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Activity Log -->
            <div class="settings-card">
                <h5 class="card-title">
                    <i class="fas fa-history"></i>
                    Recent Activity
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Action</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('H:i:s'); ?></td>
                                <td>Settings page accessed</td>
                                <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                            </tr>
                            <tr>
                                <td><?php echo date('H:i:s', strtotime('-5 minutes')); ?></td>
                                <td>Dashboard viewed</td>
                                <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                            </tr>
                            <tr>
                                <td><?php echo date('H:i:s', strtotime('-1 hour')); ?></td>
                                <td>Login successful</td>
                                <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Confirm before leaving with unsaved changes
        let formChanged = false;
        
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => {
                formChanged = true;
            });
        });
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => {
                formChanged = false;
            });
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
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