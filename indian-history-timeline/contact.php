<?php include 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Indian History Interactive Timeline</title>
    
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
        
        /* Page Header */
        .page-header {
            background: linear-gradient(rgba(26, 35, 126, 0.95), rgba(40, 53, 147, 0.95)), 
                        url('https://images.unsplash.com/photo-1548013146-72479768bada?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 140px 0 80px;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-top: 76px;
        }
        
        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .breadcrumb {
            background: transparent;
            justify-content: center;
        }
        
        .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: var(--secondary);
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Contact Section */
        .contact-section {
            padding: 80px 0;
            background: white;
        }
        
        .contact-info-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            height: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .contact-info-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .contact-info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .contact-icon {
            width: 90px;
            height: 90px;
            line-height: 90px;
            text-align: center;
            background: linear-gradient(135deg, rgba(26, 35, 126, 0.1), rgba(255, 171, 0, 0.1));
            border-radius: 50%;
            margin: 0 auto 25px;
            font-size: 40px;
            color: var(--primary);
        }
        
        .contact-info-card h4 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .contact-info-card p {
            color: #666;
            margin-bottom: 8px;
            line-height: 1.8;
            font-size: 1.1rem;
        }
        
        .contact-info-card .contact-detail {
            font-weight: 500;
            color: #444;
        }
        
        /* Contact Info Section Title */
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .section-title p {
            color: #666;
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
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
            .page-title {
                font-size: 2.5rem;
            }
            
            .contact-info-card {
                margin-bottom: 30px;
            }
            
            .section-title h2 {
                font-size: 2rem;
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="page-title">Contact Us</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contact</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info Cards -->
    <section class="contact-section">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch With Us</h2>
                <p>We'd love to hear from you. Here's how you can reach us</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Our Address</h4>
                        <p class="contact-detail">Loyola College</p>
                        <p>Chennai - 600034</p>
                        <p>Tamil Nadu, India</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email Us</h4>
                        <p class="contact-detail">info@historytimeline.com</p>
                        <p>support@historytimeline.com</p>
                        <p>careers@historytimeline.com</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="contact-info-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h4>Call Us</h4>
                        <p class="contact-detail">+91 98765 43210</p>
                        <p>+91 98765 43211</p>
                        <p class="text-muted">Mon - Fri, 9:00 AM - 6:00 PM</p>
                    </div>
                </div>
            </div>
            
            <!-- Additional Contact Information -->
            <div class="row mt-5">
                <div class="col-lg-12">
                    <div class="text-center p-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px;">
                        <h4 class="mb-3" style="color: var(--primary);">Connect With Us</h4>
                        <p class="mb-4">Follow us on social media for updates and historical insights</p>
                        <div class="social-icons">
                            <a href="#" style="color: var(--primary); background: white; width: 45px; height: 45px; line-height: 45px; border-radius: 50%; margin: 0 8px; display: inline-block; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" style="color: var(--primary); background: white; width: 45px; height: 45px; line-height: 45px; border-radius: 50%; margin: 0 8px; display: inline-block; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" style="color: var(--primary); background: white; width: 45px; height: 45px; line-height: 45px; border-radius: 50%; margin: 0 8px; display: inline-block; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" style="color: var(--primary); background: white; width: 45px; height: 45px; line-height: 45px; border-radius: 50%; margin: 0 8px; display: inline-block; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fab fa-youtube"></i>
                            </a>
                            <a href="#" style="color: var(--primary); background: white; width: 45px; height: 45px; line-height: 45px; border-radius: 50%; margin: 0 8px; display: inline-block; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        </div>
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