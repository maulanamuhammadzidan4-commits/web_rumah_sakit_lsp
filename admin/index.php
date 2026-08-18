<?php
session_start();

// Import konfigurasi dan koneksi database
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../backend/config/database.php';

#---------------------------------------------------
# 1. CEK AUTENTIKASI & OTORISASI (RBAC)            |
#---------------------------------------------------
// Pastikan user sudah login
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

// Ambil data user dari session
$currentUser = $_SESSION['user'] ?? [];
$userRoles   = $currentUser['roles'] ?? [];

// Cek apakah user memiliki role 'admin' atau 'super_admin'
// Adjust nama role sesuai dengan yang ada pada database kamu (misal: 'Admin', 'admin', 'Super Admin')
$isAdmin = false;
foreach ($userRoles as $role) {
    if (in_array(strtolower($role), ['admin', 'super admin', 'super_admin'], true)) {
        $isAdmin = true;
        break;
    }
}

// Jika bukan admin, tolak akses (Forbidden)
if (!$isAdmin) {
    http_response_code(403);
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2>403 - Akses Ditolak</h2>";
    echo "<p>Anda tidak memiliki izin (role Admin) untuk mengakses halaman ini.</p>";
    echo "<a href='" . htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/home.php', ENT_QUOTES, 'UTF-8') . "'>Kembali ke Beranda</a>";
    echo "</div>";
    exit;
} else {
    header("Location: " . BASE_URL . 'admin/dashboard/admin-dashboard.php');
    exit;
}

?>