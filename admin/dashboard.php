<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Hitung jumlah data
$packages_count = $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$gallery_count = $db->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Jeep Adventure Jogja</title>
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
        .topnav .brand { display:flex; align-items:center; gap:10px; }
        .topnav .brand h2 { color: #FF6B35; margin:0; font-size:18px; }
        .topnav .menu { display:flex; gap:6px; align-items:center; }
        .topnav .menu a { color: white; text-decoration: none; padding:8px 12px; border-radius:6px; font-weight:600; }
        .topnav .menu a.active, .topnav .menu a:hover { background: #FF6B35; color: #fff; }
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar-header h2 {
            color: #FF6B35;
        }
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: #FF6B35;
        }
        .sidebar-menu li a i {
            margin-right: 10px;
        }
        .main-content {
            padding: 90px 20px 20px; /* account for topnav height */
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #FF6B35;
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
        /* Responsive helpers copied from admins.php */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 800px) {
            .main-content { margin-left: 0; padding: 14px; }
            .header { padding: 14px; }
            .stat-card { padding: 12px; }
            .btn { padding: 8px 10px; font-size: 14px; }
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
            <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-building"></i> Profil</a>
            <a href="admins.php"><i class="fas fa-users-cog"></i> Admins</a>
            <a href="packages.php"><i class="fas fa-box"></i> Paket</a>
            <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="header">
            <h1>Dashboard</h1>
            <div>Selamat datang, <?php echo $_SESSION['admin_username']; ?>!</div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Paket Tour</h3>
                <div class="number"><?php echo $packages_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Gambar Galeri</h3>
                <div class="number"><?php echo $gallery_count; ?></div>
            </div>
        </div>
        
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div style="margin-top: 15px;">
                <a href="profile.php" class="btn">Edit Profil Perusahaan</a>
                <a href="packages.php" class="btn">Kelola Paket Tour</a>
                <a href="gallery.php" class="btn">Kelola Galeri</a>
            </div>
        </div>
    </div>
</body>
</html>