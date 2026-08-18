<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../backend/config/database.php';

#---------------------------------------------------
# 1. CEK AUTENTIKASI & OTORISASI (RBAC)            |
#---------------------------------------------------
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
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
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2>403 - Akses Ditolak</h2>";
    echo "<p>Anda tidak memiliki izin (role Admin) untuk mengakses halaman ini.</p>";
    echo "<a href='" . htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/home.php', ENT_QUOTES, 'UTF-8') . "'>Kembali ke Beranda</a>";
    echo "</div>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../../frontend/components/css.php'; ?>
</head>
<body>
    
</body>
</html>