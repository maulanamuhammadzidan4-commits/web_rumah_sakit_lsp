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

$isUser = in_array('user', $userRoles, true);

$sessionUserId = (int) ($currentUser['id'] ?? 0);
if ($sessionUserId <= 0) {
    $_SESSION['error_message'] = 'Data pengguna tidak valid. Silakan login kembali.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

if (!$isUser) {
    $_SESSION['error_message'] = 'Hanya user yang dapat membuat janji temu.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php');
    exit;
}

#---------------------------------------------------
# VALIDASI INPUT
#---------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Metode permintaan tidak valid.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php');
    exit;
}

$id_user = $sessionUserId;
$id_dokter = isset($_POST['id_dokter']) ? (int) $_POST['id_dokter'] : 0;
$klinik = isset($_POST['klinik']) ? trim($_POST['klinik']) : '';
$tanggal_temu = isset($_POST['tanggal_temu']) ? trim($_POST['tanggal_temu']) : '';

if ($id_user <= 0) {
    $_SESSION['error_message'] = 'Pasien harus dipilih.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php#appointment');
    exit;
}

if ($id_dokter <= 0) {
    $_SESSION['error_message'] = 'Dokter harus dipilih.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php#appointment');
    exit;
}

if (empty($klinik)) {
    $_SESSION['error_message'] = 'Klinik harus diisi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php#appointment');
    exit;
}

if (empty($tanggal_temu)) {
    $_SESSION['error_message'] = 'Tanggal dan waktu temu harus diisi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php#appointment');
    exit;
}

$appointmentDate = DateTime::createFromFormat('Y-m-d\\TH:i', $tanggal_temu);
if (!$appointmentDate || $appointmentDate->format('Y-m-d\\TH:i') !== $tanggal_temu) {
    $_SESSION['error_message'] = 'Format tanggal dan waktu temu tidak valid.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/home.php#appointment');
    exit;
}

#---------------------------------------------------
# INSERT DATA KE DATABASE
#---------------------------------------------------
try {
    $stmt = $pdo->prepare(
        "INSERT INTO appointments (id_user, id_dokter, klinik, tanggal_temu)
         VALUES (:id_user, :id_dokter, :klinik, :tanggal_temu)"
    );

    $stmt->execute([
        ':id_user' => $id_user,
        ':id_dokter' => $id_dokter,
        ':klinik' => $klinik,
        ':tanggal_temu' => $appointmentDate->format('Y-m-d H:i:s'),
    ]);

    $_SESSION['success_message'] = 'Janji temu berhasil ditambahkan.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/profile.php');
    exit;

} catch (PDOException $e) {
    error_log("Error adding appointment: " . $e->getMessage());
    $_SESSION['error_message'] = 'Terjadi kesalahan saat menambah janji temu.';
}

