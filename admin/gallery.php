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

// Tambah gambar baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_gallery'])) {
    $title = sanitize($_POST['title']);
    
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], '../uploads/gallery/');
        if ($upload['success']) {
            $image = $upload['filename'];
            
            $query = "INSERT INTO gallery (title, image) VALUES (:title, :image)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':image', $image);
            
            if ($stmt->execute()) {
                $message = "Gambar berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan gambar!";
            }
        } else {
            $error = $upload['message'];
        }
    } else {
        $error = "Harap pilih gambar!";
    }
}

// Hapus gambar
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Ambil data gambar untuk menghapus file
    $query = "SELECT image FROM gallery WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $gallery = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Hapus dari database
    $query = "DELETE FROM gallery WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Hapus file gambar
        if (!empty($gallery['image']) && file_exists('../uploads/gallery/' . $gallery['image'])) {
            unlink('../uploads/gallery/' . $gallery['image']);
        }
        $message = "Gambar berhasil dihapus!";
    } else {
        $error = "Gagal menghapus gambar!";
    }
}

// Toggle status gambar
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $query = "UPDATE gallery SET is_active = NOT is_active WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $message = "Status gambar berhasil diubah!";
    } else {
        $error = "Gagal mengubah status gambar!";
    }
}

// Ambil semua gambar
$query = "SELECT * FROM gallery ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$gallery = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Jeep Adventure Jogja</title>
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
            padding: 8px 12px; 
            border-radius: 5px; 
            text-decoration: none; 
            display: inline-block; 
            cursor: pointer; 
            transition: background 0.3s; 
            font-size: 14px; 
        }
        .btn:hover { 
            background: #e55a2b; 
        }
        .btn-sm { 
            padding: 5px 8px; 
            font-size: 12px; 
        }
        .btn-success { 
            background: #28a745; 
        }
        .btn-success:hover { 
            background: #218838; 
        }
        .btn-warning { 
            background: #ffc107; 
            color: #212529; 
        }
        .btn-warning:hover { 
            background: #e0a800; 
        }
        .btn-danger { 
            background: #dc3545; 
        }
        .btn-danger:hover { 
            background: #c82333; 
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
            margin-bottom: 20px; 
        }
        .gallery-container { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .form-group { 
            margin-bottom: 15px; 
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
            padding: 8px 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-family: inherit; 
        }
        .gallery-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-top: 20px; 
        }
        .gallery-item { 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            overflow: hidden; 
            position: relative; 
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .gallery-image { 
            width: 100%; 
            height: 150px; 
            object-fit: cover; 
        }
        .gallery-info { 
            padding: 10px; 
        }
        .gallery-title { 
            font-weight: 600; 
            margin-bottom: 5px; 
            font-size: 14px;
            line-height: 1.3;
        }
        .gallery-status { 
            font-size: 12px; 
            margin-bottom: 10px; 
        }
        .status-active { 
            color: #28a745; 
        }
        .status-inactive { 
            color: #dc3545; 
        }
        .gallery-actions { 
            display: flex; 
            gap: 5px; 
        }
        .empty-state { 
            text-align: center; 
            padding: 40px; 
            color: #666; 
        }
        .empty-state i { 
            font-size: 48px; 
            margin-bottom: 10px; 
            color: #ddd; 
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
            .gallery-container {
                padding: 14px;
            }
            .gallery-grid { 
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
                gap: 12px; 
            }
            .btn { 
                padding: 8px 10px; 
                font-size: 14px; 
            }
            .form-grid {
                grid-template-columns: 1fr !important;
                gap: 0 !important;
            }
        }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
                gap: 10px;
            }
            .gallery-image {
                height: 120px;
            }
            .gallery-info {
                padding: 8px;
            }
            .gallery-title {
                font-size: 12px;
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
            <a href="gallery.php" class="active"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="header">
            <h1>Kelola Galeri</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Form Tambah Gambar -->
        <div class="form-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">Tambah Gambar Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Judul Gambar *</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Gambar *</label>
                        <input type="file" name="image" accept="image/*" required>
                    </div>
                </div>
                <button type="submit" name="add_gallery" class="btn">Tambah ke Galeri</button>
            </form>
        </div>
        
        <!-- Daftar Gambar -->
        <div class="gallery-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">Daftar Gambar Galeri</h2>
            
            <?php if (empty($gallery)): ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>Belum ada gambar</h3>
                    <p>Tambahkan gambar pertama Anda ke galeri</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($gallery as $item): ?>
                    <div class="gallery-item">
                        <img src="../uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                             class="gallery-image">
                        <div class="gallery-info">
                            <div class="gallery-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            <div class="gallery-status">
                                Status: 
                                <?php if ($item['is_active']): ?>
                                    <span class="status-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </div>
                            <div class="gallery-actions">
                                <a href="?toggle=<?php echo $item['id']; ?>" 
                                   class="btn btn-sm <?php echo $item['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                   title="<?php echo $item['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus gambar <?php echo htmlspecialchars($item['title']); ?>?')"
                                   title="Hapus Gambar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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

        // Preview image before upload
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('input[name="image"]');
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
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            alert('Hanya file gambar (JPEG, PNG, GIF) yang diizinkan');
                            e.target.value = '';
                            return;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>