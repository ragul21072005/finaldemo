<?php
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Get user statistics
$favorites_count = 0;
$history_count = 0;

$fav_query = $conn->prepare("SELECT COUNT(*) as count FROM user_favorites WHERE user_id = ?");
$fav_query->bind_param("i", $user_id);
$fav_query->execute();
$fav_result = $fav_query->get_result();
if ($fav_result) {
    $favorites_count = $fav_result->fetch_assoc()['count'];
}

$hist_query = $conn->prepare("SELECT COUNT(*) as count FROM user_history WHERE user_id = ?");
$hist_query->bind_param("i", $user_id);
$hist_query->execute();
$hist_result = $hist_query->get_result();
if ($hist_result) {
    $history_count = $hist_result->fetch_assoc()['count'];
}

// Get recent events
$events_query = $conn->query("SELECT * FROM historical_events ORDER BY year DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --light: #f8f9fa;
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
            box-shadow: 3px 0 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 280px;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-header i {
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .user-profile {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-pic {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: var(--secondary);
            border: 3px solid var(--secondary);
        }
        
        .sidebar-menu {
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            height: calc(100vh - 280px);
        }
        
        .sidebar-menu a {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 25px;
            display: block;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--secondary);
        }
        
        .sidebar-menu a.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--secondary);
            font-weight: 500;
        }
        
        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        .logout-section {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 0;
        }
        
        .logout-link {
            color: #ff9999 !important;
        }
        
        .logout-link:hover {
            color: #ff4444 !important;
            background: rgba(255, 0, 0, 0.1) !important;
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
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(26, 35, 126, 0.3);
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
            border-top: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .event-year {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-outline-custom {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-outline-custom:hover {
            background: var(--primary);
            color: white;
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary);
            display: inline-block;
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
            
            .sidebar-menu {
                height: auto;
            }
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
            <p class="mb-0 small mt-2">
                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
            </p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="profile.php">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="search.php">
                <i class="fas fa-search"></i> Search Events
            </a>
            <a href="favorites.php">
                <i class="fas fa-heart"></i> Favorites
                <?php if($favorites_count > 0): ?>
                    <span class="badge bg-warning text-dark float-end"><?php echo $favorites_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="history.php">
                <i class="fas fa-history"></i> View History
                <?php if($history_count > 0): ?>
                    <span class="badge bg-info float-end"><?php echo $history_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
            
            <!-- Logout Section - Fixed and Visible -->
            <div class="logout-section">
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>
                        User Dashboard
                    </h5>
                    <div>
                        <span class="me-3 text-muted">
                            <i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?>
                        </span>
                        <a href="../index.php" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                        <a href="logout.php" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>! 👋</h2>
                    <p class="mb-0">Continue your journey through Indian history. Explore new events and track your learning progress.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="search.php" class="btn btn-light">
                        <i class="fas fa-search me-2"></i>Search Events
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-number"><?php echo $history_count; ?></div>
                    <p class="text-muted mb-0">Events Viewed</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-number"><?php echo $favorites_count; ?></div>
                    <p class="text-muted mb-0">Favorites</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <?php
                    // Calculate account age in days
                    $created = new DateTime($user['created_at']);
                    $now = new DateTime();
                    $days_active = $created->diff($now)->days;
                    ?>
                    <div class="stat-number"><?php echo $days_active; ?></div>
                    <p class="text-muted mb-0">Days Active</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <?php
                    // Calculate achievements based on activity
                    $achievements = 0;
                    if ($history_count >= 5) $achievements++;
                    if ($favorites_count >= 3) $achievements++;
                    if ($days_active >= 7) $achievements++;
                    ?>
                    <div class="stat-number"><?php echo $achievements; ?></div>
                    <p class="text-muted mb-0">Achievements</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Events -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="section-title">
                        <i class="fas fa-calendar-alt me-2"></i>Recent Historical Events
                    </h4>
                    <a href="../timeline.php" class="btn btn-outline-custom">
                        <i class="fas fa-stream me-2"></i>View All Events
                    </a>
                </div>
                
                <div class="row">
                    <?php if($events_query && $events_query->num_rows > 0): ?>
                        <?php while($event = $events_query->fetch_assoc()): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="event-card">
                                <div class="p-4">
                                    <span class="event-year"><?php echo $event['year']; ?> CE</span>
                                    <h6 class="mb-2 fw-bold"><?php echo htmlspecialchars($event['title']); ?></h6>
                                    <p class="small text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($event['location']); ?>
                                    </p>
                                    <p class="mb-3 small text-secondary">
                                        <?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-info"><?php echo $event['category']; ?></span>
                                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-custom btn-sm">
                                            View Details <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center p-5">
                                <i class="fas fa-info-circle fa-3x mb-3"></i>
                                <h5>No Events Available</h5>
                                <p>Check back later for new historical events!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Row -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="stat-card text-start p-4">
                    <h5 class="mb-3"><i class="fas fa-search me-2" style="color: var(--primary);"></i>Quick Search</h5>
                    <form action="search.php" method="GET" class="row g-3">
                        <div class="col-md-8">
                            <input type="number" name="year" class="form-control" 
                                   placeholder="Enter Year (e.g., 1947)" min="1500" max="2024" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-custom w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <small class="text-muted">
                            Try these years: 
                            <a href="search.php?year=1857" class="text-decoration-none">1857</a>, 
                            <a href="search.php?year=1947" class="text-decoration-none">1947</a>, 
                            <a href="search.php?year=1971" class="text-decoration-none">1971</a>
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="stat-card text-start p-4">
                    <h5 class="mb-3"><i class="fas fa-link me-2" style="color: var(--primary);"></i>Quick Links</h5>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="../timeline.php" class="btn btn-outline-custom w-100">
                                <i class="fas fa-stream me-2"></i>Timeline
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="favorites.php" class="btn btn-outline-custom w-100">
                                <i class="fas fa-heart me-2"></i>Favorites
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="profile.php" class="btn btn-outline-custom w-100">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="settings.php" class="btn btn-outline-custom w-100">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Highlight active page in sidebar
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            const href = link.getAttribute('href');
            if (href === currentPage) {
                link.classList.add('active');
            }
        });
        
        // Add smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
        
        // Confirm logout
        document.querySelectorAll('.logout-link, .btn-outline-danger').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href') === 'logout.php') {
                    if (!confirm('Are you sure you want to logout?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>
</html>