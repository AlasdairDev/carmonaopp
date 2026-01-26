<?php


// Show PHP errors (IMPORTANT for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Start session
session_start();


// Load config safely
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    define("SITE_NAME", "LGU Permit Tracking System");
}


// Fallback if SITE_NAME is missing
if (!defined('SITE_NAME')) {
    define("SITE_NAME", "LGU Permit Tracking System");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="./assets/favicon.png">

    <style>
        /* ================================
           GREEN THEMED LANDING PAGE
           ================================ */


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            color: #2d3748;
        }
       
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }


        /* Navbar */
        .navbar {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }


        .navbar .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .navbar-brand a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }


        .navbar-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #7BA428;
        }


        .navbar-menu {
            display: flex;
            gap: 1rem;
            align-items: center;
        }


        .nav-links {
            display: flex;
            gap: 2rem;
            margin-right: 2rem;
        }


        .nav-links a {
            color: #2d3748;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }


        .nav-links a:hover {
            color: #7BA428;
        }


        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #7BA428;
            transition: width 0.3s ease;
        }


        .nav-links a:hover::after {
            width: 100%;
        }


        .btn-login, .btn-register {
            padding: 0.6rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
        }


        .btn-login {
            background: transparent;
            color: #7BA428;
            border: 2px solid #7BA428;
        }


        .btn-login:hover {
            background: #7BA428;
            color: white;
        }


        .btn-register {
            background: #7BA428;
            color: white;
            border: 2px solid #7BA428;
        }


        .btn-register:hover {
            background: #6a9322;
            transform: translateY(-2px);
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 0.4rem;
            cursor: pointer;
            background: #7BA428;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            z-index: 1001;
            position: relative;
        }

        .menu-toggle span {
            width: 25px;
            height: 3px;
            background: white;
            border-radius: 3px;
            transition: all 0.3s ease;
            display: block;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(8px, -8px);
        }


        /* Hero Section */
        .hero-section {
            margin-top: 0;
            padding-top: 80px;
            background: linear-gradient(135deg, #7BA428 0%, #9BC446 100%);
            padding-bottom: 6rem;
            padding-left: 2rem;
            padding-right: 2rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }


        .hero-content {
            max-width: 1400px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }


        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            line-height: 1.2;
        }


        .hero-text p {
            font-size: 1.3rem;
            color: rgba(255,255,255,0.95);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }


        .hero-actions {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }


        .btn {
            padding: 0.9rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-block;
            cursor: pointer;
        }


        .btn-hero-primary {
            background: white;
            color: #7BA428;
            border: 2px solid white;
        }


        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 30px rgba(255, 255, 255, 0.5);
        }


        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }


        .btn-hero-outline:hover {
            background: white;
            color: #7BA428;
            transform: translateY(-3px);
        }


        .hero-placeholder {
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 0;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }


        .hero-placeholder img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 18px;
        }


        /* Mission Vision Section */
        .mission-vision-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #fdfef9 0%, #f5f9e8 50%, #edf5d8 100%);
            position: relative;
        }
       
        .mission-vision-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 70% 20%, rgba(155, 196, 70, 0.1) 0%, transparent 60%),
                radial-gradient(circle at 30% 80%, rgba(123, 164, 40, 0.06) 0%, transparent 60%);
            pointer-events: none;
        }
       
        .mission-vision-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            width: 100%;
        }


        .mission-vision-text h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2rem;
        }


        .mission-vision-text p {
            font-size: 1.1rem;
            color: #718096;
            line-height: 1.8;
            margin-bottom: 2rem;
        }


        .mission-vision-cards {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }


        .mv-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 15px;
            border-left: 4px solid #7BA428;
        }


        .mv-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }


        .mv-icon {
            font-size: 1.5rem;
        }


        .mv-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
        }


        .mv-card p {
            color: #2d3748;
            line-height: 1.6;
        }


        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }


        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 1rem;
        }


        .section-header p {
            font-size: 1.2rem;
            color: #718096;
        }


        /* How It Works Section */
        .how-it-works-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #f0f7e0 0%, #e8f5d0 50%, #dff2c0 100%);
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
       
        .how-it-works-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 50%, rgba(123, 164, 40, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(155, 196, 70, 0.08) 0%, transparent 50%);
            pointer-events: none;
        }
       
        .how-it-works-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }


        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }


        .step-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }


        .step-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(123, 164, 40, 0.2);
        }


        .step-number {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #7BA428 0%, #9BC446 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            margin: 0 auto 1.5rem;
        }


        .step-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.75rem;
        }


        .step-card p {
            color: #718096;
            line-height: 1.6;
        }


        /* Footer Section */
        .footer-section {
            background: #2d3e1f;
            color: white;
            padding: 2rem;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin-top: 0;
        }


        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
        }


        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 3rem;
            text-align: center;
            margin: auto 0;
        }


        .footer-column {
            display: flex;
            flex-direction: column;
            align-items: center;
        }


        .footer-column h3 {
            color: #7BA428;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
        }


        .footer-column p,
        .footer-column a {
            color: #b8c5d6;
            line-height: 1.8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            text-align: center;
        }


        .footer-column a:hover {
            color: #9BC446;
        }


        .footer-contact-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            text-align: center;
        }


        .footer-contact-item span:first-child {
            color: #7BA428;
        }


        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            padding-bottom: 1rem;
            text-align: center;
            color: #8899a6;
            font-size: 0.9rem;
        }


        /* ===================================
           RESPONSIVE DESIGN - MOBILE/TABLET
           =================================== */

        /* Large Tablets & Small Laptops (1024px and below) */
        @media (max-width: 1024px) {
            .hero-content,
            .mission-vision-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .hero-content {
                grid-template-columns: 1fr;
            }

            /* Swap order on tablet - image first, text second */
            .hero-placeholder {
                order: 1;
            }

            .hero-text {
                order: 2;
            }

            .steps-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero-text h1 {
                font-size: 2.8rem;
            }

            .hero-text p {
                font-size: 1.1rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }

            .mission-vision-text h2 {
                font-size: 2rem;
            }
        }

        /* Tablets (768px and below) */
        @media (max-width: 768px) {
            .navbar .container {
                flex-wrap: wrap;
                padding: 0 1.5rem;
                position: relative;
            }

            .navbar-brand {
                order: 1;
                flex: 1;
            }

            .menu-toggle {
                display: flex !important;
                order: 2;
            }

            .navbar-menu {
                order: 3;
                flex-direction: column;
                width: 100%;
                background: white;
                padding: 0;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                border-radius: 0 0 12px 12px;
                margin-top: 1rem;
                display: none;
            }

            .navbar-menu.show {
                display: flex;
                animation: slideDown 0.3s ease;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .nav-links {
                flex-direction: column;
                gap: 0;
                margin-right: 0;
                text-align: center;
                width: 100%;
                padding: 1rem 0;
                margin-bottom: 0;
                border-bottom: 1px solid #e2e8f0;
            }

            .nav-links a {
                padding: 0.75rem 1rem;
                width: 100%;
                display: block;
            }

            .nav-links a:hover {
                background: #f8f9fa;
            }

            .navbar-menu .btn-login,
            .navbar-menu .btn-register {
                width: 90%;
                margin: 0.5rem auto;
                text-align: center;
                justify-content: center;
                display: flex;
            }

            .navbar-menu .btn-register {
                margin-bottom: 1rem;
            }

            .navbar-title {
                font-size: 1rem;
            }

            .navbar-brand a img {
                width: 40px !important;
                height: 40px !important;
            }

            .hero-section {
                padding-top: 100px;
                padding-bottom: 3rem;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
                min-height: auto;
            }

            .hero-text h1 {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }

            .hero-text p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .hero-actions {
                flex-direction: column;
                gap: 1rem;
            }

            .hero-actions .btn {
                width: 100%;
                text-align: center;
            }

            .hero-placeholder {
                min-height: 250px;
                margin-bottom: 1.5rem;
            }

            .steps-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .step-card {
                padding: 1.5rem;
            }

            .step-number {
                width: 60px;
                height: 60px;
                font-size: 1.75rem;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .section-header p {
                font-size: 1rem;
            }

            .mission-vision-section,
            .how-it-works-section {
                padding: 3rem 1.5rem;
                min-height: auto;
            }

            .mission-vision-container {
                gap: 2rem;
            }

            .mission-vision-text h2 {
                font-size: 1.75rem;
                margin-bottom: 1.5rem;
            }

            .mission-vision-text p {
                font-size: 1rem;
                margin-bottom: 1.5rem;
            }

            .mv-card {
                padding: 1.5rem;
            }

            .mv-card h3 {
                font-size: 1.25rem;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }

            .footer-column h3 {
                font-size: 1.1rem;
            }

            .footer-section {
                padding: 2rem 1.5rem;
                min-height: auto;
            }
        }

        /* Mobile Phones (480px and below) */
        @media (max-width: 480px) {
            .navbar .container {
                padding: 0 1rem;
            }

            .hero-section {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .hero-text h1 {
                font-size: 1.75rem;
            }

            .hero-text p {
                font-size: 0.9375rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9375rem;
            }

            .hero-placeholder {
                min-height: 200px;
            }

            .section-header h2 {
                font-size: 1.5rem;
            }

            .section-header p {
                font-size: 0.9375rem;
            }

            .mission-vision-text h2 {
                font-size: 1.5rem;
            }

            .mission-vision-text p {
                font-size: 0.9375rem;
            }

            .step-card h3 {
                font-size: 1.1rem;
            }

            .step-card p {
                font-size: 0.875rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .footer-column {
                text-align: center;
            }

            .footer-contact-item {
                flex-direction: column;
                gap: 0.25rem;
            }

            .mv-card-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Very Small Screens (360px and below) */
        @media (max-width: 360px) {
            .hero-text h1 {
                font-size: 1.5rem;
            }

            .navbar-title {
                font-size: 0.875rem;
            }

            .btn-login, .btn-register {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>
</head>
<body>


<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="index.php">
                <img src="./assets/carmona-logo.png" alt="City of Carmona Logo" style="width: 50px; height: 50px; object-fit: contain;">
                <span class="navbar-title"><?php echo SITE_NAME; ?></span>
            </a>
        </div>

        <button class="menu-toggle" id="menuToggle" type="button" onclick="document.getElementById('navbarMenu').classList.toggle('show'); this.classList.toggle('active'); console.log('BURGER CLICKED!');">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="navbar-menu" id="navbarMenu">
            <nav class="nav-links">
                <a href="#home">Home</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#mission-vision">About Us</a>
                <a href="#contact">Contact</a>
            </nav>


            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="btn-register">Admin Dashboard</a>
                <?php else: ?>
                    <a href="user/dashboard.php" class="btn-register">My Dashboard</a>
                <?php endif; ?>
                <a href="auth/logout.php" class="btn-login">Logout</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>


<!-- HERO SECTION -->
<section class="hero-section" id="home">
    <div class="hero-content">
        <div class="hero-placeholder">
            <img src="./assets/carmona-city.jpg" alt="Carmona City View">
        </div>
       
        <div class="hero-text">
            <h1>Carmona Online Portal</h1>
            <p>Permit & Document Request Tracking System<br>Track permits, submit documents, and stay updated on your applications—all in one convenient platform for Carmona, Cavite residents.</p>
           
            <div class="hero-actions">
                <a href="auth/register.php" class="btn btn-hero-primary">Sign Up</a>
                <a href="user/track.php" class="btn btn-hero-outline">Track Application</a>
            </div>
        </div>
    </div>
</section>


<!-- HOW IT WORKS SECTION -->
<section class="how-it-works-section" id="how-it-works">
    <div class="how-it-works-container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Get your permits and documents in just a few simple steps</p>
        </div>


        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create Account</h3>
                <p>Register using your email and create a secure account to access all services.</p>
            </div>


            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Submit Request</h3>
                <p>Choose your service, fill out the form, and upload all required documents.</p>
            </div>


            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Track Progress</h3>
                <p>Monitor your application status in real-time through your dashboard.</p>
            </div>


            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Get Notified</h3>
                <p>Receive updates via SMS/email when your documents are ready for pick-up.</p>
            </div>
        </div>
    </div>
</section>


<!-- MISSION VISION SECTION -->
<section class="mission-vision-section" id="mission-vision" style="background: linear-gradient(135deg, #a8d060 0%, #b5db70 15%, #c2e680 30%, #d0f090 50%, #ddf5a0 70%, #eafab0 85%, #f5fdc8 100%) !important; min-height: 100vh; display: flex; align-items: center;">
    <div class="mission-vision-container">
        <div class="mission-vision-text">
            <h2>Digital Governance for a Smarter City</h2>
            <p>The City of Carmona Portal was developed by the City's Information and Communications Technology Department (ICTD) with a primary goal: to streamline bureaucratic processes and empower citizens and businesses through digital efficiency.</p>
            <p>We are committed to transparency, speed, and public convenience.</p>
            <p>Since its launch in 2024, the platform has processed over 50,000 applications, cutting down average processing time for key permits by 40%. Our infrastructure uses secure, modern encryption to protect all your sensitive data and ensure compliance with national data privacy laws.</p>
        </div>


        <div class="mission-vision-cards">
            <div class="mv-card">
                <div class="mv-card-header">
                    <span class="mv-icon">🎯</span>
                    <h3>Our Mission</h3>
                </div>
                <p>To provide a single, secure, and intuitive digital access point for all government services, fostering a culture of transparency and efficiency in Carmona.</p>
            </div>


            <div class="mv-card">
                <div class="mv-card-header">
                    <span class="mv-icon">💡</span>
                    <h3>Our Vision</h3>
                </div>
                <p>To be the premier model for digital governance in the country, building a fully paperless, smarter, and citizen-centric City of Carmona.</p>
            </div>
        </div>
    </div>
</section>


<!-- FOOTER SECTION -->
<footer class="footer-section" id="contact">
    <div class="footer-container">
        <div class="footer-content">
           <div class="footer-column">
                <h3>About</h3>
                <p>The City of Carmona Portal is your one-stop digital platform for all government permit and document services, designed to make your life easier.</p>
            </div>


           <div class="footer-column">
    <h3>Quick Links</h3>
    <a href="index.php">Home</a>
    <a href="#how-it-works">How It Works</a>
    <a href="#mission-vision">About Us</a>
    <a href="#contact">Contact</a>
</div>


            <div class="footer-column">
                <h3>Contact Us</h3>
                <div class="footer-contact-item">
                    <span>📍</span>
                    <span>City Hall, Carmona, Cavite, Philippines</span>
                </div>
                <div class="footer-contact-item">
                    <span>📞</span>
                    <span>(046) 430-0042</span>
                </div>
                <div class="footer-contact-item">
                    <span>📧</span>
                    <span>info@carmona.gov.ph</span>
                </div>
            </div>


            <div class="footer-column">
                <h3>Office Hours</h3>
                <p><strong>Monday - Friday</strong><br>8:00 AM - 5:00 PM</p>
                <p><strong>Saturday</strong><br>9:00 AM - 12:00 PM</p>
                <p style="margin-top: 1rem;"><em>Closed on Sundays and Holidays</em></p>
            </div>
        </div>


       <div class="footer-bottom">
    © 2026 City of Carmona Portal. All rights reserved.
    <div class="footer-column">
        <a href="https://cavite.gov.ph/home/privacy-policy/">Privacy Policy</a>
    </div>
</div>


        </div>