<?php
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$event = null;

// Get event ID from URL
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($event_id <= 0) {
    header('Location: manageevents.php');
    exit();
}

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM historical_events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $event = $result->fetch_assoc();
} else {
    header('Location: manageevents.php');
    exit();
}
$stmt->close();

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/events/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $year = intval($_POST['year']);
    $date = $_POST['date'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $significance = trim($_POST['significance']);
    
    // Handle image upload
    $image_path = $event['image_path']; // Keep existing image by default
    
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
            // Delete old image if exists
            if (!empty($event['image_path']) && file_exists('../' . $event['image_path'])) {
                unlink('../' . $event['image_path']);
            }
            
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
    
    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if (!empty($event['image_path']) && file_exists('../' . $event['image_path'])) {
            unlink('../' . $event['image_path']);
        }
        $image_path = '';
    }
    
    // Validation
    if (empty($title) || empty($year) || empty($date) || empty($location) || empty($description)) {
        $error = 'Please fill all required fields';
    } elseif ($year < 1500 || $year > 2024) {
        $error = 'Year must be between 1500 and 2024';
    } else {
        // Update database
        $stmt = $conn->prepare("UPDATE historical_events SET title=?, year=?, date=?, location=?, description=?, category=?, significance=?, image_path=? WHERE id=?");
        $stmt->bind_param("sissssssi", $title, $year, $date, $location, $description, $category, $significance, $image_path, $event_id);
        
        if ($stmt->execute()) {
            $success = 'Historical event updated successfully!';
            
            // Refresh event data
            $stmt2 = $conn->prepare("SELECT * FROM historical_events WHERE id = ?");
            $stmt2->bind_param("i", $event_id);
            $stmt2->execute();
            $event = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error = 'Failed to update event: ' . $conn->error;
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
    <title>Edit Event | Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --danger: #d32f2f;
            --success: #2e7d32;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
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
        
        .event-form-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--secondary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .form-title i {
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .event-id-badge {
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
        
        .btn-update {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border: none;
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-update:hover {
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
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #b71c1c 100%);
            color: white;
            border: none;
            padding: 14px 25px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(211, 47, 47, 0.3);
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
        }
        
        .image-preview img {
            width: 100%;
            border-radius: 10px;
            border: 3px solid var(--primary);
        }
        
        .upload-area {
            border: 3px dashed var(--primary);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            background: #e9ecef;
            border-color: var(--secondary);
        }
        
        .upload-area i {
            font-size: 48px;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .upload-area p {
            margin-bottom: 5px;
            color: #666;
        }
        
        .upload-area small {
            color: #999;
        }
        
        .current-image {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 2px solid var(--primary);
        }
        
        .current-image img {
            max-height: 150px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        
        .btn-remove-image {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-remove-image:hover {
            background: #b71c1c;
            transform: translateY(-2px);
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
            <p class="mb-0 small">Edit Event</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="manageusers.php"><i class="fas fa-users"></i> Manage Users</a>
            <a href="manageevents.php"><i class="fas fa-calendar-alt"></i> Manage Events</a>
            <a href="add_event.php"><i class="fas fa-plus-circle"></i> Add Event</a>
            <a href="edit_event.php" class="active"><i class="fas fa-edit"></i> Edit Event</a>
            <a href="report.php"><i class="fas fa-chart-bar"></i> Reports</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
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
                        <i class="fas fa-edit text-primary me-2"></i>
                        Edit Historical Event
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
            <div class="form-title">
                <div>
                    <i class="fas fa-edit"></i>
                    Edit Event: <?php echo htmlspecialchars($event['title']); ?>
                </div>
                <span class="event-id-badge">Event ID: <?php echo $event['id']; ?></span>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <a href="manageevents.php" class="float-end">Back to Events →</a>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label required-field">Event Title</label>
                        <input type="text" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($event['title']); ?>"
                               placeholder="Enter event title" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="Ancient" <?php echo ($event['category'] == 'Ancient') ? 'selected' : ''; ?>>Ancient</option>
                            <option value="Medieval" <?php echo ($event['category'] == 'Medieval') ? 'selected' : ''; ?>>Medieval</option>
                            <option value="Empire" <?php echo ($event['category'] == 'Empire') ? 'selected' : ''; ?>>Empire</option>
                            <option value="Independence" <?php echo ($event['category'] == 'Independence') ? 'selected' : ''; ?>>Independence</option>
                            <option value="Modern" <?php echo ($event['category'] == 'Modern') ? 'selected' : ''; ?>>Modern</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Year</label>
                        <input type="number" name="year" class="form-control" 
                               value="<?php echo $event['year']; ?>"
                               min="1500" max="2024" placeholder="e.g., 1947" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required-field">Date</label>
                        <input type="date" name="date" class="form-control" 
                               value="<?php echo $event['date']; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label required-field">Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?php echo htmlspecialchars($event['location']); ?>"
                           placeholder="Enter event location" required>
                </div>
                
                <!-- Image Upload Section -->
                <div class="mb-4">
                    <label class="form-label">Event Image</label>
                    
                    <?php if(!empty($event['image_path'])): ?>
                    <div class="current-image">
                        <div class="d-flex align-items-center">
                            <div class="me-4">
                                <img src="../<?php echo $event['image_path']; ?>" 
                                     alt="Current Image" style="max-height: 100px;">
                            </div>
                            <div>
                                <p class="mb-1"><strong>Current Image:</strong> <?php echo basename($event['image_path']); ?></p>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image" id="removeImage" value="1">
                                    <label class="form-check-label text-danger" for="removeImage">
                                        <i class="fas fa-trash-alt me-1"></i>Remove this image
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="upload-area" onclick="document.getElementById('event_image').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload new image or drag and drop</p>
                        <small>JPG, PNG or GIF (Max. 5MB)</small>
                        <input type="file" id="event_image" name="event_image" 
                               accept="image/jpeg,image/png,image/jpg,image/gif" 
                               style="display: none;" onchange="previewImage(this)">
                    </div>
                    
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img src="" alt="Preview">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removePreview()">
                            <i class="fas fa-times me-2"></i>Cancel New Image
                        </button>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label required-field">Description</label>
                    <textarea name="description" class="form-control" rows="5" 
                              placeholder="Enter detailed description of the event" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Historical Significance</label>
                    <textarea name="significance" class="form-control" rows="3" 
                              placeholder="Explain the importance and impact of this event"><?php echo htmlspecialchars($event['significance']); ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="manageevents.php" class="btn btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <a href="../event-details.php?id=<?php echo $event['id']; ?>" target="_blank" class="btn btn-info text-white ms-2">
                            <i class="fas fa-eye me-2"></i>Preview
                        </a>
                    </div>
                    <div>
                        <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-danger me-2" 
                           onclick="return confirm('Are you sure you want to delete this event?')">
                            <i class="fas fa-trash me-2"></i>Delete
                        </a>
                        <button type="submit" class="btn btn-update">
                            <i class="fas fa-save me-2"></i>Update Event
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Event Meta Information -->
            <div class="row mt-5">
                <div class="col-md-6">
                    <div class="info-box bg-light p-3 rounded">
                        <small class="text-muted">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Created:</strong> <?php echo date('F j, Y \a\t h:i A', strtotime($event['created_at'] ?? 'now')); ?>
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-light p-3 rounded">
                        <small class="text-muted">
                            <i class="fas fa-edit me-2"></i>
                            <strong>Last Updated:</strong> <?php echo date('F j, Y \a\t h:i A', strtotime($event['updated_at'] ?? 'now')); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    
                    // Hide current image section if exists
                    const currentImage = document.querySelector('.current-image');
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Remove preview function
        function removePreview() {
            const input = document.getElementById('event_image');
            const preview = document.getElementById('imagePreview');
            
            input.value = '';
            preview.style.display = 'none';
            preview.querySelector('img').src = '';
            
            // Show current image section if exists
            const currentImage = document.querySelector('.current-image');
            if (currentImage) {
                currentImage.style.display = 'block';
            }
            
            // Uncheck remove image checkbox
            const removeCheckbox = document.getElementById('removeImage');
            if (removeCheckbox) {
                removeCheckbox.checked = false;
            }
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
        
        // Confirm before leaving with unsaved changes
        let formChanged = false;
        
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', () => {
                formChanged = true;
            });
        });
        
        document.querySelector('form').addEventListener('submit', () => {
            formChanged = false;
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        // Preview event link
        document.querySelector('a[target="_blank"]')?.addEventListener('click', function(e) {
            if (formChanged) {
                if (!confirm('You have unsaved changes. Continue to preview?')) {
                    e.preventDefault();
                }
            }
        });
        
        // Handle remove image checkbox
        const removeCheckbox = document.getElementById('removeImage');
        if (removeCheckbox) {
            removeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    document.getElementById('event_image').value = '';
                    document.getElementById('imagePreview').style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>