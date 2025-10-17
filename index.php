<?php
session_start();
require_once "config/database.php";

$database = new Database();
$db = $database->getConnection();

// Fail fast if the connection couldn't be established
if ($db === null) {
    // Connection error message should already have been echoed by Database, but provide a more actionable hint and stop.
    echo "\nFatal error: Database connection failed. Please check your PHP PDO driver and database credentials. See config/database.php for configuration.\n";
    exit;
}

// Ambil data profil
$query = "SELECT * FROM profiles WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data paket (hanya yang aktif)
$query = "SELECT * FROM packages WHERE is_active = 1 ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data galeri (hanya yang aktif)
$query = "SELECT * FROM gallery WHERE is_active = 1 ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();
$gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kontak
$query = "SELECT * FROM contacts WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<?php
// Determine hero background image path (prefer uploads/profiles, then uploads, then local images)
$hero_bg = 'images/jeep-merapi.jpg';
if ($profile && !empty($profile['hero_image'])) {
    if (file_exists(__DIR__ . '/uploads/profiles/' . $profile['hero_image'])) {
        $hero_bg = 'uploads/profiles/' . $profile['hero_image'];
    } elseif (file_exists(__DIR__ . '/uploads/' . $profile['hero_image'])) {
        $hero_bg = 'uploads/' . $profile['hero_image'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $profile ? htmlspecialchars($profile['company_name']) : 'Jeep Adventure Jogja'; ?> - Petualangan Lava Tour Merapi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
   <style>
        :root {
            --primary: #FF6B35;
            --secondary: #1A3C40;
            --accent: #FFD166;
            --dark: #2D2D2D;
            --light: #F8F9FA;
            --adventure-dark: #1A1F25;
            --adventure-light: #E9ECEF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(26, 31, 37, 0.95);
            color: white;
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 107, 53, 0.3);
        }

        header.scrolled {
            padding: 10px 0;
            background-color: rgba(26, 31, 37, 0.98);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            margin-right: 10px;
        }

        .logo h1 {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .logo span {
            color: var(--primary);
            display: block;
            font-size: 0.8rem;
            font-weight: 400;
            letter-spacing: 2px;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 25px;
            position: relative;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s;
            padding: 5px 0;
        }

        nav ul li a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s;
        }

        nav ul li a:hover {
            color: var(--primary);
        }

        nav ul li a:hover:after {
            width: 100%;
        }

        .cta-button {
            background-color: var(--primary);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            border: 2px solid var(--primary);
            font-size: 0.9rem;
        }

        .cta-button:hover {
            background-color: transparent;
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        /* Hero Section */
        .hero {
            /* Hero background comes from profile hero_image (uploads/profiles/ or uploads/) */
            background: linear-gradient(rgba(26, 31, 37, 0.6), rgba(26, 31, 37, 0.75)), url('<?php echo htmlspecialchars($hero_bg, ENT_QUOTES, 'UTF-8'); ?>');
            background-size: cover;
            background-position: center;
            /* background-attachment: fixed can cause issues on mobile; use scroll for better cross-device behavior */
            background-attachment: scroll;
            color: white;
            padding: 180px 0 120px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(to bottom, transparent, var(--light));
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h2 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.5);
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        /* Mobile navigation toggle button (default hidden on desktop) */
        .nav-toggle {
            display: none;
            background: transparent;
            border: none;
            cursor: pointer;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            padding: 6px;
        }

        .nav-toggle span,
        .nav-toggle span:before,
        .nav-toggle span:after {
            display: block;
            background: white;
            height: 3px;
            width: 22px;
            border-radius: 2px;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-toggle span:before,
        .nav-toggle span:after {
            content: '';
            position: absolute;
            left: 0;
        }

        .nav-toggle span:before { top: -7px; }
        .nav-toggle span:after { top: 7px; }

        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background-color: white;
            color: var(--dark);
        }

        /* Adventure Stats */
        .adventure-stats {
            background-color: var(--secondary);
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            display: block;
            margin-bottom: 10px;
        }

        .stat-text {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Profile Section */
        .profile {
            padding: 100px 0;
            background-color: white;
            position: relative;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background-color: var(--primary);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .section-title p {
            color: #666;
            max-width: 700px;
            margin: 0 auto;
            font-size: 1.1rem;
        }

        .profile-content {
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .profile-img {
            flex: 1;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            transform: rotate(-2deg);
            transition: transform 0.5s;
        }

        .profile-img:hover {
            transform: rotate(0deg);
        }

        .profile-img img {
            width: 100%;
            height: auto;
            display: block;
            transition: transform 0.5s;
        }

        .profile-img:hover img {
            transform: scale(1.05);
        }

        .profile-text {
            flex: 1;
        }

        .profile-text h3 {
            font-size: 1.8rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .profile-text p {
            margin-bottom: 20px;
            color: #555;
            font-size: 1.05rem;
        }

        .adventure-features {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: var(--adventure-light);
            padding: 10px 15px;
            border-radius: 5px;
            flex: 1 1 calc(50% - 20px);
            min-width: 200px;
        }

        .feature i {
            color: var(--primary);
            font-size: 1.2rem;
        }

        /* Lava Tour Packages */
        .packages {
            padding: 100px 0;
            background: linear-gradient(to bottom, var(--light), #e6e9ec);
            position: relative;
        }

        .packages:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: linear-gradient(to right, var(--primary), var(--accent), var(--primary));
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .package-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #eaeaea;
        }

        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .package-badge {
            position: absolute;
            top: 20px;
            right: -30px;
            background-color: var(--primary);
            color: white;
            padding: 8px 40px;
            transform: rotate(45deg);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 2;
        }

        .package-img {
            height: 220px;
            overflow: hidden;
            position: relative;
        }

        .package-img:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
        }

        .package-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .package-card:hover .package-img img {
            transform: scale(1.1);
        }

        .package-content {
            padding: 25px;
        }

        .package-content h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--secondary);
        }

        .package-content p {
            color: #666;
            margin-bottom: 20px;
            min-height: 80px;
        }

        .package-features {
            margin-bottom: 20px;
        }

        .package-features ul {
            list-style: none;
        }

        .package-features li {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .package-features i {
            color: var(--primary);
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 20px;
            display: block;
        }

        .whatsapp-btn {
            background-color: #25D366;
            color: white;
            border: none;
            padding: 15px 20px;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .whatsapp-btn:hover {
            background-color: #128C7E;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3);
        }

        /* Gallery */
        .gallery {
            padding: 100px 0;
            background-color: var(--adventure-dark);
            color: white;
        }

        .gallery .section-title h2 {
            color: white;
        }

        .gallery .section-title p {
            color: #ccc;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .gallery-item {
            border-radius: 8px;
            overflow: hidden;
            height: 250px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.5s;
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* Contact */
        .contact {
            padding: 100px 0;
            background-color: white;
        }

        .contact-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 50px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
        }

        .contact-icon {
            background-color: var(--primary);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.2rem;
        }

        .contact-text h4 {
            margin-bottom: 8px;
            color: var(--secondary);
            font-size: 1.2rem;
        }

        .contact-text p {
            color: #555;
        }

        .map {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: 400px;
        }

        .map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* Footer */
        footer {
            background-color: var(--adventure-dark);
            color: white;
            padding: 80px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 25px;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-column p, .footer-column a {
            color: #ccc;
            margin-bottom: 12px;
            display: block;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-column a:hover {
            color: var(--primary);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background-color: var(--primary);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #aaa;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .profile-content {
                flex-direction: column;
            }
            
            .hero h2 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
            }

            /* Show mobile toggle and hide nav by default */
            .nav-toggle {
                display: flex;
            }

            nav {
                display: none;
                width: 100%;
            }

            nav.open {
                display: block;
            }

            nav ul {
                margin-top: 15px;
                flex-direction: column;
                gap: 10px;
                align-items: center;
            }

            nav ul li {
                margin: 5px 10px;
            }

            /* Hide the primary CTA in the header on mobile to save space (keep hero buttons visible) */
            header .cta-button {
                display: none;
            }

            .hero h2 {
                font-size: 2.2rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-buttons a {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
            
            .stats-container {
                flex-direction: column;
                gap: 30px;
            }

            /* Adjust package cards on smaller screens */
            .packages-grid {
                grid-template-columns: 1fr;
            }

            .gallery-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 120px 0 80px;
            }

            .hero h2 {
                font-size: 1.8rem;
            }

            .hero p {
                font-size: 1rem;
            }

            .packages-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .gallery-grid {
                grid-template-columns: 1fr;
            }

            .cta-button {
                padding: 10px 14px;
                font-size: 0.85rem;
            }
        }
        /* Floating WhatsApp button (fixed bottom-left) - slightly larger, proportionally adjusted */

        .whatsapp-float {
            position: fixed;
            left: 20px;
            bottom: 20px;
            z-index: 2000;
            --wa-color: 37,211,102; /* RGB for easier rgba usage */
            background-color: #25D366;
            color: white;
            width: 72px; /* increased size */
            height: 72px; /* increased size */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            /* multiple layered glow for stronger 'shine' */
            box-shadow: 0 10px 40px rgba(var(--wa-color),0.30), 0 0 0 8px rgba(var(--wa-color),0.06);
            transition: transform 0.12s ease, box-shadow 0.12s ease, opacity 0.12s ease, filter 0.12s ease;
            /* animations: bob + stronger glow pulse */
            animation: float 3.6s ease-in-out infinite, glowPulse 2.4s ease-in-out infinite;
            text-decoration: none; /* remove underline */
            -webkit-tap-highlight-color: transparent;
            backdrop-filter: blur(2px);
            will-change: transform, box-shadow, filter;
        }

        /* stronger soft halo behind the button */
        .whatsapp-float::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 104px; /* scaled with button */
            height: 104px;
            transform: translate(-50%, -50%) scale(0.95);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(var(--wa-color),0.34) 0%, rgba(var(--wa-color),0.14) 35%, rgba(var(--wa-color),0.03) 60%, rgba(0,0,0,0) 100%);
            z-index: 1998;
            filter: blur(7px) saturate(1.05);
            animation: haloBrighter 2.6s ease-out infinite;
        }

        /* subtle inner ripple for added depth */
        .whatsapp-float::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 72px; /* match button */
            height: 72px;
            transform: translate(-50%, -50%) scale(1);
            border-radius: 50%;
            background: rgba(var(--wa-color),0.12);
            z-index: 1999;
            animation: tinyPulse 2.4s ease-out infinite;
        }

        .whatsapp-float:hover {
            transform: translateY(-10px) scale(1.06);
            box-shadow: 0 26px 80px rgba(var(--wa-color),0.40), 0 0 48px rgba(var(--wa-color),0.26);
            filter: brightness(1.08) saturate(1.08);
        }

        .whatsapp-float i {
            font-size: 32px; /* increased icon size */
            position: relative;
            z-index: 2001;
            text-decoration: none; /* safety for icon text */
            filter: drop-shadow(0 6px 10px rgba(0,0,0,0.14));
        }

        @keyframes float {
            0% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0); }
        }

        @keyframes glowPulse {
            0% { box-shadow: 0 8px 26px rgba(var(--wa-color),0.20), 0 0 0 0 rgba(var(--wa-color),0.06); filter: brightness(1); }
            45% { box-shadow: 0 26px 80px rgba(var(--wa-color),0.40), 0 0 22px rgba(var(--wa-color),0.20); filter: brightness(1.12) saturate(1.06); }
            100% { box-shadow: 0 8px 26px rgba(var(--wa-color),0.20), 0 0 0 0 rgba(var(--wa-color),0.06); filter: brightness(1); }
        }

        @keyframes haloBrighter {
            0% { transform: translate(-50%, -50%) scale(0.9); opacity: 0.95; }
            60% { transform: translate(-50%, -50%) scale(2.1); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(2.1); opacity: 0; }
        }

        @keyframes tinyPulse {
            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.9; }
            60% { transform: translate(-50%, -50%) scale(1.35); opacity: 0; }
            100% { transform: translate(-50%, -50%) scale(1.35); opacity: 0; }
        }

        @media (max-width: 420px) {
            .whatsapp-float {
                left: 14px;
                bottom: 14px;
                width: 56px; /* slightly larger than before */
                height: 56px;
            }

            .whatsapp-float i {
                font-size: 24px; /* adjusted for mobile */
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header id="header">
        <div class="container header-container">
            <div class="header-left">
                <div class="logo">
                    <!--<img src="images/jeep-merapi.jpg" alt="Jeep Adventure Jogja">-->
                    <div>
                        <h1><?php echo $profile ? htmlspecialchars($profile['company_name']) : 'Jeep Adventure'; ?></h1>
                        <span><?php echo $profile ? htmlspecialchars($profile['company_tagline']) : 'JOGJA'; ?></span>
                    </div>
                </div>
                <!-- Mobile nav toggle (placed to the right of the logo) -->
                <button class="nav-toggle" aria-label="Toggle navigation">
                    <span></span>
                </button>
            </div>
            <nav>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#profile">Profile</a></li>
                    <li><a href="#packages">Paket Lava Tour</a></li>
                    <li><a href="#gallery">Galeri</a></li>
                    <li><a href="#contact">Kontak</a></li>
                </ul>
            </nav>
            <a href="#packages" class="cta-button">Pesan Sekarang</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container hero-content">
            <h2><?php echo $profile ? htmlspecialchars($profile['hero_title']) : 'Petualangan Ekstrem Lava Tour Merapi'; ?></h2>
            <p><?php echo $profile ? htmlspecialchars($profile['hero_description']) : 'Rasakan sensasi mendebarkan menjelajahi kawasan bekas erupsi Gunung Merapi dengan Jeep Adventure Jogja. Pengalaman tak terlupakan menanti Anda!'; ?></p>
            <div class="hero-buttons">
                <a href="#packages" class="cta-button">Jelajahi Paket</a>
                <a href="#gallery" class="cta-button btn-secondary">Lihat Galeri</a>
            </div>
        </div>
    </section>

    <!-- Adventure Stats -->
    <section class="adventure-stats">
        <div class="container stats-container">
            <div class="stat-item">
                <span class="stat-number"><?php echo $profile ? htmlspecialchars($profile['stat_customers']) : '5000+'; ?></span>
                <span class="stat-text">Petualang Bergabung</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $profile ? htmlspecialchars($profile['stat_experience']) : '8+'; ?></span>
                <span class="stat-text">Tahun Pengalaman</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $profile ? htmlspecialchars($profile['stat_satisfaction']) : '98%'; ?></span>
                <span class="stat-text">Kepuasan Pelanggan</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $profile ? htmlspecialchars($profile['stat_service']) : '24/7'; ?></span>
                <span class="stat-text">Layanan Pelanggan</span>
            </div>
        </div>
    </section>

    <!-- Profile Section -->
    <section class="profile" id="profile">
        <div class="container">
            <div class="section-title">
                <h2>Profil Petualangan Kami</h2>
                <p><?php echo $profile ? htmlspecialchars($profile['profile_description']) : 'Jeep Adventure Jogja adalah penyedia tur Jeep terkemuka dengan fokus pada pengalaman petualangan yang aman dan tak terlupakan'; ?></p>
            </div>
            <div class="profile-content">
                <div class="profile-img">
                    <?php
                    // Prefer uploads/profiles/ for profile images (admin uploads), fallback to uploads/
                    $profile_img_src = 'uploads/' . ($profile ? $profile['profile_image'] : 'jeep.jpeg');
                    if ($profile && !empty($profile['profile_image']) && file_exists(__DIR__ . '/uploads/profiles/' . $profile['profile_image'])) {
                        $profile_img_src = 'uploads/profiles/' . $profile['profile_image'];
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($profile_img_src); ?>" alt="Jeep Adventure Jogja">
                </div>
                <div class="profile-text">
                    <h3><?php echo $profile ? htmlspecialchars($profile['profile_title']) : 'Petualangan Sejati Dimulai Di Sini'; ?></h3>
                    <p><?php echo $profile ? htmlspecialchars($profile['profile_content1']) : 'Dengan lebih dari 8 tahun pengalaman, Jeep Adventure Jogja telah menjadi pilihan utama bagi para petualang yang ingin menjelajahi kawasan Gunung Merapi. Kami menawarkan pengalaman otentik dengan keamanan sebagai prioritas utama.'; ?></p>
                    <p><?php echo $profile ? htmlspecialchars($profile['profile_content2']) : 'Tim pemandu profesional kami tidak hanya berpengalaman dalam mengemudi di medan yang menantang, tetapi juga memiliki pengetahuan mendalam tentang sejarah dan geologi Gunung Merapi.'; ?></p>
                    
                    <div class="adventure-features">
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <span><?php echo $profile ? htmlspecialchars($profile['feature1']) : 'Perlengkapan Keamanan Lengkap'; ?></span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-camera"></i>
                            <span><?php echo $profile ? htmlspecialchars($profile['feature2']) : 'Dokumentasi Profesional'; ?></span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-user-check"></i>
                            <span><?php echo $profile ? htmlspecialchars($profile['feature3']) : 'Pemandu Berpengalaman'; ?></span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-car"></i>
                            <span><?php echo $profile ? htmlspecialchars($profile['feature4']) : 'Armada Jeep Terawat'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Lava Tour Packages -->
    <section class="packages" id="packages">
        <div class="container">
            <div class="section-title">
                <h2>Paket Petualangan Lava Tour</h2>
                <p>Pilih paket petualangan sesuai jiwa petualang Anda. Semua paket sudah termasuk pemandu profesional, asuransi, dan perlengkapan keamanan.</p>
            </div>
            <div class="packages-grid">
                <?php if ($packages): ?>
                    <?php foreach ($packages as $package): ?>
                        <div class="package-card">
                            <?php if ($package['badge']): ?>
                                <div class="package-badge"><?php echo htmlspecialchars($package['badge']); ?></div>
                            <?php endif; ?>
                            <div class="package-img">
                                <?php
                                // Prefer uploads/packages/ for package images
                                $package_img = 'uploads/' . $package['image'];
                                if (!empty($package['image']) && file_exists(__DIR__ . '/uploads/packages/' . $package['image'])) {
                                    $package_img = 'uploads/packages/' . $package['image'];
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($package_img); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>">
                            </div>
                            <div class="package-content">
                                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                <p><?php echo htmlspecialchars($package['description']); ?></p>
                                
                                <div class="package-features">
                                    <ul>
                                        <?php
                                        $features = explode("\n", $package['features']);
                                        foreach ($features as $feature):
                                            if (!empty(trim($feature))):
                                        ?>
                                        <li><i class="fas fa-check"></i> <?php echo htmlspecialchars(trim($feature)); ?></li>
                                        <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </ul>
                                </div>
                                
                                <span class="price">Rp <?php echo number_format($package['price'], 0, ',', '.'); ?> / Jeep</span>
                                <button class="whatsapp-btn" onclick="orderPackage('<?php echo htmlspecialchars($package['name']); ?>', <?php echo $package['price']; ?>)">
                                    <i class="fab fa-whatsapp"></i> Pesan via WhatsApp
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada paket tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Gallery -->
    <section class="gallery" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Galeri Petualangan</h2>
                <p>Lihat momen-momen menakjubkan dari petualangan Lava Tour bersama Jeep Adventure Jogja</p>
            </div>
            <div class="gallery-grid">
                <?php if ($gallery): ?>
                    <?php foreach ($gallery as $item): ?>
                        <div class="gallery-item">
                            <?php
                            // Prefer uploads/gallery/ for gallery images
                            $gallery_img = 'uploads/' . $item['image'];
                            if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/gallery/' . $item['image'])) {
                                $gallery_img = 'uploads/gallery/' . $item['image'];
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($gallery_img); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="gallery-overlay">
                                <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Tidak ada gambar galeri tersedia saat ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Hubungi Petualangan</h2>
                <p>Jangan ragu untuk menghubungi kami untuk informasi lebih lanjut atau pemesanan paket petualangan</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Markas Petualangan</h4>
                            <p><?php echo $contact ? htmlspecialchars($contact['address']) : 'Jl. Kaliurang KM 22, Sleman, Yogyakarta'; ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Telepon/WhatsApp</h4>
                            <p><?php echo $contact ? htmlspecialchars($contact['phone']) : '+62 895-0779-6522'; ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email Petualangan</h4>
                            <p><?php echo $contact ? htmlspecialchars($contact['email']) : 'adventure@jeepjogja.com'; ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Jam Operasional</h4>
                            <p><?php echo $contact ? htmlspecialchars($contact['operating_hours']) : 'Setiap hari: 06.00 - 22.00 WIB'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="map">
                    <iframe src="<?php echo $contact ? htmlspecialchars($contact['map_embed']) : 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3953.320963366209!2d110.4243144747604!3d-7.759950477043119!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a599a0272ebad%3A0x5c7c7b83e9c16f0!2sMount%20Merapi!5e0!3m2!1sen!2sid!4v1690123456789!5m2!1sen!2sid'; ?>" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3><?php echo $profile ? htmlspecialchars($profile['company_name']) : 'Jeep Adventure Jogja'; ?></h3>
                    <p><?php echo $profile ? htmlspecialchars($profile['footer_description']) : 'Penyedia pengalaman petualangan Lava Tour terbaik di kawasan Gunung Merapi dengan layanan profesional dan jiwa petualang.'; ?></p>
                    <div class="social-links">
                        <a href="<?php echo $contact ? htmlspecialchars($contact['facebook']) : '#'; ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo $contact ? htmlspecialchars($contact['instagram']) : '#'; ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?php echo $contact ? htmlspecialchars($contact['tiktok']) : 'https://vt.tiktok.com/ZSUu7acdb/'; ?>"><i class="fab fa-tiktok"></i></a>
                        <a href="https://wa.me/<?php echo $contact ? preg_replace('/[^0-9]/', '', $contact['phone']) : '6289507796522'; ?>"><i class="fab fa-whatsapp"></i></a>
                        <a href="<?php echo $contact ? htmlspecialchars($contact['youtube']) : '#'; ?>"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Paket Petualangan</h3>
                    <?php if ($packages): ?>
                        <?php foreach ($packages as $package): ?>
                            <a href="#packages"><?php echo htmlspecialchars($package['name']); ?></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a href="#packages">Paket Kustom</a>
                </div>
                <div class="footer-column">
                    <h3>Tautan Petualangan</h3>
                    <a href="#home">Home</a>
                    <a href="#profile">Profil Kami</a>
                    <a href="#gallery">Galeri Petualangan</a>
                    <a href="#contact">Hubungi Kami</a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $profile ? htmlspecialchars($profile['company_name']) : 'Jeep Adventure Jogja'; ?>. All Rights Reserved. | Jiwa Petualang Tak Pernah Padam | By PrenjakAlangAlang</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp button -->
    <a class="whatsapp-float" href="https://wa.me/<?php echo $contact ? preg_replace('/[^0-9]/', '', $contact['phone']) : '6289507796522'; ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat via WhatsApp">
        <i class="fab fa-whatsapp" aria-hidden="true"></i>
    </a>

    <script>
        // WhatsApp order function
        function orderPackage(packageName, price) {
            const phoneNumber = "<?php echo $contact ? preg_replace('/[^0-9]/', '', $contact['phone']) : '6289507796522'; ?>";
            const message = `Halo <?php echo $profile ? htmlspecialchars($profile['company_name']) : 'Jeep Adventure Jogja'; ?>! Saya ingin memesan ${packageName} seharga Rp ${price.toLocaleString('id-ID')}. 
            
Bisa tolong informasikan:
1. Tanggal ketersediaan
2. Proses pemesanan dan pembayaran
3. Apa saja yang termasuk dalam paket ini
4. Persyaratan yang perlu dipersiapkan

Terima kasih, saya tunggu info lebih lanjut!`;
            
            const encodedMessage = encodeURIComponent(message);
            const whatsappURL = `https://wa.me/${phoneNumber}?text=${encodedMessage}`;
            
            window.open(whatsappURL, '_blank');
        }

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Mobile nav toggle
        (function() {
            const navToggle = document.querySelector('.nav-toggle');
            const nav = document.querySelector('nav');

            if (!navToggle || !nav) return;

            navToggle.addEventListener('click', function() {
                nav.classList.toggle('open');
                this.classList.toggle('open');
            });

            // Close mobile nav when clicking a link
            nav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    if (nav.classList.contains('open')) {
                        nav.classList.remove('open');
                    }
                });
            });
        })();
    </script>
</body>
</html>