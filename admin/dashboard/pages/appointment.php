<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../backend/config/database.php';

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

$appointments = $pdo->query(
    "SELECT a.id_appointment AS id, u.username AS nama_pasien,
            d.nama_dokter, a.klinik, a.tanggal_temu
    FROM appointments a
    LEFT JOIN users u ON u.user_id = a.id_user
    LEFT JOIN dokter d ON d.id = a.id_dokter
    ORDER BY a.id_appointment DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$usersList = $pdo->query('SELECT user_id, username FROM users ORDER BY username ASC')->fetchAll(PDO::FETCH_ASSOC);
$doctorsList = $pdo->query('SELECT id, nama_dokter FROM dokter ORDER BY nama_dokter ASC')->fetchAll(PDO::FETCH_ASSOC);
$editAppointment = null;
if (isset($_GET['edit'])) {
    $editStatement = $pdo->prepare('SELECT id_appointment, id_user, id_dokter, klinik, tanggal_temu FROM appointments WHERE id_appointment = :id LIMIT 1');
    $editStatement->execute([':id' => (int) $_GET['edit']]);
    $editAppointment = $editStatement->fetch(PDO::FETCH_ASSOC) ?: null;
}

$baseUrl = rtrim(BASE_URL, '/');
$dashboardUrl = $baseUrl . '/admin/dashboard/admin-dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Janji Temu - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/css/admin.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="flex-1 p-6 md:p-10">
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 pb-4 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-emerald-600 mb-1">MANAJEMEN DATA</p>
                    <h1 class="text-2xl font-bold text-gray-800">Detail Janji Temu</h1>
                    <p class="text-sm text-gray-500 mt-1">Informasi lengkap seluruh jadwal janji temu pasien.</p>
                </div>
                <a href="<?= htmlspecialchars($dashboardUrl . '#appointments-section', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition"><i class="fas fa-arrow-left"></i><span>Kembali ke Dashboard</span></a>
            </header>

            <?php if (!empty($_SESSION['success_message'])): ?><div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r"><?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['success_message']); endif; ?>
            <?php if (!empty($_SESSION['error_message'])): ?><div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-r"><?= htmlspecialchars(strip_tags($_SESSION['error_message']), ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['error_message']); endif; ?>

            <section id="tambah-janji-temu" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" aria-labelledby="appointment-form-title">
                <div class="flex items-center gap-3 mb-5"><div class="p-3 bg-purple-50 text-purple-600 rounded-lg"><i class="fas fa-calendar-plus"></i></div><div><h2 id="appointment-form-title" class="text-lg font-bold text-gray-800"><?= $editAppointment ? 'Edit Janji Temu' : 'Tambah Janji Temu' ?></h2><p class="text-sm text-gray-500">Kelola pasien, dokter, klinik, dan waktu kunjungan.</p></div></div>
                <form action="<?= htmlspecialchars($baseUrl . ($editAppointment ? '/backend/forms/process-edit-appointment.php' : '/backend/forms/process-admin-add-appointment.php'), ENT_QUOTES, 'UTF-8') ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php if ($editAppointment): ?><input type="hidden" name="id" value="<?= (int) $editAppointment['id_appointment'] ?>"><?php endif; ?>
                    <div>
                        <label for="appointment-user" class="block text-sm font-medium text-gray-700 mb-1">Pasien</label>
                        <select id="appointment-user" name="id_user" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"><option value="">Pilih pasien</option>
                            <?php foreach ($usersList as $user): ?>
                                <option value="<?= (int) $user['user_id'] ?>" <?= (int) ($editAppointment['id_user'] ?? 0) === (int) $user['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    <div>
                        <label for="appointment-doctor" class="block text-sm font-medium text-gray-700 mb-1">Dokter</label>
                        <select id="appointment-doctor" name="id_dokter" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih dokter</option>
                            <?php foreach ($doctorsList as $doctor): ?>
                                <option value="<?= (int) $doctor['id'] ?>" <?= (int) ($editAppointment['id_dokter'] ?? 0) === (int) $doctor['id'] ? 'selected' : '' ?>><?= htmlspecialchars($doctor['nama_dokter'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="appointment-clinic" class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                        <input id="appointment-clinic" type="text" name="klinik" required value="<?= htmlspecialchars($editAppointment['klinik'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="appointment-date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal &amp; Waktu</label>
                        <input id="appointment-date" type="datetime-local" name="tanggal_temu" required value="<?= $editAppointment ? date('Y-m-d\\TH:i', strtotime($editAppointment['tanggal_temu'])) : '' ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div class="md:col-span-2 flex justify-end gap-3">
                        <a href="<?= htmlspecialchars($baseUrl . '/admin/dashboard/pages/appointment.php#tambah-janji-temu', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg">Batal</a>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg"><i class="fas fa-save"></i><?= $editAppointment ? 'Simpan Perubahan' : 'Simpan Janji Temu' ?></button>
                    </div>
                </form>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8" aria-label="Ringkasan janji temu">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Janji Temu</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format(count($appointments)) ?></p>
                    </div>
                <div class="p-4 bg-purple-50 text-purple-600 rounded-xl">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Data Ditampilkan</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-2"><?= number_format(count($appointments)) ?></p>
                    </div>
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fas fa-table-list text-2xl"></i>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" aria-labelledby="appointments-title">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 id="appointments-title" class="text-lg font-bold text-gray-800">Daftar Semua Janji Temu</h2>
                        <p class="text-sm text-gray-500 mt-1">Pasien, dokter, klinik, dan waktu kunjungan.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 self-start px-3 py-2 bg-slate-50 text-slate-600 text-sm rounded-lg">
                        <i class="fas fa-database"></i><?= number_format(count($appointments)) ?> data
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <caption class="sr-only">Daftar lengkap janji temu rumah sakit</caption>
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th scope="col" class="py-3 px-3">ID</th>
                                <th scope="col" class="py-3 px-3">Pasien</th>
                                <th scope="col" class="py-3 px-3">Dokter</th>
                                <th scope="col" class="py-3 px-3">Klinik</th>
                                <th scope="col" class="py-3 px-3">Tanggal &amp; Waktu</th>
                                <th scope="col" class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if ($appointments): ?>
                                <?php foreach ($appointments as $item): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $item['id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800"><?= htmlspecialchars($item['nama_pasien'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3"><?= htmlspecialchars($item['nama_dokter'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3"><?= htmlspecialchars($item['klinik'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3"><?= htmlspecialchars(!empty($item['tanggal_temu']) ? date('d M Y H:i', strtotime($item['tanggal_temu'])) : '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3">
                                            <div class="flex justify-end gap-2">
                                                <a href="<?= htmlspecialchars($baseUrl . '/admin/dashboard/pages/appointment.php?edit=' . (int) $item['id'] . '#tambah-janji-temu', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">
                                                    <i class="fas fa-pen"></i>Edit
                                                </a>
                                                <form action="<?= htmlspecialchars($baseUrl . '/backend/forms/process-delete-appointment.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus janji temu ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium">
                                                        <i class="fas fa-trash"></i>Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 italic">Belum ada data janji temu.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
