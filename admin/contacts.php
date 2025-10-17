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
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .form-section h3 { color: #1A3C40; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #FF6B35; }
        .social-preview { display: flex; gap: 10px; margin-top: 10px; }
        .social-preview a { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; background-color: #1A3C40; border-radius: 50%; color: white; text-decoration: none; }
        .map-preview { margin-top: 10px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
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
            <li><a href="gallery.php"><i class="fas fa-images"></i> Galeri</a></li>
            <li><a href="contacts.php" class="active"><i class="fas fa-address-book"></i> Kontak</a></li>
            <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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