<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Indian History Timeline</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --accent: #b71c1c;
            --light: #f5f5f5;
            --dark: #0d1b2a;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #333;
            padding-top: 76px;
        }
        
        /* Navigation */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.2);
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: white !important;
        }
        
        .navbar-brand i {
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 8px 20px !important;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--secondary) !important;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(rgba(26, 35, 126, 0.9), rgba(40, 53, 147, 0.9)), 
                        url('https://images.unsplash.com/photo-1524492412937-c28074a5d7da?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        /* About Sections */
        .about-section {
            padding: 60px 0;
            background: white;
        }
        
        .section-title {
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--secondary);
        }
        
        .mission-card {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            border-radius: 20px;
            padding: 40px;
            height: 100%;
            box-shadow: 0 20px 40px rgba(26, 35, 126, 0.2);
        }
        
        .mission-icon {
            font-size: 60px;
            color: var(--secondary);
            margin-bottom: 25px;
        }
        
        .value-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
            border-bottom: 4px solid var(--primary);
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .value-icon {
            font-size: 50px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        
        .team-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            text-align: center;
            height: 100%;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .team-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .team-info {
            padding: 25px;
        }
        
        .team-info h4 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .team-info p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .team-social a {
            color: var(--primary);
            margin: 0 10px;
            font-size: 18px;
            transition: all 0.3s;
        }
        
        .team-social a:hover {
            color: var(--secondary);
            transform: translateY(-3px);
        }
        
        .timeline-stats {
            background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);
            color: white;
            padding: 80px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--secondary);
        }
        
        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin: 20px 0;
        }
        
        .testimonial-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary);
        }
        
        .testimonial-text {
            font-style: italic;
            margin: 20px 0;
            color: #555;
        }
        
        .testimonial-author {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark) 0%, #1b263b 100%);
            color: white;
            padding: 80px 0 30px;
            clip-path: polygon(0 10%, 100% 0, 100% 100%, 0 100%);
        }
        
        .footer h5 {
            color: var(--secondary);
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
            transition: all 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }
        
        .social-icons a {
            color: white;
            font-size: 20px;
            margin-right: 15px;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--secondary);
            transform: translateY(-3px);
        }
        
        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 25px;
            margin-top: 50px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .stat-number {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
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
                        <a class="nav-link" href="timeline.php">Timeline</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1 class="page-title">About Us</h1>
            <p class="lead">Preserving India's rich heritage through interactive storytelling</p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <h2 class="section-title">Our Story</h2>
                    <p class="lead" style="color: var(--primary);">Bringing history to life, one timeline at a time.</p>
                    <p>Indian History Interactive Timeline was born from a simple idea: history should be engaging, accessible, and memorable. Founded in 2026 by a group of history enthusiasts and educators from Loyola College, Chennai, we set out to create a platform that transforms how people learn about India's magnificent past.</p>
                    <p>What started as a small classroom project has grown into a comprehensive digital archive, featuring thousands of historical events, rich multimedia content, and interactive features that make learning history an adventure rather than a chore.</p>
                    <p>Today, we serve thousands of students, researchers, and history buffs worldwide, helping them explore the depths of Indian civilization from the Indus Valley to modern times.</p>
                    
                    <div class="mt-4">
                        <a href="timeline.php" class="btn btn-lg" style="background: var(--primary); color: white; padding: 12px 30px; border-radius: 50px;">
                            <i class="fas fa-play-circle me-2"></i>Start Exploring
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <img src="https://images.unsplash.com/photo-1564981797816-1043664bf78d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                         alt="Our Team" class="img-fluid rounded-4 shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="mb-4">Our Mission</h3>
                        <p class="lead mb-4">To make Indian history accessible, engaging, and meaningful for everyone, everywhere.</p>
                        <p>We believe that understanding history is key to understanding ourselves and our future. Through innovative technology and curated content, we aim to bridge the gap between academic research and public knowledge.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="mission-card" style="background: linear-gradient(135deg, #b71c1c 0%, #d32f2f 100%);">
                        <div class="mission-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="mb-4">Our Vision</h3>
                        <p class="lead mb-4">To become the world's most trusted and comprehensive digital resource for Indian history.</p>
                        <p>We envision a future where every student, teacher, and history enthusiast can explore India's past with ease, discovering connections and insights that inspire a deeper appreciation for our shared heritage.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Values -->
    <section class="about-section">
        <div class="container">
            <h2 class="section-title text-center mx-auto" style="width: fit-content;">Our Core Values</h2>
            <p class="text-center lead mb-5">The principles that guide everything we do</p>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Accuracy</h4>
                        <p class="text-muted">Every event is carefully researched and verified by historians</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-universal-access"></i>
                        </div>
                        <h4>Accessibility</h4>
                        <p class="text-muted">Free and open access to historical knowledge for everyone</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <h4>Innovation</h4>
                        <p class="text-muted">Using technology to make learning history engaging and interactive</p>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="value-card">
                        <div class="value-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h4>Inclusivity</h4>
                        <p class="text-muted">Representing diverse perspectives and regional histories</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <h5><i class="fas fa-history me-2"></i>Indian History Timeline</h5>
                    <p style="color: rgba(255, 255, 255, 0.8);">An interactive educational platform exploring India's rich historical heritage through engaging visual timelines and multimedia content.</p>
                    <div class="social-icons mt-4">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-5">
                    <h5>Quick Links</h5>
                    <div class="footer-links">
                        <a href="index.php">Home</a>
                        <a href="timeline.php">Timeline</a>
                        <a href="about.php">About Us</a>
                        <a href="contact.php">Contact</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-5">
                    <h5>Account</h5>
                    <div class="footer-links">
                        <a href="user/login.php">User Login</a>
                        <a href="user/register.php">User Register</a>
                        <a href="admin/login.php">Admin Login</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
                
                <div class="col-lg-3 mb-5">
                    <h5>Contact Info</h5>
                    <div style="color: rgba(255, 255, 255, 0.8);">
                        <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Loyola College, Chennai - 600034</p>
                        <p class="mb-2"><i class="fas fa-envelope me-2"></i>info@historytimeline.com</p>
                        <p class="mb-2"><i class="fas fa-phone me-2"></i>+91 98765 43210</p>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2026 Indian History Interactive Timeline. All rights reserved.</p>
                <p>Developed by S RAGUL BALA | Guided by Dr. DEEBA | Loyola College (Autonomous)</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>