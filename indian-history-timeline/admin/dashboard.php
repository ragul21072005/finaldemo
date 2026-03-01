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

// Get recent users
$recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");

// Get recent events
$recent_events = $conn->query("SELECT * FROM historical_events ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --danger: #d32f2f;
            --success: #2e7d32;
            --info: #0277bd;
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
            text-align: center;
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
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-top: 4px solid var(--primary);
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        
        .table-title {
            color: var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 3px solid var(--secondary);
            display: inline-block;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, var(--danger) 0%, #b71c1c 100%);
        }
        
        .btn-warning-custom {
            background: linear-gradient(135deg, var(--secondary) 0%, #ff8f00 100%);
            color: #212529;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .quick-action-btn {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-5px);
            color: white;
            text-decoration: none;
        }
        
        .qa-users {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
        }
        
        .qa-events {
            background: linear-gradient(135deg, var(--success) 0%, #1b5e20 100%);
        }
        
        .qa-add {
            background: linear-gradient(135deg, var(--info) 0%, #01579b 100%);
        }
        
        .qa-reports {
            background: linear-gradient(135deg, #6a1b9a 0%, #4a148c 100%);
        }
        
        /* New style for event thumbnails */
        .event-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid var(--primary);
        }
        
        .no-image-badge {
            background: #f0f0f0;
            color: #666;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 11px;
        }
        
        /* Action buttons for events */
        .action-btn {
            padding: 3px 8px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 12px;
            margin: 0 2px;
        }
        
        .action-btn:hover {
            color: white;
            opacity: 0.8;
        }
        
        .btn-view {
            background: var(--info);
        }
        
        .btn-edit {
            background: var(--secondary);
            color: #212529;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">HistoryTimeline</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="manage_users.php">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="manage_events.php">
                <i class="fas fa-calendar-alt"></i> Manage Events
            </a>
            <a href="add_event.php">
                <i class="fas fa-plus-circle"></i> Add Event
            </a>
            <a href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="settings.php">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="logout.php" class="text-danger mt-5">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar-top">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle text-primary me-2"></i>
                        Welcome, <?php echo $_SESSION['admin_username']; ?>!
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted">
                            <i class="fas fa-calendar-day me-2"></i><?php echo date('F j, Y'); ?>
                        </span>
                        <a href="logout.php" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="stat-number"><?php echo $total_users; ?></h2>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h2 class="stat-number"><?php echo $total_events; ?></h2>
                    <p class="text-muted mb-0">Total Events</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2 class="stat-number">0</h2>
                    <p class="text-muted mb-0">Today's Visitors</p>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card text-center">
                    <div class="stat-icon text-info">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h2 class="stat-number">4</h2>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="mb-4">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="quick-actions">
                <a href="manage_users.php" class="quick-action-btn qa-users">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h6>Manage Users</h6>
                </a>
                <a href="manage_events.php" class="quick-action-btn qa-events">
                    <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                    <h6>Manage Events</h6>
                </a>
                <a href="add_event.php" class="quick-action-btn qa-add">
                    <i class="fas fa-plus-circle fa-2x mb-3"></i>
                    <h6>Add New Event</h6>
                </a>
                <a href="reports.php" class="quick-action-btn qa-reports">
                    <i class="fas fa-chart-bar fa-2x mb-3"></i>
                    <h6>View Reports</h6>
                </a>
            </div>
        </div>
        
        <!-- Recent Users Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="table-title"><i class="fas fa-users me-2"></i>Recent Users</h5>
                <a href="manage_users.php" class="btn btn-custom btn-sm">
                    <i class="fas fa-eye me-2"></i>View All Users
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $recent_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo htmlspecialchars($user['username']); ?></span>
                            </td>
                            <td>
                                <?php 
                                $gender_color = ($user['gender'] == 'Male') ? 'primary' : 
                                              (($user['gender'] == 'Female') ? 'danger' : 'secondary');
                                ?>
                                <span class="badge bg-<?php echo $gender_color; ?>">
                                    <?php echo $user['gender']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Events Table - MODIFIED WITH IMAGE COLUMN -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="table-title"><i class="fas fa-calendar-alt me-2"></i>Recent Events</h5>
                <a href="manage_events.php" class="btn btn-custom btn-sm">
                    <i class="fas fa-eye me-2"></i>View All Events
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="eventsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>  <!-- NEW COLUMN -->
                            <th>Event Name</th>
                            <th>Year</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Actions</th> <!-- NEW COLUMN -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($event = $recent_events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $event['id']; ?></td>
                            
                            <!-- NEW IMAGE COLUMN -->
                            <td>
                                <?php if(!empty($event['image_path'])): ?>
                                    <img src="../<?php echo $event['image_path']; ?>" 
                                         alt="<?php echo htmlspecialchars($event['title']); ?>" 
                                         class="event-thumb"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span class="no-image-badge" style="display:none;">Invalid</span>
                                <?php else: ?>
                                    <span class="no-image-badge">No Image</span>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $event['year']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($event['location']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($event['date'])); ?></td>
                            <td>
                                <?php
                                $category_colors = [
                                    'Ancient' => 'bg-secondary',
                                    'Medieval' => 'bg-warning text-dark',
                                    'Empire' => 'bg-info text-dark',
                                    'Independence' => 'bg-success',
                                    'Modern' => 'bg-primary'
                                ];
                                $color = isset($category_colors[$event['category']]) ? $category_colors[$event['category']] : 'bg-dark';
                                ?>
                                <span class="badge <?php echo $color; ?>">
                                    <?php echo $event['category'] ?? 'Uncategorized'; ?>
                                </span>
                            </td>
                            
                            <!-- NEW ACTIONS COLUMN -->
                            <td>
                                <a href="../event-details.php?id=<?php echo $event['id']; ?>" 
                                   target="_blank" 
                                   class="action-btn btn-view" 
                                   title="View Event">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                                   class="action-btn btn-edit" 
                                   title="Edit Event">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#usersTable').DataTable({
                "pageLength": 5,
                "order": [[0, 'desc']]
            });
            
            $('#eventsTable').DataTable({
                "pageLength": 5,
                "order": [[0, 'desc']],
                "columnDefs": [
                    { "orderable": false, "targets": [1, 7] } // Disable sorting on image and action columns
                ]
            });
        });

        // Add tooltips
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function(e) {
                const title = this.getAttribute('title');
                if(title) {
                    // Simple tooltip can be added here or use Bootstrap tooltips
                }
            });
        });
    </script>
</body>
</html>