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

// Tambah paket baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_package'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $features = sanitize($_POST['features']);
    $price = (int)$_POST['price'];
    $badge = sanitize($_POST['badge']);
    
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], '../uploads/packages/');
        if ($upload['success']) {
            $image = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    }
    
    if (empty($error)) {
        $query = "INSERT INTO packages (name, description, features, price, image, badge) 
                  VALUES (:name, :description, :features, :price, :image, :badge)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':features', $features);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':badge', $badge);
        
        if ($stmt->execute()) {
            $message = "Paket berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan paket!";
        }
    }
}

// Hapus paket
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Ambil data paket untuk menghapus gambar
    $query = "SELECT image FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Hapus dari database
    $query = "DELETE FROM packages WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        // Hapus gambar jika ada
        if (!empty($package['image']) && file_exists('../uploads/packages/' . $package['image'])) {
            unlink('../uploads/packages/' . $package['image']);
        }
        $message = "Paket berhasil dihapus!";
    } else {
        $error = "Gagal menghapus paket!";
    }
}

// Toggle status paket
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $query = "UPDATE packages SET is_active = NOT is_active WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        $message = "Status paket berhasil diubah!";
    } else {
        $error = "Gagal mengubah status paket!";
    }
}

// Ambil semua paket
$query = "SELECT * FROM packages ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute();
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Paket - Jeep Adventure Jogja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
    .topnav { position: fixed; top: 0; left: 0; right: 0; height: 64px; background: #1A3C40; color: white; display:flex; align-items:center; justify-content:space-between; padding:8px 20px; z-index:1200; }
    .topnav .brand { display:flex; align-items:center; gap:10px; }
    .topnav .brand h2 { color:#FF6B35; margin:0; font-size:18px }
    .topnav .menu { display:flex; gap:6px; align-items:center }
    .topnav .menu a { color:white; text-decoration:none; padding:8px 12px; border-radius:6px; font-weight:600 }
    .topnav .menu a.active, .topnav .menu a:hover { background:#FF6B35 }
    .main-content { padding: 90px 20px 20px }
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
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .form-group textarea { min-height: 80px; resize: vertical; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1A3C40; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .status-active { color: #28a745; font-weight: 600; }
        .status-inactive { color: #dc3545; font-weight: 600; }
        .package-image { width: 80px; height: 60px; object-fit: cover; border-radius: 5px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
        .badge-populer { background: #FF6B35; color: white; }
        .badge-favorit { background: #28a745; color: white; }
        .badge-eksklusif { background: #6f42c1; color: white; }
        .action-buttons { display: flex; gap: 5px; }
        /* Responsive helpers */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 800px) {
            .main-content { margin-left: 0; padding: 12px; }
            .form-container, .table-container { padding: 12px; }
            .btn { padding: 8px 10px; font-size: 14px; }
            table { font-size: 13px; }
        }
    </style>
</head>
<body>
    <header class="topnav">
        <div class="brand">
            <h2>Jeep Adventure</h2>
            <small style="color:#fff;opacity:0.85">Admin Panel</small>
        </div>
        <nav class="menu">
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
        <div class="header">
            <h1>Kelola Paket Tour</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Form Tambah Paket -->
        <div class="form-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">Tambah Paket Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Nama Paket *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Harga *</label>
                        <input type="number" name="price" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi *</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label>Fitur (pisahkan dengan enter) *</label>
                    <textarea name="features" placeholder="Contoh:&#10;Durasi: 3-4 jam&#10;Maksimal 4 orang/Jeep&#10;Pemandu profesional" required></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Badge</label>
                        <select name="badge">
                            <option value="">Tidak ada</option>
                            <option value="Populer">Populer</option>
                            <option value="Favorit">Favorit</option>
                            <option value="Eksklusif">Eksklusif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Gambar</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                </div>
                <button type="submit" name="add_package" class="btn">Tambah Paket</button>
            </form>
        </div>
        
        <!-- Daftar Paket -->
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">Daftar Paket</h2>
            
            <?php if (empty($packages)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">Belum ada paket tour.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Paket</th>
                            <th>Harga</th>
                            <th>Badge</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                        <tr>
                            <td>
                                <?php if (!empty($package['image'])): ?>
                                    <img src="../uploads/packages/<?php echo htmlspecialchars($package['image']); ?>" class="package-image">
                                <?php else: ?>
                                    <span style="color: #999;">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($package['name']); ?></td>
                            <td>Rp <?php echo number_format($package['price'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if (!empty($package['badge'])): ?>
                                    <span class="badge badge-<?php echo strtolower($package['badge']); ?>">
                                        <?php echo htmlspecialchars($package['badge']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($package['is_active']): ?>
                                    <span class="status-active">Aktif</span>
                                <?php else: ?>
                                    <span class="status-inactive">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="packages_edit.php?id=<?php echo $package['id']; ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?toggle=<?php echo $package['id']; ?>" class="btn btn-sm <?php echo $package['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                        <i class="fas fa-power-off"></i> <?php echo $package['is_active'] ? 'Nonaktif' : 'Aktif'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $package['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus paket <?php echo htmlspecialchars($package['name']); ?>?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>