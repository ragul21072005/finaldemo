<?php
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle clear history
if (isset($_GET['clear'])) {
    $delete = $conn->prepare("DELETE FROM user_history WHERE user_id = ?");
    $delete->bind_param("i", $user_id);
    $delete->execute();
    $delete->close();
    header('Location: history.php');
    exit();
}

// Get user history
$query = "SELECT h.*, e.* FROM user_history h 
          JOIN historical_events e ON h.event_id = e.id 
          WHERE h.user_id = ? 
          ORDER BY h.viewed_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result();

// Get user info for sidebar
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Group by date
$grouped_history = [];
while ($row = $history->fetch_assoc()) {
    $date = date('Y-m-d', strtotime($row['viewed_at']));
    if (!isset($grouped_history[$date])) {
        $grouped_history[$date] = [];
    }
    $grouped_history[$date][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View History | Indian History Timeline</title>
    
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
        
        .history-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .date-group {
            margin-bottom: 30px;
        }
        
        .date-header {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .history-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 10px;
            border-left: 4px solid var(--secondary);
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .history-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .history-time {
            color: #6c757d;
            font-size: 14px;
            min-width: 100px;
        }
        
        .history-title {
            flex-grow: 1;
            margin: 0 20px;
        }
        
        .history-title h6 {
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .history-title small {
            color: #6c757d;
        }
        
        .btn-view {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
        }
        
        .btn-clear {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .empty-icon {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .stat-badge {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
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
            <a href="history.php" class="active"><i class="fas fa-history"></i> View History</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            
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
                <h5 class="mb-0"><i class="fas fa-history text-primary me-2"></i>My View History</h5>
                <div>
                    <span class="me-3 text-muted"><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?></span>
                    <span class="stat-badge"><i class="fas fa-eye me-2"></i><?php echo $history->num_rows; ?> Views</span>
                </div>
            </div>
        </nav>

        <div class="history-container">
            <div class="section-title">
                <span><i class="fas fa-history me-2"></i>Recently Viewed Events</span>
                <?php if(!empty($grouped_history)): ?>
                <a href="?clear=1" class="btn-clear" onclick="return confirm('Clear all history?')">
                    <i class="fas fa-trash-alt me-2"></i>Clear History
                </a>
                <?php endif; ?>
            </div>
            
            <?php if(!empty($grouped_history)): ?>
                <?php foreach($grouped_history as $date => $items): ?>
                <div class="date-group">
                    <div class="date-header">
                        <i class="fas fa-calendar me-2"></i>
                        <?php echo date('l, F j, Y', strtotime($date)); ?>
                        <span class="badge bg-light text-dark ms-2"><?php echo count($items); ?> events</span>
                    </div>
                    
                    <?php foreach($items as $item): ?>
                    <div class="history-item">
                        <div class="history-time">
                            <i class="far fa-clock me-1"></i>
                            <?php echo date('h:i A', strtotime($item['viewed_at'])); ?>
                        </div>
                        
                        <div class="history-title">
                            <h6><?php echo htmlspecialchars($item['title']); ?></h6>
                            <small>
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($item['location']); ?>
                                <span class="badge bg-info ms-2"><?php echo $item['category']; ?></span>
                            </small>
                        </div>
                        
                        <a href="../event-details.php?id=<?php echo $item['event_id']; ?>" class="btn-view">
                            <i class="fas fa-eye me-2"></i>View Again
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h4 class="mb-3">No View History</h4>
                    <p class="text-muted mb-4">You haven't viewed any events yet. Start exploring!</p>
                    <a href="../timeline.php" class="btn btn-primary">
                        <i class="fas fa-stream me-2"></i>Browse Timeline
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>