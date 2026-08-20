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

$galleryList = $pdo->query(
    'SELECT id, file_name, title, description FROM gallery ORDER BY id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$baseUrl = rtrim(BASE_URL, '/');
$dashboardUrl = $baseUrl . '/admin/dashboard/admin-dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl . '/frontend/assets/css/admin.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex flex-col md:flex-row">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        <main class="flex-1 p-6 md:p-10">
            <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 pb-4 border-b border-gray-200">
                <div>
                    <p class="text-sm font-medium text-emerald-600 mb-1">MANAJEMEN DATA</p>
                    <h1 class="text-2xl font-bold text-gray-800">Kelola Galeri</h1>
                    <p class="text-sm text-gray-500 mt-1">Tambah, ubah, dan hapus gambar galeri rumah sakit.</p>
                </div>
                <a href="<?= htmlspecialchars($dashboardUrl . '#gallery-section', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition"><i class="fas fa-arrow-left"></i><span>Kembali ke Dashboard</span></a>
            </header>

            <?php if (!empty($_SESSION['success_message'])): ?><div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r"><?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['success_message']); endif; ?>
            <?php if (!empty($_SESSION['error_message'])): ?><div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-r"><?= htmlspecialchars(strip_tags($_SESSION['error_message']), ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['error_message']); endif; ?>

            <section id="tambah-galeri" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" aria-labelledby="add-gallery-title">
                <div class="flex items-center gap-3 mb-5"><div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg"><i class="fas fa-plus"></i></div>
                    <div>
                        <h2 id="add-gallery-title" class="text-lg font-bold text-gray-800">Tambah Galeri</h2>
                        <p class="text-sm text-gray-500">Isi semua data untuk menambahkan gambar baru.</p>
                    </div>
                </div>
                <form action="<?= htmlspecialchars($baseUrl . '/backend/forms/process-add-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar</label>
                        <div class="gallery-upload">
                            <input id="gallery-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
                            <label for="gallery-image" class="upload-box"><span class="upload-icon"><i class="fas fa-upload"></i></span>
                            <span id="gallery-file-name">Pilih gambar galeri</span></label></div><img id="gallery-preview" class="gallery-preview mt-3" src="" alt="Preview gambar galeri">
                        </div>
                    <div>
                        <label for="gallery-title" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <input id="gallery-title" type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Masukkan judul gambar">
                    </div>
                    <div>
                        <label for="gallery-description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea id="gallery-description" name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Masukkan deskripsi"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                            <i class="fas fa-save"></i>Simpan Galeri
                        </button>
                        </div>
                </form>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" aria-labelledby="gallery-list-title">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 id="gallery-list-title" class="text-lg font-bold text-gray-800">Daftar Galeri</h2>
                        <p class="text-sm text-gray-500 mt-1">Terdapat <?= number_format(count($galleryList)) ?> gambar.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <caption class="sr-only">Daftar galeri rumah sakit</caption>
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
                            <?php if ($galleryList): ?>
                                <?php foreach ($galleryList as $gallery): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $gallery['id'] ?></td>
                                        <td class="py-3 px-3"><img src="<?= htmlspecialchars($baseUrl . '/frontend/assets/img/gallery/' . $gallery['file_name'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?>" class="w-24 h-16 object-cover rounded-lg border border-gray-200"></td>
                                        <td class="py-3 px-3 font-medium text-gray-800"><?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3 max-w-sm"><?= htmlspecialchars($gallery['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3 text-right">
                                            <div class="flex justify-end gap-2">
                                                <details class="relative">
                                                    <summary class="list-none cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200"><i class="fas fa-pen"></i>Edit</summary>
                                                    <div class="absolute right-0 z-10 mt-2 w-80 bg-white border border-gray-200 rounded-lg shadow-xl p-4 text-left">
                                                        <form action="<?= htmlspecialchars($baseUrl . '/backend/forms/process-edit-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" enctype="multipart/form-data" class="space-y-3">
                                                            <input type="hidden" name="id" value="<?= (int) $gallery['id'] ?>">
                                                            <input type="hidden" name="current_file_name" value="<?= htmlspecialchars($gallery['file_name'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <label class="block text-xs font-medium text-gray-700">Ganti gambar<div class="gallery-upload mt-1">
                                                                <input id="edit-gallery-<?= (int) $gallery['id'] ?>" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                                                                <label for="edit-gallery-<?= (int) $gallery['id'] ?>" class="upload-box">
                                                                    <span class="upload-icon"><i class="fas fa-upload"></i></span>
                                                                    <span id="edit-gallery-name-<?= (int) $gallery['id'] ?>">Pilih file baru (opsional)</span>
                                                                </label>
                                                            </div>
                                                        </label>
                                                        <label class="block text-xs font-medium text-gray-700">
                                                            Judul
                                                            <input type="text" name="title" value="<?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?>" required class="w-full mt-1 px-2 py-1.5 border border-gray-300 rounded" placeholder="Masukkan judul gambar">
                                                        </label>
                                                        <label class="block text-xs font-medium text-gray-700">
                                                            Deskripsi
                                                            <textarea name="description" rows="3" required class="w-full mt-1 px-2 py-1.5 border border-gray-300 rounded" placeholder="Masukkan deskripsi gambar"><?= htmlspecialchars($gallery['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                                        </label>
                                                        <button type="submit" class="w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Simpan Perubahan</button>
                                                    </form>
                                                </div>
                                            </details>
                                            <form action="<?= htmlspecialchars($baseUrl . '/backend/forms/process-delete-gallery.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus galeri ini?')">
                                                <input type="hidden" name="id" value="<?= (int) $gallery['id'] ?>">
                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200">
                                                    <i class="fas fa-trash"></i>Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">Belum ada data galeri.</td>
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
    function bindFilePreview(inputId, labelId, previewId, fallback) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            const label = document.getElementById(labelId);
            const preview = document.getElementById(previewId);
            label.textContent = file.name || fallback;
            if (!preview) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.add('is-visible');
            };
            reader.readAsDataURL(file);
        });
    }

    bindFilePreview('gallery-image', 'gallery-file-name', 'gallery-preview', 'Pilih gambar galeri');
    document.querySelectorAll('input[id^="edit-gallery-"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const label = document.getElementById('edit-gallery-name-' + this.id.replace('edit-gallery-', ''));
            if (label && this.files[0]) label.textContent = this.files[0].name;
        });
    });
</script>
</html>
