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
# 2. QUERY DATA STATISTIK, REKAM MEDIS, DOKTER & USER |
#---------------------------------------------------
$totalUsers = 0;
$totalDokter = 0;
$totalRekam = 0;
$recentMedis = [];
$doctorsList = [];
$usersList = [];
$rolesList = [];

try {
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalDokter = (int) $pdo->query("SELECT COUNT(*) FROM dokter")->fetchColumn();
    $totalRekam = (int) $pdo->query("SELECT COUNT(*) FROM rekam_medis")->fetchColumn();

    $recentMedis = $pdo->query(
        "SELECT rm.id_rekam_medis AS id,
                rm.keluhan,
                u.username AS nama_pasien,
                CONCAT('Pasien #', rm.us_id) AS pasien_label
         FROM rekam_medis rm
         LEFT JOIN users u ON u.user_id = rm.us_id
         ORDER BY rm.id_rekam_medis DESC
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
                <a href="#doctors-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-user-md w-5"></i>
                    <span>Kelola Dokter</span>
                </a>
                <a href="#recent-medis-section" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-file-medical w-5"></i>
                    <span>Rekam Medis</span>
                </a>
                <div class="pt-6 border-t border-slate-700">
                    <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/logout.php', ENT_QUOTES, 'UTF-8') ?>"
                        class="flex items-center space-x-3 px-4 py-3 text-red-400 hover:bg-slate-700 rounded-lg transition">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Keluar</span>
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
                        <p class="text-sm font-medium text-gray-500">Rekam Medis</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format($totalRekam) ?></p>
                    </div>
                    <div class="p-4 bg-purple-50 text-purple-600 rounded-xl"><i class="fas fa-file-medical text-2xl"></i></div>
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

            <!-- PANEL REKAM MEDIS TERBARU -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" id="recent-medis-section">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-800">Rekam Medis Terbaru</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th class="py-3 px-3">ID</th>
                                <th class="py-3 px-3">Pasien</th>
                                <th class="py-3 px-3">Keluhan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if (!empty($recentMedis)): ?>
                                <?php foreach ($recentMedis as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $item['id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800">
                                            <?= htmlspecialchars($item['nama_pasien'] ?? $item['pasien_label'] ?? 'Pasien', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600">
                                            <?= htmlspecialchars($item['keluhan'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-gray-400 italic">Belum ada data rekam medis.</td>
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

    <!-- MODAL FORM TAMBAH DOKTER -->
    <div id="doctorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
            <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Tambah Data Dokter Baru</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-add-doctor.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="space-y-4">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama File Foto</label>
                    <input type="text" name="foto" placeholder="contoh: dr_ahmad.png"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
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
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
            <button type="button" onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-lg"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Data Dokter</h3>

            <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-edit-doctor.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="space-y-4">
                <input type="hidden" name="id" id="edit_id">

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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama File Foto</label>
                    <input type="text" id="edit_foto" name="foto"
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
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
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

    <script>
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
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            document.getElementById('edit_id').value = button.dataset.id || '';
            document.getElementById('edit_nama_dokter').value = button.dataset.nama || '';
            document.getElementById('edit_spesialis').value = button.dataset.spesialis || '';
            document.getElementById('edit_klinik').value = button.dataset.klinik || '';
            document.getElementById('edit_foto').value = button.dataset.foto || '';
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
    </script>
</body>
</html>
