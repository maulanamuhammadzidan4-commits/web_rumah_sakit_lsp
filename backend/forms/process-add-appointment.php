<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

#---------------------------------------------------
# CEK AUTENTIKASI
#---------------------------------------------------
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
    $_SESSION['error_message'] = 'Anda tidak memiliki izin untuk menambah janji temu.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

#---------------------------------------------------
# VALIDASI INPUT
#---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode permintaan tidak valid.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

$id_user = isset($_POST['id_user']) ? (int) $_POST['id_user'] : 0;
$id_dokter = isset($_POST['id_dokter']) ? (int) $_POST['id_dokter'] : 0;
$klinik = isset($_POST['klinik']) ? trim($_POST['klinik']) : '';

if ($id_user <= 0) {
    $_SESSION['error_message'] = 'Pasien harus dipilih.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

if ($id_dokter <= 0) {
    $_SESSION['error_message'] = 'Dokter harus dipilih.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

if (empty($klinik)) {
    $_SESSION['error_message'] = 'Klinik harus diisi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
    exit;
}

#---------------------------------------------------
# INSERT DATA KE DATABASE
#---------------------------------------------------
try {
    $stmt = $pdo->prepare(
        "INSERT INTO appointments (id_user, id_dokter, klinik)
         VALUES (:id_user, :id_dokter, :klinik)"
    );

    $stmt->execute([
        ':id_user' => $id_user,
        ':id_dokter' => $id_dokter,
        ':klinik' => $klinik,
    ]);

    $_SESSION['success_message'] = 'Janji temu berhasil ditambahkan.';
} catch (PDOException $e) {
    error_log("Error adding appointment: " . $e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan saat menambah janji temu.';
}

header('Location: ' . rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php');
exit;
