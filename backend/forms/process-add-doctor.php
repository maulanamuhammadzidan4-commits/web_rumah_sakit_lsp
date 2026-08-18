<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

#------------------------------------------------
# CEK AUTENTIKASI & OTORISASI                   |
#------------------------------------------------
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
  header('Location: ' . BASE_URL . 'frontend/pages/login-page.php');
  exit;
}

$currentUser = $_SESSION['user'] ?? [];
$userRoles   = $currentUser['roles'] ?? [];

$isAdmin = false;
foreach ($userRoles as $role) {
  if (in_array(strtolower($role), ['admin', 'super admin', 'super_admin'], true)) {
    $isAdmin = true;
    break;
  }
}

if (!$isAdmin) {
  http_response_code(403);
  die('Akses ditolak. Anda tidak memiliki izin.');
}

#-------------------------------------------------
# PROSES INPUT DATA DOKTER                       |
#-------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $namaDokter = isset($_POST['nama_dokter']) ? trim($_POST['nama_dokter']) : '';
  $spesialis = isset($_POST['spesialis']) ? trim($_POST['spesialis']) : '';
  $klinik = isset($_POST['klinik']) ? trim($_POST['klinik']) : '';
  $foto = isset($_POST['foto']) ? trim($_POST['foto']) : '';

  $errors = [];

  // Validasi Sederhana
  if (empty($namaDokter)) {
    $errors[] = 'Nama dokter wajib diisi!';
  }
  if (empty($spesialis)) {
    $errors[] = 'Spesialisasi wajib diisi!';
  }

  if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }

  try {
    $sql = "INSERT INTO dokter (nama_dokter, spesialis, klinik, foto) VALUES (:nama, :spesialis, :klinik, :foto)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nama' => $namaDokter,
      ':spesialis' => $spesialis,
      ':klinik' => $klinik,
      ':foto' => $foto
    ]);

    $_SESSION['success_message'] = 'Data dokter berhasil ditambahkan!';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;

  } catch (PDOException $e) {
    error_log("Error adding doctor: " . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menyimpan data dokter ke database.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }
} else {
  header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
  exit;
}