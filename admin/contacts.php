<?php
session_start();
require_once "../config/database.php";
require_once "../includes/functions.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Ambil data kontak
$query = "SELECT * FROM contacts WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

// Update kontak
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = sanitize($_POST['address']);
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $operating_hours = sanitize($_POST['operating_hours']);
    $map_embed = sanitize($_POST['map_embed']);
    $facebook = sanitize($_POST['facebook']);
    $instagram = sanitize($_POST['instagram']);
    $tiktok = sanitize($_POST['tiktok']);
    $youtube = sanitize($_POST['youtube']);
    
    if ($contact) {
        // Update data yang sudah ada
        $query = "UPDATE contacts SET 
            address = :address,
            phone = :phone,
            email = :email,
            operating_hours = :operating_hours,
            map_embed = :map_embed,
            facebook = :facebook,
            instagram = :instagram,
            tiktok = :tiktok,
            youtube = :youtube,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = 1";
    } else {
        // Insert data baru
        $query = "INSERT INTO contacts (
            address, phone, email, operating_hours, map_embed,
            facebook, instagram, tiktok, youtube
        ) VALUES (
            :address, :phone, :email, :operating_hours, :map_embed,
            :facebook, :instagram, :tiktok, :youtube
        )";
    }
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':operating_hours', $operating_hours);
    $stmt->bindParam(':map_embed', $map_embed);
    $stmt->bindParam(':facebook', $facebook);
    $stmt->bindParam(':instagram', $instagram);
    $stmt->bindParam(':tiktok', $tiktok);
    $stmt->bindParam(':youtube', $youtube);
    
    if ($stmt->execute()) {
        $message = "Informasi kontak berhasil diperbarui!";
        // Reload data
        $query = "SELECT * FROM contacts WHERE id = 1";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Gagal memperbarui informasi kontak!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kontak - Jeep Adventure Jogja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
        }
        .topnav {
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            height: 64px; 
            background: #1A3C40; 
            color: white; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 8px 20px; 
            z-index: 1200;
        }
        .topnav .brand { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .topnav .brand h2 { 
            color: #FF6B35; 
            margin: 0; 
            font-size: 18px; 
        }
        .topnav .menu { 
            display: flex; 
            gap: 6px; 
            align-items: center; 
        }
        .topnav .menu a { 
            color: white; 
            text-decoration: none; 
            padding: 8px 12px; 
            border-radius: 6px; 
            font-weight: 600; 
        }
        .topnav .menu a.active, 
        .topnav .menu a:hover { 
            background: #FF6B35; 
        }
        
        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }
        .hamburger span {
            height: 3px;
            width: 25px;
            background: white;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }
        
        /* Responsive Styles */
        @media (max-width: 900px) {
            .topnav .menu {
                position: fixed;
                top: 64px;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: calc(100vh - 64px);
                background: #1A3C40;
                flex-direction: column;
                align-items: flex-start;
                padding: 20px;
                transition: right 0.3s ease;
                box-shadow: -5px 0 15px rgba(0,0,0,0.1);
                gap: 0;
                overflow-y: auto;
            }
            
            .topnav .menu.active {
                right: 0;
            }
            
            .topnav .menu a {
                width: 100%;
                padding: 12px 15px;
                margin-bottom: 5px;
                border-radius: 5px;
                display: flex;
                align-items: center;
            }
            
            .topnav .menu a i {
                width: 20px;
                text-align: center;
                margin-right: 10px;
            }
            
            .hamburger {
                display: flex;
            }
            
            /* Hamburger Animation */
            .hamburger.active span:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }
            
            .hamburger.active span:nth-child(2) {
                opacity: 0;
            }
            
            .hamburger.active span:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }
        
        @media (max-width: 480px) {
            .topnav {
                padding: 8px 15px;
            }
            
            .topnav .brand h2 {
                font-size: 16px;
            }
            
            .topnav .brand small {
                font-size: 12px;
            }
        }
        
        .main-content { 
            padding: 90px 20px 20px; 
        }
        .header { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            margin-bottom: 20px; 
        }
        .btn { 
            background: #FF6B35; 
            color: white; 
            border: none; 
            padding: 10px 15px; 
            border-radius: 5px; 
            text-decoration: none; 
            display: inline-block; 
            cursor: pointer; 
            transition: background 0.3s; 
        }
        .btn:hover { 
            background: #e55a2b; 
        }
        .logout { 
            background: #dc3545; 
        }
        .logout:hover { 
            background: #c82333; 
        }
        .message { 
            background: #d4edda; 
            color: #155724; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .form-container { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #333; 
        }
        .form-group input, 
        .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-family: inherit; 
        }
        .form-group textarea { 
            min-height: 100px; 
            resize: vertical; 
        }
        .form-section { 
            margin-bottom: 30px; 
            padding-bottom: 20px; 
            border-bottom: 1px solid #eee; 
        }
        .form-section h3 { 
            color: #1A3C40; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #FF6B35; 
        }
        .social-preview { 
            display: flex; 
            gap: 10px; 
            margin-top: 10px; 
        }
        .social-preview a { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            width: 40px; 
            height: 40px; 
            background-color: #1A3C40; 
            border-radius: 50%; 
            color: white; 
            text-decoration: none; 
            transition: background 0.3s;
        }
        .social-preview a:hover {
            background: #FF6B35;
        }
        .map-preview { 
            margin-top: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            overflow: hidden; 
        }
        .map-preview iframe {
            width: 100%;
            height: 300px;
            border: none;
        }

        /* Responsive helpers */
        .table-wrap { 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
        }

        @media (max-width: 800px) {
            .main-content { 
                margin-left: 0; 
                padding: 80px 14px 14px; 
            }
            .form-container { 
                padding: 14px; 
            }
            .btn { 
                padding: 8px 10px; 
                font-size: 14px; 
            }
            .form-section { 
                padding-bottom: 10px; 
            }
            .form-group {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr !important;
                gap: 0 !important;
            }
            .social-preview {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="topnav">
        <div class="brand">
            <h2>Jeep Merapi Tripster</h2>
            <small style="color:#fff;opacity:0.85">Admin Panel</small>
        </div>
        
        <!-- Hamburger Menu Icon -->
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        
        <nav class="menu" id="menu">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-building"></i> Profil</a>
            <a href="admins.php"><i class="fas fa-users-cog"></i> Admins</a>
            <a href="packages.php"><i class="fas fa-box"></i> Paket</a>
            <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php" class="active"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="header">
            <h1>Kelola Informasi Kontak</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="">
                <!-- Section Informasi Kontak -->
                <div class="form-section">
                    <h3>Informasi Kontak</h3>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="address"><?php echo $contact ? htmlspecialchars($contact['address']) : ''; ?></textarea>
                    </div>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Telepon/WhatsApp</label>
                            <input type="text" name="phone" value="<?php echo $contact ? htmlspecialchars($contact['phone']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo $contact ? htmlspecialchars($contact['email']) : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jam Operasional</label>
                        <input type="text" name="operating_hours" value="<?php echo $contact ? htmlspecialchars($contact['operating_hours']) : ''; ?>">
                    </div>
                </div>

                <!-- Section Peta -->
                <div class="form-section">
                    <h3>Embed Peta</h3>
                    <div class="form-group">
                        <label>Kode Embed Google Maps</label>
                        <textarea name="map_embed" placeholder='&lt;iframe src="https://www.google.com/maps/embed?pb=..."&gt;&lt;/iframe&gt;'><?php echo $contact ? htmlspecialchars($contact['map_embed']) : ''; ?></textarea>
                    </div>
                    <?php if ($contact && !empty($contact['map_embed'])): ?>
                    <div class="map-preview">
                        <?php echo $contact['map_embed']; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Section Media Sosial -->
                <div class="form-section">
                    <h3>Media Sosial</h3>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Facebook URL</label>
                            <input type="url" name="facebook" value="<?php echo $contact ? htmlspecialchars($contact['facebook']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Instagram URL</label>
                            <input type="url" name="instagram" value="<?php echo $contact ? htmlspecialchars($contact['instagram']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>TikTok URL</label>
                            <input type="url" name="tiktok" value="<?php echo $contact ? htmlspecialchars($contact['tiktok']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>YouTube URL</label>
                            <input type="url" name="youtube" value="<?php echo $contact ? htmlspecialchars($contact['youtube']) : ''; ?>">
                        </div>
                    </div>
                    
                    <?php if ($contact): ?>
                    <div class="social-preview">
                        <?php if (!empty($contact['facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($contact['facebook']); ?>" target="_blank">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contact['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($contact['instagram']); ?>" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contact['tiktok'])): ?>
                            <a href="<?php echo htmlspecialchars($contact['tiktok']); ?>" target="_blank">
                                <i class="fab fa-tiktok"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($contact['youtube'])): ?>
                            <a href="<?php echo htmlspecialchars($contact['youtube']); ?>" target="_blank">
                                <i class="fab fa-youtube"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        // Toggle mobile menu
        const hamburger = document.getElementById('hamburger');
        const menu = document.getElementById('menu');
        
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            menu.classList.toggle('active');
        });
        
        // Close menu when clicking on a link
        const menuLinks = document.querySelectorAll('.menu a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                menu.classList.remove('active');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInsideMenu = menu.contains(event.target);
            const isClickInsideHamburger = hamburger.contains(event.target);
            
            if (!isClickInsideMenu && !isClickInsideHamburger && menu.classList.contains('active')) {
                hamburger.classList.remove('active');
                menu.classList.remove('active');
            }
        });

        // Preview untuk embed map
        function updateMapPreview() {
            const embedCode = document.querySelector('textarea[name="map_embed"]').value;
            const preview = document.querySelector('.map-preview');
            
            if (embedCode && preview) {
                preview.innerHTML = embedCode;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const mapTextarea = document.querySelector('textarea[name="map_embed"]');
            if (mapTextarea) {
                mapTextarea.addEventListener('input', updateMapPreview);
            }
        });
    </script>
</body>
</html>