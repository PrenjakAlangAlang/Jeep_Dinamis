<?php
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function uploadImage($file, $target_dir) {
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "File is too large. Maximum size is 5MB."];
    }
    
    // Allow certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if(!in_array($imageFileType, $allowed_types)) {
        return ["success" => false, "message" => "Only JPG, JPEG, PNG, GIF & WEBP files are allowed."];
    }
    
    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ["success" => true, "filename" => $new_filename];
    } else {
        return ["success" => false, "message" => "Error uploading file."];
    }
}

function formatPrice($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function getStatusBadge($status) {
    if ($status) {
        return '<span style="color: #28a745; font-weight: 600;">Aktif</span>';
    } else {
        return '<span style="color: #dc3545; font-weight: 600;">Nonaktif</span>';
    }
}
?>