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
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$roleId = isset($_POST['role_id']) ? (int) $_POST['role_id'] : 0;

if ($id <= 0 || $username === '' || $email === '' || $roleId <= 0) {
    $_SESSION['error_message'] = 'Data pengguna tidak valid.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_message'] = 'Format email tidak valid.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}

try {
    $checkUser = $pdo->prepare("SELECT user_id FROM users WHERE (username = :username OR email = :email) AND user_id != :id LIMIT 1");
    $checkUser->execute([
        ':username' => $username,
        ':email' => $email,
        ':id' => $id,
    ]);

    if ($checkUser->fetch()) {
        $_SESSION['error_message'] = 'Username atau email sudah dipakai pengguna lain.';
        header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE user_id = :id");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':id' => $id,
    ]);

    $deleteRoles = $pdo->prepare("DELETE FROM user_roles WHERE user_id = :id");
    $deleteRoles->execute([':id' => $id]);

    $assignRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
    $assignRole->execute([
        ':user_id' => $id,
        ':role_id' => $roleId,
    ]);

    $_SESSION['success_message'] = 'Data pengguna berhasil diperbarui.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
} catch (PDOException $e) {
    error_log('Edit user error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal memperbarui data pengguna.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}
