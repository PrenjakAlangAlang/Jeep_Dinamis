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

// Require id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: packages.php');
    exit;
}

$id = (int)$_GET['id'];

// Ambil data paket
$query = "SELECT * FROM packages WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    header('Location: packages.php');
    exit;
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $features = sanitize($_POST['features'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $badge = sanitize($_POST['badge'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Gambar lama
    $image = $package['image'];
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], '../uploads/packages/');
        if ($upload['success']) {
            $image = $upload['filename'];
            // hapus gambar lama jika ada
            if (!empty($package['image']) && file_exists('../uploads/packages/' . $package['image'])) {
                @unlink('../uploads/packages/' . $package['image']);
            }
        } else {
            $error = $upload['message'];
        }
    }

    if (empty($error)) {
        $query = "UPDATE packages SET
            name = :name,
            description = :description,
            features = :features,
            price = :price,
            image = :image,
            badge = :badge,
            is_active = :is_active
            WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':features', $features);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':badge', $badge);
        $stmt->bindParam(':is_active', $is_active);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            $message = 'Paket berhasil diperbarui!';
            // reload data
            $query = "SELECT * FROM packages WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Gagal memperbarui paket!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Paket - Jeep Adventure</title>
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
        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
        }
        .card { 
            background: #fff; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 6px 20px rgba(0,0,0,0.06); 
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 6px; 
            font-weight: 600; 
        }
        input[type=text], 
        input[type=number], 
        textarea, 
        select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            font-family: inherit;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .btn { 
            background: #FF6B35; 
            color: #fff; 
            padding: 10px 14px; 
            border-radius: 6px; 
            border: none; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .btn:hover { 
            background: #e55a2b; 
        }
        .btn-secondary { 
            background: #6c757d; 
        }
        .btn-secondary:hover {
            background: #5a6268;
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
            border-radius: 6px; 
            margin-bottom: 15px; 
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px; 
            border-radius: 6px; 
            margin-bottom: 15px; 
        }
        .image-preview { 
            max-width: 100%; 
            margin-top: 8px; 
            border-radius: 6px; 
            border: 1px solid #ddd;
        }
        .top-actions { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-layout {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 20px;
            align-items: start;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 15px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content { 
                padding: 80px 14px 14px; 
            }
            .container {
                max-width: 100%;
            }
            .card {
                padding: 15px;
            }
            .top-actions { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 12px;
                padding: 15px;
            }
            .btn { 
                padding: 8px 12px; 
                font-size: 14px;
            }
            .form-layout {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .image-preview {
                max-width: 200px;
                margin: 0 auto;
                display: block;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 80px 10px 10px;
            }
            .top-actions {
                padding: 12px;
            }
            .card {
                padding: 12px;
            }
            .form-group {
                margin-bottom: 12px;
            }
            input[type=text], 
            input[type=number], 
            textarea, 
            select {
                padding: 8px 10px;
                font-size: 14px;
            }
            .btn {
                padding: 8px 10px;
                font-size: 13px;
                width: 100%;
                text-align: center;
            }
            .button-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
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
            <a href="packages.php" class="active"><i class="fas fa-box"></i> Paket</a>
            <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="container">
            <div class="top-actions">
                <h1>Edit Paket</h1>
                <div>
                    <a href="packages.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Paket
                    </a>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data" action="">
                    <div class="form-group">
                        <label>Nama Paket *</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Harga *</label>
                        <input type="number" name="price" value="<?php echo (int)$package['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi *</label>
                        <textarea name="description" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Fitur (pisahkan dengan enter) *</label>
                        <textarea name="features" placeholder="Contoh:&#10;Durasi: 3-4 jam&#10;Maksimal 4 orang/Jeep&#10;Pemandu profesional"><?php echo htmlspecialchars($package['features']); ?></textarea>
                    </div>
                    
                    <div class="form-layout">
                        <div>
                            <div class="form-group">
                                <label>Badge</label>
                                <select name="badge">
                                    <option value="" <?php echo $package['badge']==''? 'selected':''; ?>>Tidak ada</option>
                                    <option value="Populer" <?php echo $package['badge']=='Populer'? 'selected':''; ?>>Populer</option>
                                    <option value="Favorit" <?php echo $package['badge']=='Favorit'? 'selected':''; ?>>Favorit</option>
                                    <option value="Eksklusif" <?php echo $package['badge']=='Eksklusif'? 'selected':''; ?>>Eksklusif</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Ganti Gambar (optional)</label>
                                <input type="file" name="image" accept="image/*">
                            </div>

                            <div class="checkbox-group">
                                <input type="checkbox" name="is_active" value="1" id="is_active" <?php echo $package['is_active'] ? 'checked' : ''; ?>>
                                <label for="is_active" style="margin: 0; font-weight: normal;">Aktifkan paket</label>
                            </div>
                            
                            <div class="button-group">
                                <button type="submit" class="btn">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label>Preview Gambar Saat Ini</label>
                            <?php if (!empty($package['image']) && file_exists('../uploads/packages/' . $package['image'])): ?>
                                <img src="../uploads/packages/<?php echo htmlspecialchars($package['image']); ?>" 
                                     class="image-preview" 
                                     alt="<?php echo htmlspecialchars($package['name']); ?>">
                            <?php else: ?>
                                <div style="color:#666;padding:14px;background:#f8f9fa;border-radius:6px;text-align:center">
                                    <i class="fas fa-image" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                                    Tidak ada gambar
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
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

        // File upload validation and preview
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('input[name="image"]');
            const imagePreview = document.querySelector('.image-preview');
            
            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
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
                        
                        // Preview gambar baru
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            if (imagePreview && imagePreview.tagName === 'IMG') {
                                imagePreview.src = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>