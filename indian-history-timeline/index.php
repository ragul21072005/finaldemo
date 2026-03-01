<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indian History Interactive Timeline | Explore India's Heritage</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #ffab00;
            --accent: #b71c1c;
            --light: #f5f5f5;
            --dark: #0d1b2a;
            --success: #2e7d32;
            --info: #0277bd;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #333;
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
            letter-spacing: 0.5px;
        }
        
        .navbar-brand i {
            color: var(--secondary);
            margin-right: 10px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 8px 20px !important;
            margin: 0 5px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--secondary) !important;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(26, 35, 126, 0.9), rgba(40, 53, 147, 0.9)), 
                        url('https://images.unsplash.com/photo-1548013146-72479768bada?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 140px 0 100px;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-top: 76px;
        }
        
        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 25px;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 40px;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary) 0%, #ff8f00 100%);
            color: var(--primary);
            border: none;
            padding: 14px 35px;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 6px 20px rgba(255, 171, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 171, 0, 0.4);
            color: var(--primary);
        }
        
        .btn-outline-light {
            border: 2px solid white;
            color: white;
            padding: 12px 32px;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
        }
        
        /* Features */
        .features-section {
            padding: 100px 0;
            background: white;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .feature-icon {
            font-size: 55px;
            color: var(--primary);
            margin-bottom: 25px;
            display: inline-block;
            padding: 20px;
            background: linear-gradient(135deg, rgba(26, 35, 126, 0.1), rgba(255, 171, 0, 0.1));
            border-radius: 20px;
        }
        
        .feature-card h4 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        /* Timeline Preview */
        .timeline-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 100px 0;
        }
        
        .timeline-preview {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-item {
            padding: 25px;
            border-left: 4px solid var(--secondary);
            margin: 20px 0;
            background: white;
            transition: all 0.3s ease;
        }
        
        .timeline-item:hover {
            transform: translateX(10px);
            background: #f8f9fa;
        }
        
        .timeline-year {
            background: var(--primary);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
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
            display: inline-block;
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
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .feature-card {
                margin-bottom: 30px;
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="timeline.php">Timeline</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> Account
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="user/login.php"><i class="fas fa-sign-in-alt"></i> User Login</a></li>
                            <li><a class="dropdown-item" href="user/register.php"><i class="fas fa-user-plus"></i> User Register</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin/login.php"><i class="fas fa-user-shield"></i> Admin Login</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="hero-title">Indian History Interactive Timeline</h1>
                    <p class="hero-subtitle">Explore 5000+ years of India's rich cultural heritage through an engaging, visual timeline. Journey from ancient civilizations to modern independence, experiencing history like never before.</p>
                    
                    <div class="mt-4">
                        <a href="timeline.php" class="btn btn-primary me-3">
                            <i class="fas fa-play-circle me-2"></i>Start Exploring
                        </a>
                        <a href="user/register.php" class="btn btn-outline-light">
                            <i class="fas fa-user-plus me-2"></i>Create Free Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3" style="color: var(--primary);">Why Choose Our Timeline?</h2>
                    <p class="lead text-muted">An immersive educational experience that brings history to life</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Chronological Timeline</h4>
                        <p>Navigate through centuries with our interactive timeline that visually presents historical events in chronological order.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-images"></i>
                        </div>
                        <h4>Rich Multimedia</h4>
                        <p>Experience history through photos, videos, maps, and detailed descriptions that make learning engaging and memorable.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Advanced Search</h4>
                        <p>Find events by year, location, historical figures, or keywords with our powerful search functionality.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Preview -->
    <section class="timeline-section">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3" style="color: var(--primary);">Featured Historical Events</h2>
                    <p class="lead text-muted">Explore key moments that shaped Indian history</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="timeline-preview">
                        <div class="p-4">
                            <div class="timeline-item">
                                <span class="timeline-year">2500 BCE</span>
                                <h5>Indus Valley Civilization</h5>
                                <p class="text-muted">One of the world's earliest urban civilizations flourishing in the northwestern regions of South Asia.</p>
                                <span class="badge bg-primary">Ancient</span>
                            </div>
                            
                            <div class="timeline-item">
                                <span class="timeline-year">1526 CE</span>
                                <h5>Mughal Empire Begins</h5>
                                <p class="text-muted">Babur establishes the Mughal Empire after winning the First Battle of Panipat.</p>
                                <span class="badge bg-success">Empire</span>
                            </div>
                            
                            <div class="timeline-item">
                                <span class="timeline-year">1947 CE</span>
                                <h5>Indian Independence</h5>
                                <p class="text-muted">India gains independence from British rule, ending nearly 200 years of colonial governance.</p>
                                <span class="badge bg-danger">Modern</span>
                            </div>
                            
                            <div class="text-center mt-4">
                                <a href="timeline.php" class="btn btn-primary">
                                    <i class="fas fa-stream me-2"></i>View Complete Timeline
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, #283593 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-white">
                    <h3 class="mb-3">Ready to Explore Indian History?</h3>
                    <p class="mb-0">Join thousands of students, educators, and history enthusiasts on this educational journey.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="user/register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>Get Started Free
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5">
                    <h5>
                        <i class="fas fa-history me-2"></i>Indian History Timeline
                    </h5>
                    <p class="mt-3" style="color: rgba(255, 255, 255, 0.8);">
                        An interactive educational platform exploring India's rich historical heritage through engaging visual timelines and multimedia content.
                    </p>
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
                    <div class="footer-links mt-3">
                        <a href="index.php">Home</a>
                        <a href="timeline.php">Timeline</a>
                        <a href="about.php">About Us</a>
                        <a href="contact.php">Contact</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-5">
                    <h5>Account</h5>
                    <div class="footer-links mt-3">
                        <a href="user/login.php">User Login</a>
                        <a href="user/register.php">User Register</a>
                        <a href="admin/login.php">Admin Login</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Service</a>
                    </div>
                </div>
                
                <div class="col-lg-3 mb-5">
                    <h5>Contact Info</h5>
                    <div class="mt-3" style="color: rgba(255, 255, 255, 0.8);">
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Loyola College, Chennai - 600034
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            info@historytimeline.com
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +91 98765 43210
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p class="mb-2">&copy; 2026 Indian History Interactive Timeline. All rights reserved.</p>
                <p>Developed by S RAGUL BALA | Guided by Dr.DEEBA | Loyola College (Autonomous)</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scroll -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '10px 0';
                navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.boxShadow = '0 4px 20px rgba(26, 35, 126, 0.2)';
            }
        });
    </script>
</body>
</html>