<?php
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$search_results = [];
$search_year = '';
$message = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['year'])) {
    $search_year = intval($_GET['year']);
    
    if ($search_year >= 1500 && $search_year <= 2024) {
        $stmt = $conn->prepare("SELECT * FROM historical_events WHERE year = ? ORDER BY date");
        $stmt->bind_param("i", $search_year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $search_results[] = $row;
            }
        } else {
            $message = "No historical events found for the year $search_year.";
        }
        $stmt->close();
    } else {
        $message = "Please enter a valid year between 1500 and 2024.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Events | Indian History Timeline</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --light: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .search-container {
            max-width: 900px;
            margin: 80px auto 40px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .search-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .search-form {
            margin-bottom: 40px;
        }
        
        .search-input {
            border-radius: 50px 0 0 50px;
            border: 3px solid var(--primary);
            padding: 15px 25px;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
            border-color: var(--primary);
        }
        
        .search-btn {
            border-radius: 0 50px 50px 0;
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border: 3px solid var(--primary);
            padding: 15px 35px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #283593 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(26, 35, 126, 0.3);
        }
        
        .result-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary);
            transition: all 0.3s;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .event-year {
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }
        
        .no-results-icon {
            font-size: 70px;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .btn-view {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26, 35, 126, 0.3);
        }
        
        .suggestion-year {
            display: inline-block;
            margin: 5px;
            padding: 8px 15px;
            background: #e3f2fd;
            color: var(--primary);
            border-radius: 20px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .suggestion-year:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-history"></i> HistoryTimeline
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="search.php"><i class="fas fa-search"></i> Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-container">
            <h2 class="search-title">Search Historical Events</h2>
            
            <!-- Search Form -->
            <div class="search-form">
                <form method="GET" action="" class="input-group">
                    <input type="number" name="year" class="form-control search-input" 
                           placeholder="Enter Year (e.g., 1947)" min="1500" max="2024" 
                           value="<?php echo $search_year; ?>" required>
                    <button class="btn search-btn" type="submit">
                        <i class="fas fa-search me-2"></i> SEARCH
                    </button>
                </form>
                <div class="text-center mt-3">
                    <small class="text-muted">Enter a year between 1500 and 2024 to explore historical events</small>
                </div>
            </div>
            
            <!-- Search Results -->
            <?php if (!empty($search_results)): ?>
                <div class="mb-4">
                    <h4 class="mb-4">
                        <i class="fas fa-calendar-check me-2"></i>
                        Events in <?php echo $search_year; ?>
                        <span class="badge bg-primary ms-2"><?php echo count($search_results); ?> found</span>
                    </h4>
                    
                    <?php foreach ($search_results as $event): ?>
                    <div class="result-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="event-year"><?php echo $event['year']; ?></span>
                                <h5><?php echo htmlspecialchars($event['title']); ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('F j, Y', strtotime($event['date'])); ?>
                                </p>
                                <p class="mb-3"><?php echo substr(htmlspecialchars($event['description']), 0, 200); ?>...</p>
                            </div>
                            <div>
                                <?php if($event['category']): ?>
                                <span class="badge bg-success"><?php echo $event['category']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-view">
                            <i class="fas fa-info-circle me-2"></i>View Details
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
            <?php elseif ($message): ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h4>No Events Found</h4>
                    <p class="mb-4"><?php echo $message; ?></p>
                    
                    <div class="suggestions mt-4">
                        <p class="mb-3">Try searching for these important years:</p>
                        <div class="d-flex flex-wrap justify-content-center">
                            <a href="?year=1857" class="suggestion-year">1857</a>
                            <a href="?year=1885" class="suggestion-year">1885</a>
                            <a href="?year=1919" class="suggestion-year">1919</a>
                            <a href="?year=1942" class="suggestion-year">1942</a>
                            <a href="?year=1947" class="suggestion-year">1947</a>
                            <a href="?year=1971" class="suggestion-year">1971</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="text-center mt-5">
                <a href="dashboard.php" class="btn btn-view me-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <a href="../timeline.php" class="btn btn-outline-primary">
                    <i class="fas fa-stream me-2"></i>View Complete Timeline
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Focus on search input
        document.querySelector('input[name="year"]').focus();
        
        // Validate year input
        document.querySelector('form').addEventListener('submit', function(e) {
            const yearInput = document.querySelector('input[name="year"]');
            const year = parseInt(yearInput.value);
            
            if (year && (year < 1500 || year > 2024)) {
                e.preventDefault();
                alert('Please enter a year between 1500 and 2024');
                yearInput.focus();
            }
        });
        
        // Smooth scroll to results
        if (window.location.search.includes('year=')) {
            setTimeout(() => {
                document.querySelector('.result-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    </script>
</body>
</html>