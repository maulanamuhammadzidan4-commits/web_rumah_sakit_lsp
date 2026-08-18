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
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h2>403 - Akses Ditolak</h2>";
    echo "<p>Anda tidak memiliki izin (role Admin) untuk mengakses halaman ini.</p>";
    echo "<a href='" . htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/home.php', ENT_QUOTES, 'UTF-8') . "'>Kembali ke Beranda</a>";
    echo "</div>";
    exit;
}

#---------------------------------------------------
# 2. QUERY DATA STATISTIK, APPOINTMENTS, DOKTER & USER |
#---------------------------------------------------
$totalUsers = 0;
$totalDokter = 0;
$totalAppointments = 0;
$appointments = [];
$doctorsList = [];
$usersList = [];
$rolesList = [];
$galleryList = [];

try {
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalDokter = (int) $pdo->query("SELECT COUNT(*) FROM dokter")->fetchColumn();
    $totalAppointments = (int) $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();

    $appointments = $pdo->query(
        "SELECT a.id_appointment AS id,
                u.username AS nama_pasien,
                d.nama_dokter,
                a.klinik
         FROM appointments a
         LEFT JOIN users u ON u.user_id = a.id_user
         LEFT JOIN dokter d ON d.id = a.id_dokter
         ORDER BY a.id_appointment DESC
         LIMIT 5"
    )->fetchAll(PDO::FETCH_ASSOC);

    $doctorsList = $pdo->query(
        "SELECT id, nama_dokter, spesialis, klinik, foto
         FROM dokter
         ORDER BY id DESC
         LIMIT 10"
    )->fetchAll(PDO::FETCH_ASSOC);

    $rolesList = $pdo->query(
        "SELECT id, role_name
         FROM roles
         ORDER BY id ASC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $usersList = $pdo->query(
        "SELECT u.user_id,
                u.username,
                u.email,
                MIN(r.id) AS primary_role_id,
                GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_name SEPARATOR ', ') AS roles_list
         FROM users u
         LEFT JOIN user_roles ur ON ur.user_id = u.user_id
         LEFT JOIN roles r ON r.id = ur.role_id
         GROUP BY u.user_id, u.username, u.email
         ORDER BY u.user_id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);

    $galleryList = $pdo->query(
        "SELECT id, file_name, title, description
         FROM gallery
         ORDER BY id DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching admin stats: " . $e->getMessage());
}

$successMsg = $_SESSION['success_message'] ?? null;
$errorMsg = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Panel Kendali</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gallery-upload {
            position: relative;
            display: block;
        }

        .gallery-upload input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .gallery-upload .upload-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            min-height: 72px;
            padding: 0.9rem 1rem;
            border: 2px dashed #a7f3d0;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
            color: #065f46;
            font-weight: 600;
            transition: all 0.2s ease;
            text-align: center;
        }

        .gallery-upload .upload-box:hover {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdfa 100%);
            transform: translateY(-1px);
        }

        .gallery-upload .upload-icon {
            width: 2.2rem;
            height: 2.2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: rgba(16, 185, 129, 0.12);
        }

        .gallery-preview {
            display: none;
            width: 100%;
            max-height: 260px;
            object-fit: contain;
            border-radius: 0.9rem;
            border: 1px solid #dbe4e8;
            background: #f8fafc;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        }

        .gallery-preview.is-visible {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">

        <!-- SIDEBAR -->
        <aside class="w-full md:w-64 bg-slate-800 text-white flex-shrink-0">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-xl font-bold tracking-wider text-emerald-400">ADMIN PANEL</h1>
                <p class="text-xs text-slate-400 mt-1">Sistem Manajemen Rumah Sakit</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="#dashboard-section" class="flex items-center space-x-3 px-4 py-3 bg-emerald-600 rounded-lg text-white font-medium">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#users-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-users w-5"></i>
                    <span>Kelola User</span>
                </a>
                <a href="#gallery-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-images w-5"></i>
                    <span>Kelola Galeri</span>
                </a>
                <a href="#doctors-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-user-md w-5"></i>
                    <span>Kelola Dokter</span>
                </a>
                <a href="#appointments-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Janji Temu</span>
                </a>
                <div class="pt-6 border-t border-slate-700">
                    <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/logout.php', ENT_QUOTES, 'UTF-8'); ?>"
                        class="flex items-center space-x-3 px-4 py-3 text-red-400 hover:bg-slate-700 rounded-lg transition">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Keluar</span>
                    </a>
                    <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/home.php', ENT_QUOTES, 'UTF-8'); ?>"
                        class="flex items-center space-x-3 px-4 py-3 text-blue-400 hover:bg-slate-700 rounded-lg transition">
                        <i class="fas fa-home w-5"></i>
                        <span>Halaman Depan</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="flex-1 p-6 md:p-10" id="dashboard-section">

            <!-- HEADER -->
            <header class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 pb-4 border-b border-gray-200">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard Utama</h2>
                    <p class="text-sm text-gray-500">Selamat datang kembali,
                        <strong class="text-slate-800"><?= htmlspecialchars($currentUser['username'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?></strong>!
                    </p>
                </div>
            </header>

            <?php if ($successMsg): ?>
                <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r shadow-sm">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMsg): ?>
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-r shadow-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <!-- STATISTIC CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Terdaftar</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format($totalUsers) ?></p>
                    </div>
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-xl"><i class="fas fa-users text-2xl"></i></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Dokter Aktif</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format($totalDokter) ?></p>
                    </div>
                    <div class="p-4 bg-emerald-50 text-emerald-600 rounded-xl"><i class="fas fa-user-md text-2xl"></i></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class=\"text-sm font-medium text-gray-500\">Janji Temu</p>
                        <p class=\"text-3xl font-bold text-gray-800 mt-2\"><?= number_format($totalAppointments) ?></p>
                    </div>
                    <div class=\"p-4 bg-purple-50 text-purple-600 rounded-xl\"><i class=\"fas fa-calendar-check text-2xl\"></i></div>
                </div>
            </div>

            <!-- PANEL USER -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" id="users-section">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th class="py-3 px-3">ID</th>
                                <th class="py-3 px-3">Username</th>
                                <th class="py-3 px-3">Email</th>
                                <th class="py-3 px-3">Role</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if (!empty($usersList)): ?>
                                <?php foreach ($usersList as $user): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $user['user_id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800">
                                            <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-1 bg-sky-50 text-sky-700 text-xs rounded font-medium">
                                                <?= htmlspecialchars($user['roles_list'] ?: 'user', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex justify-end gap-2">
                                                <button type="button"
                                                        class="px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200 transition"
                                                        data-id="<?= (int) $user['user_id'] ?>"
                                                        data-username="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-role-id="<?= htmlspecialchars((string)($user['primary_role_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="openUserEditModal(this)">
                                                    Edit
                                                </button>

                                                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-delete-user.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>">
                                                    <button type="submit" class="px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400 italic">Belum ada data pengguna.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL GALERI -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" id="gallery-section">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Galeri</h3>
                    <button type="button" onclick="openGalleryModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg transition flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Galeri</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th class="py-3 px-3">ID</th>
                                <th class="py-3 px-3">Gambar</th>
                                <th class="py-3 px-3">Judul</th>
                                <th class="py-3 px-3">Deskripsi</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if (!empty($galleryList)): ?>
                                <?php foreach ($galleryList as $gallery): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $gallery['id'] ?></td>
                                        <td class="py-3 px-3">
                                            <?php if (!empty($gallery['file_name'])): ?>
                                                <img src="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/gallery/' . $gallery['file_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                    alt="<?= htmlspecialchars($gallery['title'] ?? 'Galeri', ENT_QUOTES, 'UTF-8') ?>"
                                                    class="w-20 h-16 object-cover rounded-lg border border-gray-200">
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-gray-800">
                                            <?= htmlspecialchars($gallery['title'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600 max-w-xs">
                                            <?= htmlspecialchars($gallery['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex justify-end gap-2">
                                                <button type="button"
                                                        class="px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200 transition"
                                                        data-id="<?= (int) $gallery['id'] ?>"
                                                        data-title="<?= htmlspecialchars($gallery['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-description="<?= htmlspecialchars($gallery['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-file_name="<?= htmlspecialchars($gallery['file_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="openGalleryEditModal(this)">
                                                    Edit
                                                </button>

                                                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-delete-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus item galeri ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $gallery['id'] ?>">
                                                    <button type="submit" class="px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400 italic">Belum ada data galeri.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PANEL JANJI TEMU -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" id="appointments-section">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Janji Temu</h3>
                    <button type="button" onclick="openAppointmentModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg transition flex items-center space-x-2">
                        <i class="fas fa-plus w-4"></i>
                        <span>Tambah Janji Temu</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th class="py-3 px-3">ID</th>
                                <th class="py-3 px-3">Pasien</th>
                                <th class="py-3 px-3">Dokter</th>
                                <th class="py-3 px-3">Klinik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $item['id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800">
                                            <?= htmlspecialchars($item['nama_pasien'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600">
                                            <?= htmlspecialchars($item['nama_dokter'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600">
                                            <?= htmlspecialchars($item['klinik'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-gray-400 italic">Belum ada data janji temu.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL DOKTER & TOMBOL TAMBAH -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" id="doctors-section">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Tenaga Medis / Dokter</h3>
                    <button type="button" onclick="openModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-lg transition flex items-center space-x-2">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Dokter</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th class="py-3 px-3">ID</th>
                                <th class="py-3 px-3">Nama Dokter</th>
                                <th class="py-3 px-3">Spesialisasi</th>
                                <th class="py-3 px-3">Klinik</th>
                                <th class="py-3 px-3">Foto</th>
                                <th class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if (!empty($doctorsList)): ?>
                                <?php foreach ($doctorsList as $doc): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $doc['id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800">
                                            <?= htmlspecialchars($doc['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs rounded font-medium">
                                                <?= htmlspecialchars($doc['spesialis'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3">
                                            <?= htmlspecialchars($doc['klinik'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <?php if (!empty($doc['foto'])): ?>
                                                <img src="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/doctors/' . $doc['foto'], ENT_QUOTES, 'UTF-8') ?>"
                                                    alt="<?= htmlspecialchars($doc['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>"
                                                    class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex justify-end gap-2">
                                                <button type="button"
                                                        class="px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200 transition"
                                                        data-id="<?= (int) $doc['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($doc['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-spesialis="<?= htmlspecialchars($doc['spesialis'], ENT_QUOTES, 'UTF-8') ?>"
                                                        data-klinik="<?= htmlspecialchars($doc['klinik'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        data-foto="<?= htmlspecialchars($doc['foto'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="openEditModal(this)">
                                                    Edit
                                                </button>

                                                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-delete-doctor.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus dokter ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>">
                                                    <button type="submit" class="px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-gray-400 italic">Belum ada data dokter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL FORM TAMBAH GALERI -->
    <div id="galleryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeGalleryModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Tambah Item Galeri Baru</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-add-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Gambar</label>
                    <div class="gallery-upload">
                        <input id="galleryImageInput" type="file" name="image" accept="image/*" required>
                        <label for="galleryImageInput" class="upload-box">
                            <span class="upload-icon"><i class="fas fa-upload"></i></span>
                            <span id="galleryFileName">Pilih gambar galeri</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preview Gambar</label>
                    <img id="galleryAddPreview" class="gallery-preview" src="" alt="Preview galeri baru">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input type="text" name="title" required placeholder="Ruang Perawatan"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="4" required placeholder="Area ruang perawatan yang nyaman..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeGalleryModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                        Simpan Galeri
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FORM EDIT GALERI -->
    <div id="editGalleryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeGalleryEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Data Galeri</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-edit-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="edit_gallery_id">
                <input type="hidden" name="current_file_name" id="edit_gallery_current_file_name">

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Saat Ini</label>
                    <img id="edit_gallery_preview" src="" alt="Preview galeri" class="gallery-preview">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ganti Gambar (opsional)</label>
                    <div class="gallery-upload">
                        <input id="editGalleryImageInput" type="file" name="image" accept="image/*">
                        <label for="editGalleryImageInput" class="upload-box">
                            <span class="upload-icon"><i class="fas fa-upload"></i></span>
                            <span id="editGalleryFileName">Pilih file baru (opsional)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <input type="text" id="edit_gallery_title" name="title" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="edit_gallery_description" name="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeGalleryEditModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH DOKTER -->
    <div id="doctorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Tambah Data Dokter Baru</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-add-doctor.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Dokter</label>
                    <input type="text" name="nama_dokter" required placeholder="dr. Ahmad Fajar, Sp.PD"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi</label>
                    <input type="text" name="spesialis" required placeholder="Spesialis Penyakit Dalam"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                    <input type="text" name="klinik" placeholder="Klinik Jantung"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Foto Dokter</label>
                    <div class="gallery-upload">
                        <input id="doctorImageInput" type="file" name="image" accept="image/*" required>
                        <label for="doctorImageInput" class="upload-box">
                            <span class="upload-icon"><i class="fas fa-upload"></i></span>
                            <span id="doctorFileName">Pilih foto dokter</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preview Foto</label>
                    <img id="doctorAddPreview" class="gallery-preview" src="" alt="Preview foto dokter baru">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                        Simpan Dokter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FORM EDIT DOKTER -->
    <div id="editDoctorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Data Dokter</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-edit-doctor.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" id="edit_id">

                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Saat Ini</label>
                    <img id="edit_doctor_preview" src="" alt="Preview foto dokter" class="gallery-preview">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ganti Foto Dokter (opsional)</label>
                    <div class="gallery-upload">
                        <input id="editDoctorImageInput" type="file" name="image" accept="image/*">
                        <label for="editDoctorImageInput" class="upload-box">
                            <span class="upload-icon"><i class="fas fa-upload"></i></span>
                            <span id="editDoctorFileName">Pilih file baru (opsional)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Dokter</label>
                    <input type="text" id="edit_nama_dokter" name="nama_dokter" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi</label>
                    <input type="text" id="edit_spesialis" name="spesialis" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                    <input type="text" id="edit_klinik" name="klinik"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FORM EDIT USER -->
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeUserEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Data Pengguna</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-edit-user.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="space-y-4">
                <input type="hidden" name="id" id="edit_user_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="edit_user_username" name="username" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="edit_user_email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select id="edit_user_role" name="role_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach ($rolesList as $role): ?>
                            <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeUserEditModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL FORM TAMBAH JANJI TEMU -->
    <div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 relative">
            <button type="button" onclick="closeAppointmentModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Tambah Janji Temu Baru</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-add-appointment.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Pasien</label>
                    <select name="id_user" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Pasien --</option>
                        <?php foreach ($usersList as $user): ?>
                            <option value="<?= (int) $user['user_id'] ?>"><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Dokter</label>
                    <select name="id_dokter" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach ($doctorsList as $doctor): ?>
                            <option value="<?= (int) $doctor['id'] ?>"><?= htmlspecialchars($doctor['nama_dokter'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                    <input type="text" name="klinik" required placeholder="Klinik Jantung"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAppointmentModal()" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                        Simpan Janji Temu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showImagePreview(input, previewElement, labelElement, fallbackText) {
            const file = input.files && input.files[0];
            if (!file) {
                if (previewElement) {
                    previewElement.classList.remove('is-visible');
                    previewElement.src = '';
                }
                if (labelElement) {
                    labelElement.textContent = fallbackText;
                }
                return;
            }

            const reader = new FileReader();
            reader.onload = function (event) {
                if (previewElement) {
                    previewElement.src = event.target.result;
                    previewElement.classList.add('is-visible');
                }
            };
            reader.readAsDataURL(file);

            if (labelElement) {
                labelElement.textContent = file.name;
            }
        }

        const addGalleryInput = document.getElementById('galleryImageInput');
        if (addGalleryInput) {
            addGalleryInput.addEventListener('change', function () {
                const label = document.getElementById('galleryFileName');
                const preview = document.getElementById('galleryAddPreview');
                showImagePreview(this, preview, label, 'Pilih gambar galeri');
            });
        }

        const doctorAddInput = document.getElementById('doctorImageInput');
        if (doctorAddInput) {
            doctorAddInput.addEventListener('change', function () {
                const label = document.getElementById('doctorFileName');
                const preview = document.getElementById('doctorAddPreview');
                showImagePreview(this, preview, label, 'Pilih foto dokter');
            });
        }

        const editGalleryInput = document.getElementById('editGalleryImageInput');
        if (editGalleryInput) {
            editGalleryInput.addEventListener('change', function () {
                const label = document.getElementById('editGalleryFileName');
                const preview = document.getElementById('edit_gallery_preview');
                showImagePreview(this, preview, label, 'Pilih file baru (opsional)');
            });
        }

        const editDoctorInput = document.getElementById('editDoctorImageInput');
        if (editDoctorInput) {
            editDoctorInput.addEventListener('change', function () {
                const label = document.getElementById('editDoctorFileName');
                const preview = document.getElementById('edit_doctor_preview');
                showImagePreview(this, preview, label, 'Pilih file baru (opsional)');
            });
        }

        function openGalleryModal() {
            document.getElementById('galleryModal').classList.remove('hidden');
            document.getElementById('galleryModal').classList.add('flex');
        }

        function closeGalleryModal() {
            document.getElementById('galleryModal').classList.add('hidden');
            document.getElementById('galleryModal').classList.remove('flex');
        }

        function openGalleryEditModal(button) {
            const modal = document.getElementById('editGalleryModal');
            const preview = document.getElementById('edit_gallery_preview');
            const fileName = button.dataset.file_name || '';
            const title = button.dataset.title || '';
            const description = button.dataset.description || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('edit_gallery_id').value = button.dataset.id || '';
            document.getElementById('edit_gallery_current_file_name').value = fileName;
            document.getElementById('edit_gallery_title').value = title;
            document.getElementById('edit_gallery_description').value = description;
            document.getElementById('editGalleryImageInput').value = '';
            document.getElementById('editGalleryFileName').textContent = fileName ? 'Ganti file: ' + fileName : 'Pilih file baru (opsional)';

            if (preview && fileName) {
                preview.src = '<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/gallery/', ENT_QUOTES, 'UTF-8') ?>' + fileName;
                preview.classList.add('is-visible');
            } else if (preview) {
                preview.src = '';
                preview.classList.remove('is-visible');
            }
        }

        function closeGalleryEditModal() {
            document.getElementById('editGalleryModal').classList.add('hidden');
            document.getElementById('editGalleryModal').classList.remove('flex');
        }

        function openModal() {
            document.getElementById('doctorModal').classList.remove('hidden');
            document.getElementById('doctorModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('doctorModal').classList.add('hidden');
            document.getElementById('doctorModal').classList.remove('flex');
        }

        function openEditModal(button) {
            const modal = document.getElementById('editDoctorModal');
            const preview = document.getElementById('edit_doctor_preview');
            const foto = button.dataset.foto || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('edit_id').value = button.dataset.id || '';
            document.getElementById('edit_nama_dokter').value = button.dataset.nama || '';
            document.getElementById('edit_spesialis').value = button.dataset.spesialis || '';
            document.getElementById('edit_klinik').value = button.dataset.klinik || '';
            document.getElementById('editDoctorImageInput').value = '';
            document.getElementById('editDoctorFileName').textContent = foto ? 'Ganti file: ' + foto : 'Pilih file baru (opsional)';

            if (preview && foto) {
                preview.src = '<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/doctors/', ENT_QUOTES, 'UTF-8') ?>' + foto;
                preview.classList.add('is-visible');
            } else if (preview) {
                preview.src = '';
                preview.classList.remove('is-visible');
            }
        }

        function closeEditModal() {
            document.getElementById('editDoctorModal').classList.add('hidden');
            document.getElementById('editDoctorModal').classList.remove('flex');
        }

        function openUserEditModal(button) {
            const modal = document.getElementById('editUserModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            const roleSelect = document.getElementById('edit_user_role');
            const selectedRoleId = button.dataset.roleId || '';
            const roleOptions = Array.from(roleSelect.options);

            roleOptions.forEach((option) => {
                option.selected = option.value === selectedRoleId;
            });

            document.getElementById('edit_user_id').value = button.dataset.id || '';
            document.getElementById('edit_user_username').value = button.dataset.username || '';
            document.getElementById('edit_user_email').value = button.dataset.email || '';
        }

        function closeUserEditModal() {
            document.getElementById('editUserModal').classList.add('hidden');
            document.getElementById('editUserModal').classList.remove('flex');
        }

        function openAppointmentModal() {
            document.getElementById('appointmentModal').classList.remove('hidden');
            document.getElementById('appointmentModal').classList.add('flex');
        }

        function closeAppointmentModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
            document.getElementById('appointmentModal').classList.remove('flex');
        }
    </script>
</body>
</html>
