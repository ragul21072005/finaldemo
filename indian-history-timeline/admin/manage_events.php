<?php
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    
    // Get image path to delete file
    $img_query = $conn->query("SELECT image_path FROM historical_events WHERE id = $event_id");
    if ($img_query && $img_query->num_rows > 0) {
        $img = $img_query->fetch_assoc();
        if (!empty($img['image_path']) && file_exists('../' . $img['image_path'])) {
            unlink('../' . $img['image_path']); // Delete image file
        }
    }
    
    $delete_query = "DELETE FROM historical_events WHERE id = $event_id";
    if ($conn->query($delete_query)) {
        $success = "Event deleted successfully!";
    } else {
        $error = "Failed to delete event: " . $conn->error;
    }
}

// Get all events
$events_query = "SELECT * FROM historical_events ORDER BY year DESC";
$events_result = $conn->query($events_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events | Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
    
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
        
        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--secondary);
        }
        
        .btn-edit {
            background: #ffab00;
            color: #212529;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
            padding: 5px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
        }
        
        .event-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">Manage Events</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage_events.php" class="active"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <a href="add_event.php"><i class="fas fa-plus-circle"></i> Add Event</a>
            <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar-top">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Manage Events</h5>
                <div>
                    <a href="add_event.php" class="btn btn-sm btn-success me-2">
                        <i class="fas fa-plus-circle me-2"></i>Add New Event
                    </a>
                    <span class="me-3"><i class="fas fa-calendar me-2"></i><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
        </nav>
        
        <div class="content-card">
            <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover" id="eventsTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Event Title</th>
                            <th>Year</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($events_result && $events_result->num_rows > 0): ?>
                            <?php while($event = $events_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $event['id']; ?></td>
                                <td>
                                    <?php if(!empty($event['image_path'])): ?>
                                        <img src="../<?php echo $event['image_path']; ?>" 
                                             alt="Event" class="event-image">
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                <td><span class="badge bg-primary"><?php echo $event['year']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <span class="badge bg-info text-dark"><?php echo $event['category']; ?></span>
                                </td>
                                <td>
                                    <a href="../event-details.php?id=<?php echo $event['id']; ?>" target="_blank" class="btn-view btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn-edit btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $event['id']; ?>" class="btn-delete btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this event?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No events found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#eventsTable').DataTable({ "order": [[2, 'desc']] });
        });
    </script>
</body>
</html>