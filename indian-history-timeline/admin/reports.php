<?php
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM historical_events")->fetch_assoc()['count'];

// Get category distribution
$category_stats = $conn->query("SELECT category, COUNT(*) as count FROM historical_events GROUP BY category ORDER BY count DESC");

// Get year range
$year_range = $conn->query("SELECT MIN(year) as min_year, MAX(year) as max_year FROM historical_events")->fetch_assoc();

// Get recent activity
$recent_events = $conn->query("SELECT * FROM historical_events ORDER BY created_at DESC LIMIT 5");
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
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
        }
        
        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary);
            transition: all 0.3s;
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
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stat-label {
            color: #666;
            margin-bottom: 0;
        }
        
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .recent-table {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">Reports & Analytics</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <a href="add_event.php"><i class="fas fa-plus-circle"></i> Add Event</a>
            <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar-top">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h5>
                <div>
                    <span class="me-3"><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
        </nav>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Registered Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-number"><?php echo $total_events; ?></div>
                <div class="stat-label">Total Historical Events</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-number"><?php echo $year_range['min_year'] ?? 'N/A'; ?> - <?php echo $year_range['max_year'] ?? 'N/A'; ?></div>
                <div class="stat-label">Historical Timeline Range</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
                <div class="stat-number"><?php echo $category_stats->num_rows; ?></div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
        
        <div class="row">
            <!-- Category Distribution Chart -->
            <div class="col-md-6">
                <div class="chart-container">
                    <h5 class="section-title"><i class="fas fa-pie-chart me-2"></i>Events by Category</h5>
                    <canvas id="categoryChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
            
            <!-- Category Distribution Table -->
            <div class="col-md-6">
                <div class="recent-table">
                    <h5 class="section-title"><i class="fas fa-tags me-2"></i>Category Distribution</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Number of Events</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = $total_events;
                            if($category_stats && $category_stats->num_rows > 0):
                                while($cat = $category_stats->fetch_assoc()):
                                    $percentage = ($total > 0) ? round(($cat['count'] / $total) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><span class="badge bg-primary"><?php echo $cat['category']; ?></span></td>
                                <td><?php echo $cat['count']; ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%">
                                            <?php echo $percentage; ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="3" class="text-center">No categories found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-6">
                <div class="recent-table">
                    <h5 class="section-title"><i class="fas fa-history me-2"></i>Recent Events Added</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Year</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($event = $recent_events->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo substr(htmlspecialchars($event['title']), 0, 30); ?></td>
                                <td><?php echo $event['year']; ?></td>
                                <td><?php echo date('d M', strtotime($event['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="recent-table">
                    <h5 class="section-title"><i class="fas fa-user-plus me-2"></i>Recent User Registrations</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo date('d M', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        <?php
        // Reset pointer for chart data
        $category_stats->data_seek(0);
        $categories = [];
        $counts = [];
        while($cat = $category_stats->fetch_assoc()) {
            $categories[] = $cat['category'];
            $counts[] = $cat['count'];
        }
        ?>
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categories); ?>,
                datasets: [{
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: ['#1a237e', '#ffab00', '#d32f2f', '#2e7d32', '#0277bd', '#6a1b9a']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>