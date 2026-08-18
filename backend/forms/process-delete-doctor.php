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
# PROSES HAPUS DATA DOKTER                        |
#-------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  if ($id <= 0) {
    $_SESSION['error_message'] = 'ID dokter tidak valid!';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }

  try {
    $sql = "DELETE FROM dokter WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    $_SESSION['success_message'] = 'Data dokter berhasil dihapus!';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;

  } catch (PDOException $e) {
    error_log("Error deleting doctor: " . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menghapus data dokter dari database.';
    header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
  }
} else {
  header('Location: ' . BASE_URL . 'admin/dashboard/admin-dashboard.php');
  exit;
}