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

// detect if admin table has extra columns
$has_email = false;
$has_full_name = false;
try {
    $res = $db->query("SHOW COLUMNS FROM admin LIKE 'email'");
    if ($res && $res->fetch()) $has_email = true;
    $res = $db->query("SHOW COLUMNS FROM admin LIKE 'full_name'");
    if ($res && $res->fetch()) $has_full_name = true;
} catch (PDOException $e) {
    // ignore - assume columns missing
}

// Add or edit admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine if this is an edit
    $edit_id = isset($_POST['edit_id']) && is_numeric($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $full_name = sanitize($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($username)) {
        $error = 'Username wajib diisi.';
    } elseif ($password !== '' && $password !== $password_confirm) {
        $error = 'Password dan konfirmasi tidak cocok.';
    } else {
        // Check if username exists (for new or when changing to another username)
        $checkQuery = "SELECT id FROM admin WHERE username = :username";
        $check = $db->prepare($checkQuery);
        $check->bindParam(':username', $username);
        $check->execute();
        $exists = $check->fetch(PDO::FETCH_ASSOC);

        if ($edit_id) {
            // if exists and not the same id -> error
            if ($exists && (int)$exists['id'] !== $edit_id) {
                $error = 'Username sudah digunakan oleh admin lain.';
            }
        } else {
            if ($exists) {
                $error = 'Username sudah digunakan.';
            }
        }
    }

    if (empty($error)) {
        if ($edit_id) {
            // build update dynamically
            $sets = [];
            $params = [];
            $sets[] = 'username = :username'; $params[':username'] = $username;
            if ($has_email) { $sets[] = 'email = :email'; $params[':email'] = $email; }
            if ($has_full_name) { $sets[] = 'full_name = :full_name'; $params[':full_name'] = $full_name; }
            if (!empty($password)) { $sets[] = 'password = :password'; $params[':password'] = password_hash($password, PASSWORD_DEFAULT); }

            $sql = 'UPDATE admin SET ' . implode(', ', $sets) . ' WHERE id = :id';
            $stmt = $db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
            try {
                if ($stmt->execute()) {
                    $message = 'Profil admin berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui profil admin.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }

        } else {
            // insert new admin
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $cols = ['username','password'];
                $placeholders = [':username',':password'];
                $params = [':username' => $username, ':password' => $hash];
                if ($has_email) { $cols[] = 'email'; $placeholders[] = ':email'; $params[':email'] = $email; }
                if ($has_full_name) { $cols[] = 'full_name'; $placeholders[] = ':full_name'; $params[':full_name'] = $full_name; }

                $sql = 'INSERT INTO admin (' . implode(',', $cols) . ') VALUES (' . implode(',', $placeholders) . ')';
                $stmt = $db->prepare($sql);
                foreach ($params as $k => $v) $stmt->bindValue($k, $v);
                if ($stmt->execute()) {
                    $message = 'Admin baru berhasil ditambahkan.';
                } else {
                    $error = 'Gagal menambahkan admin.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Fetch admins (include optional columns)
$selectCols = 'id, username';
if ($has_email) $selectCols .= ', email';
if ($has_full_name) $selectCols .= ', full_name';
$admins = $db->query("SELECT $selectCols FROM admin ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Prepare edit mode if requested
$editing = false;
$edit_admin = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT $selectCols FROM admin WHERE id = :id LIMIT 1");
    $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
    $stmt->execute();
    $edit_admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($edit_admin) {
        $editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin - Jeep Adventure Jogja</title>
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
        .table-container { 
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
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            font-family: inherit; 
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        /* Responsive Table */
        .table-wrap { 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch; 
        }

        @media (max-width: 768px) {
            .main-content { 
                margin-left: 0; 
                padding: 80px 14px 14px; 
            }
            .form-container, 
            .table-container { 
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
            
            /* Stacked table for mobile */
            table, thead, tbody, th, td, tr { 
                display: block; 
            }
            thead { 
                display: none; 
            }
            tbody tr { 
                margin-bottom: 15px; 
                background: #fff; 
                border-radius: 8px; 
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                padding: 10px;
                border: 1px solid #eee;
            }
            tbody td { 
                display: flex; 
                justify-content: space-between; 
                align-items: center;
                padding: 8px 6px; 
                border-bottom: 1px solid #f5f5f5;
            }
            tbody td:last-child {
                border-bottom: none;
            }
            tbody td .label { 
                font-weight: 600; 
                color: #666; 
                margin-right: 10px;
                min-width: 80px;
            }
            tbody td .value { 
                text-align: right; 
                color: #333; 
                word-wrap: break-word;
                flex: 1;
            }
            
            /* Actions row */
            tbody td.actions { 
                display: flex;
                justify-content: center;
                gap: 8px;
                padding-top: 10px;
                border-top: 1px solid #eee;
            }
            tbody td.actions .btn { 
                flex: 1;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 80px 10px 10px;
            }
            .header {
                padding: 15px;
            }
            .form-container,
            .table-container {
                padding: 12px;
            }
            .actions {
                flex-direction: column;
            }
            tbody td.actions {
                flex-direction: column;
            }
            tbody td.actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header class="topnav">
        <div class="brand">
            <h2>Jeep Adventure</h2>
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
            <a href="admins.php" class="active"><i class="fas fa-users-cog"></i> Admins</a>
            <a href="packages.php"><i class="fas fa-box"></i> Paket</a>
            <a href="gallery.php"><i class="fas fa-images"></i> Galeri</a>
            <a href="contacts.php"><i class="fas fa-address-book"></i> Kontak</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>
    
    <div class="main-content">
        <div class="header">
            <h1>Kelola Admin</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form Tambah/Edit Admin -->
        <div class="form-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">
                <?php echo $editing ? 'Edit Profil Admin' : 'Tambah Admin Baru'; ?>
            </h2>
            <form method="POST" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="edit_id" value="<?php echo (int)$edit_admin['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" required value="<?php echo $editing ? htmlspecialchars($edit_admin['username']) : ''; ?>">
                    </div>
                    
                    <?php if ($has_email): ?>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo $editing ? htmlspecialchars($edit_admin['email'] ?? '') : ''; ?>">
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($has_full_name): ?>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?php echo $editing ? htmlspecialchars($edit_admin['full_name'] ?? '') : ''; ?>">
                </div>
                <?php endif; ?>
                
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Password <?php echo $editing ? '(kosongkan jika tidak ingin mengganti)' : '*'; ?></label>
                        <input type="password" name="password" <?php echo $editing ? '' : 'required'; ?>>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <?php echo $editing ? '(kosongkan jika tidak ingin mengganti)' : '*'; ?></label>
                        <input type="password" name="password_confirm" <?php echo $editing ? '' : 'required'; ?>>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn" type="submit">
                        <?php echo $editing ? 'Simpan Perubahan' : 'Tambah Admin'; ?>
                    </button>
                    <?php if ($editing): ?>
                        <a href="admins.php" class="btn btn-secondary">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Daftar Admin -->
        <div class="table-container">
            <h2 style="margin-bottom: 20px; color: #1A3C40;">Daftar Admin</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <?php if ($has_email): ?><th>Email</th><?php endif; ?>
                            <?php if ($has_full_name): ?><th>Nama Lengkap</th><?php endif; ?>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $admin): ?>
                            <tr>
                                <td>
                                    <span class="label">ID:</span>
                                    <span class="value"><?php echo $admin['id']; ?></span>
                                </td>
                                <td>
                                    <span class="label">Username:</span>
                                    <span class="value"><?php echo htmlspecialchars($admin['username']); ?></span>
                                </td>
                                <?php if ($has_email): ?>
                                <td>
                                    <span class="label">Email:</span>
                                    <span class="value"><?php echo htmlspecialchars($admin['email'] ?? ''); ?></span>
                                </td>
                                <?php endif; ?>
                                <?php if ($has_full_name): ?>
                                <td>
                                    <span class="label">Nama Lengkap:</span>
                                    <span class="value"><?php echo htmlspecialchars($admin['full_name'] ?? ''); ?></span>
                                </td>
                                <?php endif; ?>
                                <td class="actions">
                                    <a href="admins.php?edit=<?php echo $admin['id']; ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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

        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            const confirmInput = document.querySelector('input[name="password_confirm"]');
            
            function validatePasswords() {
                if (passwordInput.value !== confirmInput.value) {
                    confirmInput.style.borderColor = '#dc3545';
                } else {
                    confirmInput.style.borderColor = '#28a745';
                }
            }
            
            if (passwordInput && confirmInput) {
                passwordInput.addEventListener('input', validatePasswords);
                confirmInput.addEventListener('input', validatePasswords);
            }
        });
    </script>
</body>
</html>