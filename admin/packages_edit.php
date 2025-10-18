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
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif;background:#f5f5f5}
        .container{max-width:1000px;margin:30px auto;padding:20px}
        .card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
        .form-group{margin-bottom:15px}
        label{display:block;margin-bottom:6px;font-weight:600}
        input[type=text], input[type=number], textarea, select{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px}
        .btn{background:#FF6B35;color:#fff;padding:10px 14px;border-radius:6px;border:none;cursor:pointer}
        .btn-secondary{background:#6c757d}
        .message{background:#d4edda;color:#155724;padding:8px;border-radius:6px;margin-bottom:12px}
        .error{background:#f8d7da;color:#721c24;padding:8px;border-radius:6px;margin-bottom:12px}
        .image-preview{max-width:220px;margin-top:8px;border-radius:6px}
        .top-actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
        @media (max-width: 800px) {
            .container{padding:12px;margin:12px}
            .top-actions{flex-direction:column;align-items:flex-start;gap:8px}
            .btn{padding:8px 10px}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-actions">
            <h1>Edit Paket</h1>
            <div>
                <a href="packages.php" class="btn btn-secondary">Kembali ke Daftar Paket</a>
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
                    <label>Nama Paket</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga</label>
                    <input type="number" name="price" value="<?php echo (int)$package['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" required><?php echo htmlspecialchars($package['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Fitur (pisahkan dengan enter)</label>
                    <textarea name="features"><?php echo htmlspecialchars($package['features']); ?></textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 220px;gap:16px;align-items:start">
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

                        <div class="form-group">
                            <label><input type="checkbox" name="is_active" value="1" <?php echo $package['is_active'] ? 'checked' : ''; ?>> Aktifkan paket</label>
                        </div>
                        <div style="margin-top:8px">
                            <button type="submit" class="btn">Simpan Perubahan</button>
                        </div>
                    </div>
                    <div>
                        <label>Preview Gambar Saat Ini</label>
                        <?php if (!empty($package['image']) && file_exists('../uploads/packages/' . $package['image'])): ?>
                            <img src="../uploads/packages/<?php echo htmlspecialchars($package['image']); ?>" class="image-preview" alt="<?php echo htmlspecialchars($package['name']); ?>">
                        <?php else: ?>
                            <div style="color:#666;padding:14px;background:#f8f9fa;border-radius:6px;text-align:center">Tidak ada gambar</div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
