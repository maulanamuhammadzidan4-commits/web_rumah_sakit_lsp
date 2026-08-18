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

  $errors = [];

  // Validasi Sederhana
  if (empty($namaDokter)) {
    $errors[] = 'Nama dokter wajib diisi!';
  }
  if (empty($spesialis)) {
    $errors[] = 'Spesialisasi wajib diisi!';
  }
  if (empty($_FILES['image']['name'])) {
    $errors[] = 'Foto dokter wajib diunggah!';
  }

  if (!empty($errors)) {
    $_SESSION['error_message'] = implode('<br>', $errors);
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }

  $uploadDir = __DIR__ . '/../../frontend/assets/img/doctors/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  $fileTmp = $_FILES['image']['tmp_name'] ?? '';
  $fileName = $_FILES['image']['name'];
  $fileType = $_FILES['image']['type'] ?? '';
  $fileError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;

  if ($fileError !== UPLOAD_ERR_OK || !is_uploaded_file($fileTmp)) {
    $_SESSION['error_message'] = 'Upload foto dokter gagal. Silakan coba lagi.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }

  $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
  if (!in_array($fileType, $allowedTypes, true)) {
    $_SESSION['error_message'] = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }

  $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
  $safeName = 'doctor_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
  $targetPath = $uploadDir . $safeName;

  if (!move_uploaded_file($fileTmp, $targetPath)) {
    $_SESSION['error_message'] = 'Gagal menyimpan foto dokter ke folder upload.';
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
      ':foto' => $safeName
    ]);

    $_SESSION['success_message'] = 'Data dokter berhasil ditambahkan!';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;

  } catch (PDOException $e) {
    error_log("Error adding doctor: " . $e->getMessage());
    if (file_exists($targetPath)) {
      unlink($targetPath);
    }
    $_SESSION['error_message'] = 'Gagal menyimpan data dokter ke database.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }
} else {
  header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
  exit;
}