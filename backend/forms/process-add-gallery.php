<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

$currentUser = $_SESSION['user'] ?? [];
$userRoles = array_map('strtolower', array_map('trim', (array)($currentUser['roles'] ?? [])));

$isAdmin = false;
foreach ($userRoles as $role) {
    if (in_array($role, ['admin', 'super admin', 'super_admin'], true)) {
        $isAdmin = true;
        break;
    }
}

if (!$isAdmin) {
    http_response_code(403);
    die('Akses ditolak. Anda tidak memiliki izin.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($title) || empty($description) || empty($_FILES['image']['name'])) {
    $_SESSION['error_message'] = 'Judul, deskripsi, dan gambar galeri wajib diisi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

$uploadDir = __DIR__ . '/../../frontend/assets/img/gallery/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
$fileTmp = $_FILES['image']['tmp_name'] ?? '';
$fileName = $_FILES['image']['name'];
$fileType = $_FILES['image']['type'] ?? '';
$fileError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;

if ($fileError !== UPLOAD_ERR_OK || !is_uploaded_file($fileTmp)) {
    $_SESSION['error_message'] = 'Upload gambar gagal. Silakan coba lagi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

if (!in_array($fileType, $allowedTypes, true)) {
    $_SESSION['error_message'] = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$safeName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
$targetPath = $uploadDir . $safeName;

if (!move_uploaded_file($fileTmp, $targetPath)) {
    $_SESSION['error_message'] = 'Gagal menyimpan gambar ke folder galeri.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            file_name VARCHAR(255) NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $stmt = $pdo->prepare(
        'INSERT INTO gallery (file_name, title, description) VALUES (:file_name, :title, :description)'
    );
    $stmt->execute([
        ':file_name' => $safeName,
        ':title' => $title,
        ':description' => $description,
    ]);

    $_SESSION['success_message'] = 'Data galeri berhasil ditambahkan.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
} catch (PDOException $e) {
    error_log('Error adding gallery item: ' . $e->getMessage());
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }

    $_SESSION['error_message'] = 'Gagal menyimpan data galeri ke database.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}
