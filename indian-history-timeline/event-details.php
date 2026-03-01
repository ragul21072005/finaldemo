<?php
include 'config/database.php';

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch event details
$event = null;
if ($event_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM historical_events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $event = $result->fetch_assoc();
    }
    $stmt->close();
}

// If no event found, redirect to timeline
if (!$event) {
    header('Location: timeline.php');
    exit();
}

// Track view history if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check if already viewed
    $check = $conn->prepare("SELECT id FROM user_history WHERE user_id = ? AND event_id = ?");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows === 0) {
        // Insert new view
        $insert = $conn->prepare("INSERT INTO user_history (user_id, event_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $event_id);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// Check if event is favorited
$is_favorite = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $fav_check = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND event_id = ?");
    $fav_check->bind_param("ii", $user_id, $event_id);
    $fav_check->execute();
    $fav_check->store_result();
    $is_favorite = ($fav_check->num_rows > 0);
    $fav_check->close();
}

// Handle favorite toggle
$message = '';
$message_type = '';

if (isset($_GET['action']) && isset($_SESSION['user_id'])) {
    $action = $_GET['action'];
    $user_id = $_SESSION['user_id'];
    
    if ($action === 'add_favorite') {
        // Check if already favorited
        $check = $conn->prepare("SELECT id FROM user_favorites WHERE user_id = ? AND event_id = ?");
        $check->bind_param("ii", $user_id, $event_id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO user_favorites (user_id, event_id) VALUES (?, ?)");
            $insert->bind_param("ii", $user_id, $event_id);
            if ($insert->execute()) {
                $message = "Event added to favorites!";
                $message_type = "success";
                $is_favorite = true;
            }
            $insert->close();
        }
        $check->close();
    }
    
    elseif ($action === 'remove_favorite') {
        $delete = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND event_id = ?");
        $delete->bind_param("ii", $user_id, $event_id);
        if ($delete->execute()) {
            $message = "Event removed from favorites!";
            $message_type = "info";
            $is_favorite = false;
        }
        $delete->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Inter', sans-serif;
            padding-top: 70px;
        }
        
        /* ===== UPDATED: Navigation Bar ===== */
        .navbar-custom {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
        }
        
        .navbar-brand i {
            color: #ffab00;
            margin-right: 10px;
        }
        
        .user-welcome {
            color: white;
            margin-right: 20px;
        }
        
        .user-welcome i {
            color: #ffab00;
            margin-right: 5px;
        }
        
        .nav-link-custom {
            color: rgba(255,255,255,0.9) !important;
            padding: 8px 20px !important;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .nav-link-custom:hover {
            background: rgba(255,255,255,0.1);
            color: #ffab00 !important;
        }
        
        .event-container {
            max-width: 900px;
            margin: 30px auto 50px;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .event-year {
            background: #1a237e;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .event-title {
            color: #1a237e;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        /* ===== UPDATED: Button Styles ===== */
        .btn-dashboard {
            background: #ffab00;
            color: #1a237e;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-dashboard:hover {
            background: #ffc107;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 171, 0, 0.3);
            color: #1a237e;
        }
        
        .btn-back {
            background: #1a237e;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #283593;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-favorite {
            background: #ff4444;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-favorite:hover {
            background: #cc0000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
            color: white;
        }
        
        .btn-favorite.inactive {
            background: #6c757d;
        }
        
        .btn-favorite i {
            margin-right: 8px;
        }
        
        .event-image {
            max-height: 400px;
            width: auto;
            border: 5px solid #1a237e;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .image-container {
            text-align: center;
            margin: 20px 0;
        }
        
        .alert-custom {
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        /* ===== NEW: User Menu Dropdown ===== */
        .user-dropdown {
            background: rgba(255,255,255,0.1);
            border-radius: 25px;
            padding: 5px 15px;
        }
        
        .user-dropdown .dropdown-toggle {
            color: white;
            text-decoration: none;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            color: #ffab00;
        }
        
        .user-dropdown .dropdown-menu {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .user-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 10px;
        }
        
        .user-dropdown .dropdown-item:hover {
            background: #f8f9fa;
            color: #1a237e;
        }
        
        /* ===== NEW: Quick Stats Badge ===== */
        .stats-badge {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 5px 15px;
            color: white;
            margin-left: 15px;
        }
        
        .stats-badge i {
            color: #ffab00;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- ===== UPDATED: Navigation with User Dashboard Link ===== -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-history"></i> HistoryTimeline
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="timeline.php">
                            <i class="fas fa-stream"></i> Timeline
                        </a>
                    </li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- User is logged in - Show Dashboard Link -->
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="user/dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        
                        <!-- User Dropdown Menu -->
                        <li class="nav-item dropdown user-dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> 
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="user/profile.php">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="user/favorites.php">
                                        <i class="fas fa-heart text-danger"></i> Favorites
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="user/history.php">
                                        <i class="fas fa-history"></i> View History
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="user/logout.php">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Quick Stats -->
                        <span class="stats-badge">
                            <i class="fas fa-heart"></i> 
                            <?php
                            // Get favorite count
                            $fav_count = $conn->prepare("SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?");
                            $fav_count->bind_param("i", $_SESSION['user_id']);
                            $fav_count->execute();
                            echo $fav_count->get_result()->fetch_assoc()['count'];
                            ?>
                        </span>
                        
                    <?php else: ?>
                        <!-- User not logged in - Show Login/Register -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle nav-link-custom" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> Account
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="user/login.php">
                                        <i class="fas fa-sign-in-alt"></i> User Login
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="user/register.php">
                                        <i class="fas fa-user-plus"></i> Register
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="admin/login.php">
                                        <i class="fas fa-user-shield"></i> Admin Login
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="event-container">
            <!-- Display Messages -->
            <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-custom alert-dismissible fade show">
                <i class="fas <?php echo ($message_type == 'success') ? 'fa-check-circle' : 'fa-info-circle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <span class="event-year"><?php echo $event['year']; ?> CE</span>
            <h1 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h1>
            
            <!-- Image Display Section -->
            <?php if(!empty($event['image_path'])): ?>
            <div class="image-container">
                <img src="<?php echo $event['image_path']; ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>" 
                     class="event-image">
            </div>
            <?php else: ?>
            <div class="image-container">
                <div style="background: #f0f0f0; padding: 40px; border-radius: 10px;">
                    <i class="fas fa-image fa-3x text-muted"></i>
                    <p class="text-muted mt-2">No image available for this event</p>
                </div>
            </div>
            <?php endif; ?>
            
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
            
            <div style="margin: 20px 0;">
                <h4>Description:</h4>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <?php if(!empty($event['significance'])): ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <h4><i class="fas fa-star"></i> Historical Significance</h4>
                <p><?php echo nl2br(htmlspecialchars($event['significance'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if($event['category']): ?>
            <p><strong>Category:</strong> <span style="background: #ffab00; padding: 5px 15px; border-radius: 15px;"><?php echo $event['category']; ?></span></p>
            <?php endif; ?>
            
            <!-- ===== UPDATED: Action Buttons with Dashboard Link ===== -->
            <div class="action-buttons">
                <a href="timeline.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Timeline
                </a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Show Dashboard Button for Logged-in Users -->
                    <a href="user/dashboard.php" class="btn-dashboard">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                    
                    <!-- Favorite Button -->
                    <?php if($is_favorite): ?>
                        <a href="?id=<?php echo $event_id; ?>&action=remove_favorite" 
                           class="btn-favorite"
                           onclick="return confirm('Remove from favorites?')">
                            <i class="fas fa-heart"></i> Remove from Favorites
                        </a>
                    <?php else: ?>
                        <a href="?id=<?php echo $event_id; ?>&action=add_favorite" 
                           class="btn-favorite inactive">
                            <i class="far fa-heart"></i> Add to Favorites
                        </a>
                    <?php endif; ?>
                    
                    <span class="text-muted ms-2">
                        <i class="fas fa-check-circle text-success"></i> 
                        View recorded in history
                    </span>
                <?php else: ?>
                    <a href="user/login.php" class="btn-favorite inactive" style="background: #6c757d;">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Save & Add to Favorites
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Share Buttons -->
            <div class="mt-4 pt-3 border-top">
                <small class="text-muted me-3">Share this event:</small>
                <a href="#" class="text-decoration-none me-2" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(window.location.href), '_blank')">
                    <i class="fab fa-facebook fa-lg text-primary"></i>
                </a>
                <a href="#" class="text-decoration-none me-2" onclick="window.open('https://twitter.com/intent/tweet?text=<?php echo urlencode($event['title']); ?>&url='+encodeURIComponent(window.location.href), '_blank')">
                    <i class="fab fa-twitter fa-lg text-info"></i>
                </a>
                <a href="#" class="text-decoration-none me-2" onclick="window.open('https://wa.me/?text=<?php echo urlencode($event['title'] . ' - ' . $event['location'] . ' ' . $event['year'] . ' ' . window.location.href); ?>', '_blank')">
                    <i class="fab fa-whatsapp fa-lg text-success"></i>
                </a>
            </div>
            
            <!-- ===== NEW: Quick Links for Logged-in Users ===== -->
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <div class="row">
                    <div class="col-md-4">
                        <a href="user/favorites.php" class="text-decoration-none">
                            <i class="fas fa-heart text-danger me-2"></i>My Favorites
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="user/history.php" class="text-decoration-none">
                            <i class="fas fa-history text-primary me-2"></i>My History
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="user/search.php" class="text-decoration-none">
                            <i class="fas fa-search text-success me-2"></i>Search Events
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto dismiss alerts after 3 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 3000);
    </script>
</body>
</html>