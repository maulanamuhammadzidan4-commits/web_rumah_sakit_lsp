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
if ($id <= 0) {
    $_SESSION['error_message'] = 'ID galeri tidak valid.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT file_name FROM gallery WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $gallery = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gallery) {
        $filePath = __DIR__ . '/../../frontend/assets/img/gallery/' . $gallery['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $deleteStmt = $pdo->prepare('DELETE FROM gallery WHERE id = :id');
    $deleteStmt->execute([':id' => $id]);

    $_SESSION['success_message'] = 'Data galeri berhasil dihapus.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
} catch (PDOException $e) {
    error_log('Error deleting gallery item: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menghapus data galeri dari database.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}
