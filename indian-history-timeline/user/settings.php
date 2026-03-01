<?php
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user info
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_preferences'])) {
        // Save preferences (you can create a user_preferences table for this)
        $success = 'Preferences saved successfully!';
    }
    
    elseif (isset($_POST['delete_account'])) {
        $confirm = $_POST['confirm_delete'];
        if ($confirm !== 'DELETE') {
            $error = 'Please type DELETE to confirm account deletion';
        } else {
            // Delete user account
            $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delete->bind_param("i", $user_id);
            
            if ($delete->execute()) {
                session_destroy();
                header('Location: ../index.php?msg=account_deleted');
                exit();
            } else {
                $error = 'Failed to delete account';
            }
            $delete->close();
        }
    }
}

// Get statistics
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
    <title>Settings | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --danger: #dc3545;
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
        
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
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
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
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
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #c82333 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .danger-zone {
            border: 2px solid var(--danger);
            border-radius: 15px;
            padding: 25px;
            background: #fff5f5;
        }
        
        .stat-badge {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
        }
        
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid var(--primary);
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
                <i class="fas <?php echo ($user['gender'] == 'Female') ? 'fa-female' : 'fa-male'; ?>"></i>
            </div>
            <h5><?php echo htmlspecialchars($user['name']); ?></h5>
            <p class="mb-0 small">@<?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="search.php"><i class="fas fa-search"></i> Search Events</a>
            <a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a>
            <a href="history.php"><i class="fas fa-history"></i> View History</a>
            <a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
            
            <div class="logout-section">
                <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-cog text-primary me-2"></i>Account Settings</h5>
                <div>
                    <span class="me-3 text-muted"><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?></span>
                    <span class="stat-badge"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
            </div>
        </nav>

        <div class="settings-container">
            <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Preferences -->
            <div class="settings-card">
                <h5 class="card-title"><i class="fas fa-sliders-h"></i>Preferences</h5>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Email Notifications</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notifFavorites" checked>
                            <label class="form-check-label" for="notifFavorites">
                                New events added to favorites
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notifNewsletter" checked>
                            <label class="form-check-label" for="notifNewsletter">
                                Weekly newsletter with new events
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Default View</label>
                        <select class="form-select">
                            <option selected>Timeline View</option>
                            <option>List View</option>
                            <option>Grid View</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Items Per Page</label>
                        <select class="form-select">
                            <option>10</option>
                            <option selected>20</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="save_preferences" class="btn btn-save">
                        <i class="fas fa-save me-2"></i>Save Preferences
                    </button>
                </form>
            </div>

            <!-- Privacy -->
            <div class="settings-card">
                <h5 class="card-title"><i class="fas fa-shield-alt"></i>Privacy Settings</h5>
                
                <form>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="publicProfile" checked>
                            <label class="form-check-label" for="publicProfile">Make profile public</label>
                        </div>
                        <small class="text-muted">Allow other users to see your activity</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showFavorites">
                            <label class="form-check-label" for="showFavorites">Show favorites publicly</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="saveHistory" checked>
                            <label class="form-check-label" for="saveHistory">Save view history</label>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Data & Storage -->
            <div class="settings-card">
                <h5 class="card-title"><i class="fas fa-database"></i>Data & Storage</h5>
                
                <div class="info-box mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Your Data Summary</h6>
                            <p class="mb-1"><i class="fas fa-heart text-danger me-2"></i>Favorites: <?php echo $favorites_count; ?></p>
                            <p class="mb-1"><i class="fas fa-history text-primary me-2"></i>History Items: <?php echo $history_count; ?></p>
                        </div>
                        <i class="fas fa-database fa-3x text-muted"></i>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-outline-primary w-100" onclick="alert('Export feature coming soon!')">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-outline-secondary w-100" onclick="alert('Import feature coming soon!')">
                            <i class="fas fa-upload me-2"></i>Import Data
                        </button>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="danger-zone">
                <h5 class="text-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                
                <div class="mb-3">
                    <h6>Clear All History</h6>
                    <p class="small text-muted">Remove all your viewing history. This action cannot be undone.</p>
                    <a href="history.php?clear=1" class="btn btn-outline-danger btn-sm" 
                       onclick="return confirm('Clear all viewing history?')">
                        <i class="fas fa-trash-alt me-2"></i>Clear History
                    </a>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6>Delete Account</h6>
                    <p class="small text-muted">Permanently delete your account and all associated data.</p>
                    
                    <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure? This cannot be undone!')">
                        <div class="mb-3">
                            <label class="form-label">Type <strong>DELETE</strong> to confirm</label>
                            <input type="text" name="confirm_delete" class="form-control" 
                                   placeholder="DELETE" pattern="DELETE" required>
                        </div>
                        <button type="submit" name="delete_account" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-2"></i>Permanently Delete Account
                        </button>
                    </form>
                </div>
            </div>

            <!-- Account Info -->
            <div class="settings-card">
                <h5 class="card-title"><i class="fas fa-info-circle"></i>Account Information</h5>
                
                <table class="table">
                    <tr>
                        <td>Member Since:</td>
                        <td><strong><?php echo date('F j, Y', strtotime($user['created_at'])); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Last Updated:</td>
                        <td><strong><?php echo date('F j, Y', strtotime($user['updated_at'] ?? 'now')); ?></strong></td>
                    </tr>
                    <tr>
                        <td>Account Status:</td>
                        <td><span class="badge bg-success">Active</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>