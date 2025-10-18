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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Admin - Jeep Adventure</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;background:#f5f5f5;margin:0}
        .container{max-width:900px;margin:30px auto;padding:20px}
        .card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
        .form-group{margin-bottom:12px}
        input{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px}
        .btn{background:#FF6B35;color:#fff;padding:10px 14px;border-radius:6px;border:none;cursor:pointer}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
        .message{background:#d4edda;color:#155724;padding:8px;border-radius:6px;margin-bottom:12px}
        .error{background:#f8d7da;color:#721c24;padding:8px;border-radius:6px;margin-bottom:12px}

        /* Responsive helpers */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        @media (max-width: 800px) {
            .container{max-width:100%;margin:12px;padding:12px}
            .card{padding:14px;border-radius:6px}
            .btn{padding:8px 10px;font-size:14px}
            table{font-size:13px}
            th,td{padding:8px 6px}
            h1{font-size:20px}
            .form-group input{padding:9px}
        }

        @media (max-width: 480px) {
            .container{margin:8px;padding:10px}
            .card{padding:12px}
            .btn{padding:8px 10px;font-size:13px}
            table{font-size:12px}
            th,td{padding:6px 4px}
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h1>Manajemen Admin</h1>
            <div>
                <a href="dashboard.php" class="btn" style="background:#6c757d;margin-right:8px">Kembali</a>
            </div>
        </div>
        <?php if (!empty($message)): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>

        <div class="card">
            <h3><?php echo $editing ? 'Edit Profil Admin' : 'Tambah Admin Baru'; ?></h3>
            <form method="POST" action="">
                <?php if ($editing): ?>
                    <input type="hidden" name="edit_id" value="<?php echo (int)$edit_admin['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required value="<?php echo $editing ? htmlspecialchars($edit_admin['username']) : ''; ?>">
                </div>
                <?php if ($has_email): ?>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo $editing ? htmlspecialchars($edit_admin['email'] ?? '') : ''; ?>">
                </div>
                <?php endif; ?>
                <?php if ($has_full_name): ?>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo $editing ? htmlspecialchars($edit_admin['full_name'] ?? '') : ''; ?>">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Password <?php echo $editing ? '(kosongkan jika tidak ingin mengganti)' : ''; ?></label>
                    <input type="password" name="password" <?php echo $editing ? '' : 'required'; ?>>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirm" <?php echo $editing ? '' : 'required'; ?>>
                </div>
                <button class="btn" type="submit"><?php echo $editing ? 'Simpan Perubahan' : 'Buat Admin'; ?></button>
                <?php if ($editing): ?>
                    <a href="admins.php" class="btn" style="background:#6c757d;margin-left:8px">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="margin-top:16px">
            <h3>Daftar Admin</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <?php if ($has_email): ?><th>Email</th><?php endif; ?>
                            <?php if ($has_full_name): ?><th>Full Name</th><?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $a): ?>
                            <tr>
                                <td><?php echo $a['id']; ?></td>
                                <td><?php echo htmlspecialchars($a['username']); ?></td>
                                <?php if ($has_email): ?><td><?php echo htmlspecialchars($a['email'] ?? ''); ?></td><?php endif; ?>
                                <?php if ($has_full_name): ?><td><?php echo htmlspecialchars($a['full_name'] ?? ''); ?></td><?php endif; ?>
                                <td>
                                    <a href="admins.php?edit=<?php echo $a['id']; ?>" class="btn" style="background:#6c757d;padding:6px 10px">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
