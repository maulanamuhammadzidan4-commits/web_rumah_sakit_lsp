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
    $_SESSION['error_message'] = 'Akses ditolak. Anda bukan admin.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'ID pengguna tidak valid.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $deleteRoles = $pdo->prepare("DELETE FROM user_roles WHERE user_id = :id");
    $deleteRoles->execute([':id' => $id]);

    $deleteUser = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
    $deleteUser->execute([':id' => $id]);

    $pdo->commit();

    $_SESSION['success_message'] = 'Pengguna berhasil dihapus.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Delete user error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menghapus pengguna.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}
