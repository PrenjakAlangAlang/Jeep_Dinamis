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

// Ambil data profil
$query = "SELECT * FROM profiles WHERE id = 1";
$stmt = $db->prepare($query);
$stmt->execute();
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = sanitize($_POST['company_name']);
    $company_tagline = sanitize($_POST['company_tagline']);
    $hero_title = sanitize($_POST['hero_title']);
    $hero_description = sanitize($_POST['hero_description']);
    $stat_customers = sanitize($_POST['stat_customers']);
    $stat_experience = sanitize($_POST['stat_experience']);
    $stat_satisfaction = sanitize($_POST['stat_satisfaction']);
    $stat_service = sanitize($_POST['stat_service']);
    $profile_title = sanitize($_POST['profile_title']);
    $profile_description = sanitize($_POST['profile_description']);
    $profile_content1 = sanitize($_POST['profile_content1']);
    $profile_content2 = sanitize($_POST['profile_content2']);
    $feature1 = sanitize($_POST['feature1']);
    $feature2 = sanitize($_POST['feature2']);
    $feature3 = sanitize($_POST['feature3']);
    $feature4 = sanitize($_POST['feature4']);
    $footer_description = sanitize($_POST['footer_description']);
    
    // Upload gambar hero
    $hero_image = $profile['hero_image'];
    if (!empty($_FILES['hero_image']['name'])) {
        $upload = uploadImage($_FILES['hero_image'], '../uploads/profiles/');
        if ($upload['success']) {
            $hero_image = $upload['filename'];
            // Hapus gambar lama jika ada
            if (!empty($profile['hero_image']) && file_exists('../uploads/profiles/' . $profile['hero_image'])) {
                unlink('../uploads/profiles/' . $profile['hero_image']);
            }
        } else {
            $error = $upload['message'];
        }
    }
    
    // Upload gambar profil
    $profile_image = $profile['profile_image'];
    if (!empty($_FILES['profile_image']['name'])) {
        $upload = uploadImage($_FILES['profile_image'], '../uploads/profiles/');
        if ($upload['success']) {
            $profile_image = $upload['filename'];
            // Hapus gambar lama jika ada
            if (!empty($profile['profile_image']) && file_exists('../uploads/profiles/' . $profile['profile_image'])) {
                unlink('../uploads/profiles/' . $profile['profile_image']);
            }
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error)) {
        if ($profile) {
            // Update data yang sudah ada
            $query = "UPDATE profiles SET 
                company_name = :company_name,
                company_tagline = :company_tagline,
                hero_title = :hero_title,
                hero_description = :hero_description,
                hero_image = :hero_image,
                stat_customers = :stat_customers,
                stat_experience = :stat_experience,
                stat_satisfaction = :stat_satisfaction,
                stat_service = :stat_service,
                profile_title = :profile_title,
                profile_description = :profile_description,
                profile_content1 = :profile_content1,
                profile_content2 = :profile_content2,
                profile_image = :profile_image,
                feature1 = :feature1,
                feature2 = :feature2,
                feature3 = :feature3,
                feature4 = :feature4,
                footer_description = :footer_description,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = 1";
        } else {
            // Insert data baru
            $query = "INSERT INTO profiles (
                company_name, company_tagline, hero_title, hero_description, hero_image,
                stat_customers, stat_experience, stat_satisfaction, stat_service,
                profile_title, profile_description, profile_content1, profile_content2, profile_image,
                feature1, feature2, feature3, feature4, footer_description
            ) VALUES (
                :company_name, :company_tagline, :hero_title, :hero_description, :hero_image,
                :stat_customers, :stat_experience, :stat_satisfaction, :stat_service,
                :profile_title, :profile_description, :profile_content1, :profile_content2, :profile_image,
                :feature1, :feature2, :feature3, :feature4, :footer_description
            )";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':company_tagline', $company_tagline);
        $stmt->bindParam(':hero_title', $hero_title);
        $stmt->bindParam(':hero_description', $hero_description);
        $stmt->bindParam(':hero_image', $hero_image);
        $stmt->bindParam(':stat_customers', $stat_customers);
        $stmt->bindParam(':stat_experience', $stat_experience);
        $stmt->bindParam(':stat_satisfaction', $stat_satisfaction);
        $stmt->bindParam(':stat_service', $stat_service);
        $stmt->bindParam(':profile_title', $profile_title);
        $stmt->bindParam(':profile_description', $profile_description);
        $stmt->bindParam(':profile_content1', $profile_content1);
        $stmt->bindParam(':profile_content2', $profile_content2);
        $stmt->bindParam(':profile_image', $profile_image);
        $stmt->bindParam(':feature1', $feature1);
        $stmt->bindParam(':feature2', $feature2);
        $stmt->bindParam(':feature3', $feature3);
        $stmt->bindParam(':feature4', $feature4);
        $stmt->bindParam(':footer_description', $footer_description);
        
        if ($stmt->execute()) {
            $message = "Profil berhasil diperbarui!";
            // Reload data
            $query = "SELECT * FROM profiles WHERE id = 1";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Gagal memperbarui profil!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Profil - Jeep Adventure Jogja</title>
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
        .form-group textarea, 
        .form-group select { 
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
        .image-preview { 
            max-width: 200px; 
            margin-top: 10px; 
            border-radius: 5px; 
            border: 1px solid #ddd;
        }
        .image-upload-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .image-upload-container .form-group {
            flex: 1;
        }
        
        /* Responsive helpers */
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
            .form-grid {
                grid-template-columns: 1fr !important;
                gap: 0 !important;
            }
            .image-upload-container {
                flex-direction: column;
                gap: 10px;
            }
            .image-preview {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .form-section {
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .form-group {
                margin-bottom: 15px;
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
            <a href="profile.php" class="active"><i class="fas fa-building"></i> Profil</a>
            <a href="admins.php"><i class="fas fa-users-cog"></i> Admins</a>
            <a href="packages.php"><i class="fas fa-box"></i> Paket</a>
            <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="header">
            <h1>Kelola Profil Perusahaan</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Section Informasi Perusahaan -->
                <div class="form-section">
                    <h3>Informasi Perusahaan</h3>
                    <div class="form-group">
                        <label>Nama Perusahaan</label>
                        <input type="text" name="company_name" value="<?php echo $profile ? htmlspecialchars($profile['company_name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" name="company_tagline" value="<?php echo $profile ? htmlspecialchars($profile['company_tagline']) : ''; ?>">
                    </div>
                </div>

                <!-- Section Hero -->
                <div class="form-section">
                    <h3>Hero Section</h3>
                    <div class="form-group">
                        <label>Judul Hero</label>
                        <input type="text" name="hero_title" value="<?php echo $profile ? htmlspecialchars($profile['hero_title']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Hero</label>
                        <textarea name="hero_description" required><?php echo $profile ? htmlspecialchars($profile['hero_description']) : ''; ?></textarea>
                    </div>
                    <div class="image-upload-container">
                        <div class="form-group">
                            <label>Gambar Hero</label>
                            <input type="file" name="hero_image" accept="image/*">
                        </div>
                        <?php if ($profile && !empty($profile['hero_image'])): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile['hero_image']); ?>" class="image-preview" id="hero-preview">
                        <?php else: ?>
                            <img src="" class="image-preview" id="hero-preview" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Statistik -->
                <div class="form-section">
                    <h3>Statistik</h3>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Jumlah Pelanggan</label>
                            <input type="text" name="stat_customers" value="<?php echo $profile ? htmlspecialchars($profile['stat_customers']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Pengalaman (Tahun)</label>
                            <input type="text" name="stat_experience" value="<?php echo $profile ? htmlspecialchars($profile['stat_experience']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kepuasan Pelanggan</label>
                            <input type="text" name="stat_satisfaction" value="<?php echo $profile ? htmlspecialchars($profile['stat_satisfaction']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Layanan</label>
                            <input type="text" name="stat_service" value="<?php echo $profile ? htmlspecialchars($profile['stat_service']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Profil -->
                <div class="form-section">
                    <h3>Profil Perusahaan</h3>
                    <div class="form-group">
                        <label>Judul Profil</label>
                        <input type="text" name="profile_title" value="<?php echo $profile ? htmlspecialchars($profile['profile_title']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Singkat Profil</label>
                        <textarea name="profile_description"><?php echo $profile ? htmlspecialchars($profile['profile_description']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Konten Profil 1</label>
                        <textarea name="profile_content1"><?php echo $profile ? htmlspecialchars($profile['profile_content1']) : ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Konten Profil 2</label>
                        <textarea name="profile_content2"><?php echo $profile ? htmlspecialchars($profile['profile_content2']) : ''; ?></textarea>
                    </div>
                    <div class="image-upload-container">
                        <div class="form-group">
                            <label>Gambar Profil</label>
                            <input type="file" name="profile_image" accept="image/*">
                        </div>
                        <?php if ($profile && !empty($profile['profile_image'])): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile['profile_image']); ?>" class="image-preview" id="profile-preview">
                        <?php else: ?>
                            <img src="" class="image-preview" id="profile-preview" style="display: none;">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Fitur -->
                <div class="form-section">
                    <h3>Fitur Perusahaan</h3>
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Fitur 1</label>
                            <input type="text" name="feature1" value="<?php echo $profile ? htmlspecialchars($profile['feature1']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fitur 2</label>
                            <input type="text" name="feature2" value="<?php echo $profile ? htmlspecialchars($profile['feature2']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fitur 3</label>
                            <input type="text" name="feature3" value="<?php echo $profile ? htmlspecialchars($profile['feature3']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Fitur 4</label>
                            <input type="text" name="feature4" value="<?php echo $profile ? htmlspecialchars($profile['feature4']) : ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Section Footer -->
                <div class="form-section">
                    <h3>Footer</h3>
                    <div class="form-group">
                        <label>Deskripsi Footer</label>
                        <textarea name="footer_description"><?php echo $profile ? htmlspecialchars($profile['footer_description']) : ''; ?></textarea>
                    </div>
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

        // Preview image sebelum upload
        function previewImage(input, previewElement) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.src = e.target.result;
                    previewElement.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Setup preview untuk semua file inputs
        document.addEventListener('DOMContentLoaded', function() {
            const heroInput = document.querySelector('input[name="hero_image"]');
            const profileInput = document.querySelector('input[name="profile_image"]');
            const heroPreview = document.getElementById('hero-preview');
            const profilePreview = document.getElementById('profile-preview');
            
            if (heroInput && heroPreview) {
                heroInput.addEventListener('change', function() {
                    previewImage(this, heroPreview);
                });
            }
            
            if (profileInput && profilePreview) {
                profileInput.addEventListener('change', function() {
                    previewImage(this, profilePreview);
                });
            }

            // Validasi file upload
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Validasi ukuran file (max 5MB)
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Ukuran file maksimal 5MB');
                            e.target.value = '';
                            return;
                        }
                        
                        // Validasi tipe file
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        if (!validTypes.includes(file.type)) {
                            alert('Hanya file gambar (JPEG, PNG, GIF, WebP) yang diizinkan');
                            e.target.value = '';
                            return;
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>