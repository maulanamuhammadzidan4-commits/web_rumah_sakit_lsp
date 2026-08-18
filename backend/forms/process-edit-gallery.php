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

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$currentFileName = trim($_POST['current_file_name'] ?? '');

if ($id <= 0 || $title === '' || $description === '') {
    $_SESSION['error_message'] = 'ID, judul, dan deskripsi galeri wajib diisi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT file_name FROM gallery WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        $_SESSION['error_message'] = 'Data galeri tidak ditemukan.';
        header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
        exit;
    }

    $newFileName = $existing['file_name'];
    $uploadDir = __DIR__ . '/../../frontend/assets/img/gallery/';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && !empty($_FILES['image']['name'])) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileType = $_FILES['image']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        if (!in_array($fileType, $allowedTypes, true)) {
            $_SESSION['error_message'] = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
            exit;
        }

        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $newFileName = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $targetPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmp, $targetPath)) {
            $_SESSION['error_message'] = 'Gagal menyimpan gambar baru.';
            header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
            exit;
        }

        $oldFilePath = $uploadDir . $existing['file_name'];
        if ($existing['file_name'] && file_exists($oldFilePath) && $existing['file_name'] !== $newFileName) {
            unlink($oldFilePath);
        }
    }

    $updateStmt = $pdo->prepare(
        'UPDATE gallery SET title = :title, description = :description, file_name = :file_name WHERE id = :id'
    );
    $updateStmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':file_name' => $newFileName,
        ':id' => $id,
    ]);

    $_SESSION['success_message'] = 'Data galeri berhasil diperbarui.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
} catch (PDOException $e) {
    error_log('Error updating gallery item: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal memperbarui data galeri.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}
