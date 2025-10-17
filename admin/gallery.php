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
        .btn { background: #FF6B35; color: white; border: none; padding: 8px 12px; border-radius: 5px; text-decoration: none; display: inline-block; cursor: pointer; transition: background 0.3s; font-size: 14px; }
        .btn:hover { background: #e55a2b; }
        .btn-sm { padding: 5px 8px; font-size: 12px; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .logout { background: #dc3545; }
        .logout:hover { background: #c82333; }
        .message { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .form-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .gallery-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
        .gallery-item { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; position: relative; }
        .gallery-image { width: 100%; height: 150px; object-fit: cover; }
        .gallery-info { padding: 10px; }
        .gallery-title { font-weight: 600; margin-bottom: 5px; }
        .gallery-status { font-size: 12px; margin-bottom: 10px; }
        .status-active { color: #28a745; }
        .status-inactive { color: #dc3545; }
        .gallery-actions { display: flex; gap: 5px; }
        .empty-state { text-align: center; padding: 40px; color: #666; }
        .empty-state i { font-size: 48px; margin-bottom: 10px; color: #ddd; }
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
            <li><a href="profile.php"><i class="fas fa-building"></i> Profil Perusahaan</a></li>
            <li><a href="admins.php"><i class="fas fa-users-cog"></i> Admins</a></li>
            <li><a href="packages.php"><i class="fas fa-box"></i> Paket Tour</a></li>
            <li><a href="gallery.php" class="active"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a></li>
            <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
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
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
                        <img src="../uploads/gallery/<?php echo htmlspecialchars($item['image']); ?>" class="gallery-image">
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
                                <a href="?toggle=<?php echo $item['id']; ?>" class="btn btn-sm <?php echo $item['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus gambar <?php echo htmlspecialchars($item['title']); ?>?')">
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
</body>
</html>