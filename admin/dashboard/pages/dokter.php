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

$doctorsList = $pdo->query(
    "SELECT id, nama_dokter, spesialis, klinik, foto
    FROM dokter
    ORDER BY id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$editDoctor = null;
if (isset($_GET['edit'])) {
    $editStatement = $pdo->prepare('SELECT id, nama_dokter, spesialis, klinik, foto FROM dokter WHERE id = :id LIMIT 1');
    $editStatement->execute([':id' => (int) $_GET['edit']]);
    $editDoctor = $editStatement->fetch(PDO::FETCH_ASSOC) ?: null;
}

$dashboardUrl = rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Dokter - Admin</title>
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
                    <h1 class="text-2xl font-bold text-gray-800">Detail Dokter</h1>
                    <p class="text-sm text-gray-500 mt-1">Informasi lengkap seluruh tenaga medis rumah sakit.</p>
                </div>
                <a href="<?= htmlspecialchars($dashboardUrl . '#doctors-section', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition"><i class="fas fa-arrow-left"></i><span>Kembali ke Dashboard</span></a>
            </header>

            <?php if (!empty($_SESSION['success_message'])): ?><div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r"><?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['success_message']); endif; ?>
            <?php if (!empty($_SESSION['error_message'])): ?><div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-r"><?= htmlspecialchars(strip_tags($_SESSION['error_message']), ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['error_message']); endif; ?>

            <section id="tambah-dokter" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" aria-labelledby="doctor-form-title">
                <div class="flex items-center gap-3 mb-5"><div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fas fa-user-md"></i></div><div><h2 id="doctor-form-title" class="text-lg font-bold text-gray-800"><?= $editDoctor ? 'Edit Dokter' : 'Tambah Dokter' ?></h2><p class="text-sm text-gray-500">Kelola identitas, spesialisasi, klinik, dan foto dokter.</p></div></div>
                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . ($editDoctor ? '/backend/forms/process-edit-doctor.php' : '/backend/forms/process-add-doctor.php'), ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <?php if ($editDoctor): ?><input type="hidden" name="id" value="<?= (int) $editDoctor['id'] ?>"><?php endif; ?>
                    <div>
                        <label for="doctor-name" class="block text-sm font-medium text-gray-700 mb-1">Nama Dokter</label>
                        <input id="doctor-name" type="text" name="nama_dokter" required value="<?= htmlspecialchars($editDoctor['nama_dokter'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="doctor-specialty" class="block text-sm font-medium text-gray-700 mb-1">Spesialisasi</label>
                        <input id="doctor-specialty" type="text" name="spesialis" required value="<?= htmlspecialchars($editDoctor['spesialis'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="doctor-clinic" class="block text-sm font-medium text-gray-700 mb-1">Klinik</label>
                        <input id="doctor-clinic" type="text" name="klinik" value="<?= htmlspecialchars($editDoctor['klinik'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Foto <?= $editDoctor ? '(opsional)' : '' ?></label>
                        <div class="gallery-upload">
                            <input id="doctor-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= $editDoctor ? '' : 'required' ?>>
                            <label for="doctor-image" class="upload-box">
                                <span class="upload-icon"><i class="fas fa-upload"></i></span>
                                <span id="doctor-file-name"><?= $editDoctor && !empty($editDoctor['foto']) ? 'Ganti file: ' . htmlspecialchars($editDoctor['foto'], ENT_QUOTES, 'UTF-8') : 'Pilih foto dokter' ?></span>
                            </label>
                        </div>
                        <img id="doctor-preview" class="gallery-preview mt-3<?= $editDoctor && !empty($editDoctor['foto']) ? ' is-visible' : '' ?>" src="<?= $editDoctor && !empty($editDoctor['foto']) ? htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/doctors/' . $editDoctor['foto'], ENT_QUOTES, 'UTF-8') : '' ?>" alt="Preview foto dokter">
                    </div>
                    <div class="md:col-span-2 flex justify-end gap-3"><a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/dokter.php#tambah-dokter', ENT_QUOTES, 'UTF-8') ?>" class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg">Batal</a><button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg"><i class="fas fa-save"></i><?= $editDoctor ? 'Simpan Perubahan' : 'Simpan Dokter' ?></button></div>
                </form>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8" aria-label="Ringkasan dokter">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Dokter</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format(count($doctorsList)) ?></p>
                    </div>
                    <div class="p-4 bg-emerald-50 text-emerald-600 rounded-xl">
                        <i class="fas fa-user-md text-2xl"></i>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Data Ditampilkan</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-2"><?= number_format(count($doctorsList)) ?></p>
                    </div>
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-xl">
                        <i class="fas fa-table-list text-2xl"></i>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" aria-labelledby="doctors-title">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 id="doctors-title" class="text-lg font-bold text-gray-800">Daftar Semua Dokter</h2>
                        <p class="text-sm text-gray-500 mt-1">Nama, spesialisasi, klinik, dan foto dokter.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 self-start px-3 py-2 bg-slate-50 text-slate-600 text-sm rounded-lg"><i class="fas fa-database"></i><?= number_format(count($doctorsList)) ?> data</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <caption class="sr-only">Daftar lengkap dokter rumah sakit</caption>
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th scope="col" class="py-3 px-3">ID</th>
                                <th scope="col" class="py-3 px-3">Nama Dokter</th>
                                <th scope="col" class="py-3 px-3">Spesialisasi</th>
                                <th scope="col" class="py-3 px-3">Klinik</th>
                                <th scope="col" class="py-3 px-3">Foto</th>
                                <th scope="col" class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if ($doctorsList): ?>
                                <?php foreach ($doctorsList as $doctor): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $doctor['id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800"><?= htmlspecialchars($doctor['nama_dokter'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-xs rounded font-medium"><?= htmlspecialchars($doctor['spesialis'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </td>
                                        <td class="py-3 px-3"><?= htmlspecialchars($doctor['klinik'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3">
                                            <?php if (!empty($doctor['foto'])): ?>
                                                <img src="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/assets/img/doctors/' . $doctor['foto'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($doctor['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <div class="flex justify-end gap-2">
                                                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/dokter.php?edit=' . (int) $doctor['id'] . '#tambah-dokter', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200">
                                                    <i class="fas fa-pen"></i>Edit
                                                </a>
                                                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-delete-doctor.php', ENT_QUOTES, 'UTF=8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus dokter ini?')">
                                                    <input type="hidden" name="id" value="<?= (int) $doctor['id'] ?>">
                                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py=1.5 bg-red=100 text-red=700 rounded text-xs font-medium hover:bg-red=200">
                                                        <i class="fas fa-trash"></i>Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py=8 text-center text-gray=400 italic">Belum ada data dokter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
<script>
    const doctorInput = document.getElementById('doctor-image');
    if (doctorInput) {
        doctorInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const preview = document.getElementById('doctor-preview');
            const label = document.getElementById('doctor-file-name');
            if (!file) return;
            label.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.add('is-visible');
            };
            reader.readAsDataURL(file);
        });
    }
</script>
</html>
