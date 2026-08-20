<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

$usersPage = rtrim(BASE_URL, '/') . '/admin/dashboard/pages/users.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

$currentUser = $_SESSION['user'] ?? [];
$userRoles = array_map('strtolower', array_map('trim', (array) ($currentUser['roles'] ?? [])));
if (!array_intersect($userRoles, ['admin', 'super admin', 'super_admin'])) {
    http_response_code(403);
    exit('403 - Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $usersPage);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$roleId = (int) ($_POST['role_id'] ?? 0);

if ($username === '' || $email === '' || $password === '' || $roleId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    $_SESSION['error_message'] = 'Username, email, password minimal 8 karakter, dan role wajib valid.';
    header('Location: ' . $usersPage);
    exit;
}

try {
    $check = $pdo->prepare('SELECT user_id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $check->execute([':username' => $username, ':email' => $email]);
    if ($check->fetch()) {
        $_SESSION['error_message'] = 'Username atau email sudah digunakan.';
        header('Location: ' . $usersPage);
        exit;
    }

    $pdo->beginTransaction();
    $insertUser = $pdo->prepare('INSERT INTO users (username, password_hash, email) VALUES (:username, :password_hash, :email)');
    $insertUser->execute([
        ':username' => $username,
        ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ':email' => $email,
    ]);

    $insertRole = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
    $insertRole->execute([':user_id' => $pdo->lastInsertId(), ':role_id' => $roleId]);
    $pdo->commit();

    $_SESSION['success_message'] = 'Pengguna berhasil ditambahkan.';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Add user error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menambahkan pengguna.';
}

header('Location: ' . $usersPage);
exit;
