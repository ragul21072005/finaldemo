<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fix the database include path
include 'config/database.php';

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get all events sorted by year
$events_query = "SELECT * FROM historical_events ORDER BY year DESC";
$events_result = $conn->query($events_query);

// Check if query was successful
if (!$events_result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historical Timeline | Indian History</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Inter', sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-container {
            padding: 100px 0 50px;
        }
        
        .page-title {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
            border-radius: 3px;
        }
        
        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }
        
        .timeline-item:nth-child(odd) {
            left: 0;
        }
        
        .timeline-item:nth-child(even) {
            left: 50%;
        }
        
        .timeline-content {
            padding: 20px 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            border-left: 5px solid var(--primary);
        }
        
        .timeline-year {
            position: absolute;
            top: -15px;
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 3px 10px rgba(26, 35, 126, 0.3);
        }
        
        .timeline-item:nth-child(odd) .timeline-year {
            right: -20px;
        }
        
        .timeline-item:nth-child(even) .timeline-year {
            left: -20px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        .btn-view:hover {
            background: #283593;
            color: white;
        }
        
        @media (max-width: 768px) {
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
            
            .timeline-item:nth-child(odd) .timeline-year,
            .timeline-item:nth-child(even) .timeline-year {
                left: 15px;
                right: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-history"></i> HistoryTimeline
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="timeline.php">Timeline</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> Account
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="user/login.php"><i class="fas fa-sign-in-alt"></i> User Login</a></li>
                            <li><a class="dropdown-item" href="user/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin/login.php"><i class="fas fa-user-shield"></i> Admin Login</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container timeline-container">
        <div class="row">
            <div class="col-12">
                <h1 class="page-title">Historical Timeline of India</h1>
                
                <?php if($events_result->num_rows > 0): ?>
                <div class="timeline">
                    <?php 
                    $count = 0;
                    while($event = $events_result->fetch_assoc()): 
                        $count++;
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-content">
                            <div class="timeline-year"><?php echo $event['year']; ?> CE</div>
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            <p><?php echo substr(htmlspecialchars($event['description']), 0, 150); ?>...</p>
                            <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn-view">
                                <i class="fas fa-info-circle me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="text-center mt-5">
                    <p class="text-muted">Showing <?php echo $count; ?> historical events</p>
                </div>
                
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <h4>No Events Found</h4>
                    <p>The timeline is currently empty. Please add events from admin panel.</p>
                    <a href="admin/login.php" class="btn btn-primary">Go to Admin Panel</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>