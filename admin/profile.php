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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .sidebar { width: 250px; background: #1A3C40; color: white; height: 100vh; position: fixed; padding: 20px 0; }
        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar-header h2 { color: #FF6B35; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li a { display: block; padding: 12px 20px; color: white; text-decoration: none; transition: background 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: #FF6B35; }
        .sidebar-menu li a i { margin-right: 10px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn { background: #FF6B35; color: white; border: none; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #e55a2b; }
        .logout { background: #dc3545; }
        .logout:hover { background: #c82333; }
        .message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .form-section h3 { color: #1A3C40; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #FF6B35; }
        .image-preview { max-width: 200px; margin-top: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Jeep Adventure</h2>
            <p>Admin Panel</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-building"></i> Profil Perusahaan</a></li>
            <li><a href="admins.php"><i class="fas fa-users-cog"></i> Admins</a></li>
            <li><a href="packages.php"><i class="fas fa-box"></i> Paket Tour</a></li>
            <li><a href="gallery.php"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a></li>
            <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
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
                    <div class="form-group">
                        <label>Gambar Hero</label>
                        <input type="file" name="hero_image" accept="image/*">
                        <?php if ($profile && !empty($profile['hero_image'])): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile['hero_image']); ?>" class="image-preview">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Statistik -->
                <div class="form-section">
                    <h3>Statistik</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
                    <div class="form-group">
                        <label>Gambar Profil</label>
                        <input type="file" name="profile_image" accept="image/*">
                        <?php if ($profile && !empty($profile['profile_image'])): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile['profile_image']); ?>" class="image-preview">
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section Fitur -->
                <div class="form-section">
                    <h3>Fitur Perusahaan</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const preview = this.nextElementSibling;
                    if (preview && preview.tagName === 'IMG') {
                        previewImage(this, preview);
                    }
                });
            });
        });
    </script>
</body>
</html>