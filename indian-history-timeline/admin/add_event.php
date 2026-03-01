<?php
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/events/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = intval($_POST['year']);
    $date = $_POST['date'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $significance = trim($_POST['significance']);
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $file_type = $_FILES['event_image']['type'];
        $file_size = $_FILES['event_image']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'Only JPG, PNG, and GIF images are allowed';
        } elseif ($file_size > $max_size) {
            $error = 'Image size must be less than 5MB';
        } else {
            // Generate unique filename
            $file_extension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
            $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['event_image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/events/' . $new_filename;
            } else {
                $error = 'Failed to upload image';
            }
        }
    }
    
    // Validation
    if (empty($title) || empty($year) || empty($date) || empty($location) || empty($description)) {
        $error = 'Please fill all required fields';
    } elseif ($year < 1500 || $year > 2024) {
        $error = 'Year must be between 1500 and 2024';
    } else {
        // Insert into database with image path
        $stmt = $conn->prepare("INSERT INTO historical_events (title, year, date, location, description, category, significance, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssss", $title, $year, $date, $location, $description, $category, $significance, $image_path);
        
        if ($stmt->execute()) {
            $success = 'Historical event added successfully!';
            $_POST = array(); // Clear form
        } else {
            $error = 'Failed to add event: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event | Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Inter', sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1a237e 0%, #283593 100%);
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
            border-left-color: #ffab00;
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
        
        .event-form-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            color: #1a237e;
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #ffab00;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            border: none;
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.3);
            color: white;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 14px 25px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
        }
        
        .image-preview {
            margin-top: 15px;
            max-width: 300px;
            border-radius: 10px;
            display: none;
        }
        
        .image-preview img {
            width: 100%;
            border-radius: 10px;
            border: 3px solid #1a237e;
        }
        
        .upload-area {
            border: 3px dashed #1a237e;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            background: #e9ecef;
            border-color: #ffab00;
        }
        
        .upload-area i {
            font-size: 48px;
            color: #1a237e;
            margin-bottom: 15px;
        }
        
        .upload-area p {
            margin-bottom: 5px;
            color: #666;
        }
        
        .upload-area small {
            color: #999;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">Add New Event</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <a href="add_event.php" class="active"><i class="fas fa-plus-circle"></i> Add Event</a>
            <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
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
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Add New Historical Event
                    </h5>
                    <div class="d-flex align-items-center">
                        <span class="me-3 text-muted">
                            <i class="fas fa-calendar-day me-2"></i><?php echo date('F j, Y'); ?>
                        </span>
                        <span class="badge bg-primary"><?php echo $_SESSION['admin_username']; ?></span>
                    </div>
                </div>
            </div>
        </nav>

        <div class="event-form-container">
            <h3 class="form-title">
                <i class="fas fa-plus-circle me-2"></i>Add New Historical Event
            </h3>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <a href="manage_events.php" class="float-end">View All Events →</a>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Event Title *</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                               placeholder="Enter event title" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="Ancient" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Ancient') ? 'selected' : ''; ?>>Ancient</option>
                            <option value="Medieval" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Medieval') ? 'selected' : ''; ?>>Medieval</option>
                            <option value="Empire" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Empire') ? 'selected' : ''; ?>>Empire</option>
                            <option value="Independence" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Independence') ? 'selected' : ''; ?>>Independence</option>
                            <option value="Modern" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Modern') ? 'selected' : ''; ?>>Modern</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Year *</label>
                        <input type="number" name="year" class="form-control" 
                               value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>"
                               min="1500" max="2024" placeholder="e.g., 1947" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" 
                               value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Location *</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                           placeholder="Enter event location" required>
                </div>
                
                <!-- Image Upload Section -->
                <div class="mb-4">
                    <label class="form-label">Event Image</label>
                    <div class="upload-area" onclick="document.getElementById('event_image').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload or drag and drop</p>
                        <small>JPG, PNG or GIF (Max. 5MB)</small>
                        <input type="file" id="event_image" name="event_image" 
                               accept="image/jpeg,image/png,image/jpg,image/gif" 
                               style="display: none;" onchange="previewImage(this)">
                    </div>
                    <div class="image-preview" id="imagePreview">
                        <img src="" alt="Preview">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImage()">
                            <i class="fas fa-times me-2"></i>Remove Image
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description *</label>
                    <textarea name="description" class="form-control" rows="5" 
                              placeholder="Enter detailed description of the event" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Historical Significance</label>
                    <textarea name="significance" class="form-control" rows="3" 
                              placeholder="Explain the importance and impact of this event"><?php echo isset($_POST['significance']) ? htmlspecialchars($_POST['significance']) : ''; ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-save me-2"></i>Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set today's date as default for date field
        if (!document.querySelector('input[name="date"]').value) {
            const today = new Date().toISOString().split('T')[0];
            document.querySelector('input[name="date"]').value = today;
        }
        
        // Focus on title field
        document.querySelector('input[name="title"]').focus();
        
        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Remove image function
        function removeImage() {
            const input = document.getElementById('event_image');
            const preview = document.getElementById('imagePreview');
            
            input.value = '';
            preview.style.display = 'none';
            preview.querySelector('img').src = '';
        }
        
        // Validate file size
        document.getElementById('event_image').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // in MB
                if (fileSize > 5) {
                    alert('File size must be less than 5MB');
                    this.value = '';
                    document.getElementById('imagePreview').style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>