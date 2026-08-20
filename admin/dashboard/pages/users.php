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

$usersList = $pdo->query(
    "SELECT u.user_id, u.username, u.email,
            GROUP_CONCAT(DISTINCT r.role_name ORDER BY r.role_name SEPARATOR ', ') AS roles_list
    FROM users u
    LEFT JOIN user_roles ur ON ur.user_id = u.user_id
    LEFT JOIN roles r ON r.id = ur.role_id
    GROUP BY u.user_id, u.username, u.email
    ORDER BY u.user_id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

    $rolesList = $pdo->query('SELECT id, role_name FROM roles ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);

$dashboardUrl = rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengguna - Admin</title>
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
                    <h1 class="text-2xl font-bold text-gray-800">Detail Pengguna</h1>
                    <p class="text-sm text-gray-500 mt-1">Informasi lengkap seluruh pengguna yang terdaftar pada sistem.</p>

                </div>
                <a href="<?= htmlspecialchars($dashboardUrl . '#users-section', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
            </header>

            <?php if (!empty($_SESSION['success_message'])): ?><div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-r"><?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['success_message']); endif; ?>
            <?php if (!empty($_SESSION['error_message'])): ?><div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-r"><?= htmlspecialchars(strip_tags($_SESSION['error_message']), ENT_QUOTES, 'UTF-8') ?></div><?php unset($_SESSION['error_message']); endif; ?>

            <section id="tambah-user" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8" aria-labelledby="add-user-title">
                <div class="flex items-center gap-3 mb-5"><div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-user-plus"></i></div><div>
                        <h2 id="add-user-title" class="text-lg font-bold text-gray-800">Tambah Pengguna</h2>
                        <p class="text-sm text-gray-500">Buat akun pengguna baru dan tentukan role-nya.</p>
                    </div>
                </div>
                <form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-add-user.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="new-user-name" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input id="new-user-name" type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="new-user-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input id="new-user-email" type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="new-user-password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input id="new-user-password" type="password" name="password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></div>
                    <div>
                        <label for="new-user-role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select id="new-user-role" name="role_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <?php foreach ($rolesList as $role): ?>
                                <option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            <i class="fas fa-save"></i>Simpan Pengguna
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8" aria-label="Ringkasan pengguna">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Pengguna</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?= number_format(count($usersList)) ?></p>
                    </div>
                    <div class="p-4 bg-blue-50 text-blue-600 rounded-xl"><i class="fas fa-users text-2xl"></i></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Data Ditampilkan</p>
                        <p class="text-3xl font-bold text-emerald-600 mt-2"><?= number_format(count($usersList)) ?></p>
                    </div>
                    <div class="p-4 bg-emerald-50 text-emerald-600 rounded-xl"><i class="fas fa-table-list text-2xl"></i></div>
                </div>
            </section>

            <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6" aria-labelledby="users-title">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                    <div>
                        <h2 id="users-title" class="text-lg font-bold text-gray-800">Daftar Semua Pengguna</h2>
                        <p class="text-sm text-gray-500 mt-1">ID, username, email, dan role setiap pengguna.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 self-start px-3 py-2 bg-slate-50 text-slate-600 text-sm rounded-lg">
                        <i class="fas fa-database"></i><?= number_format(count($usersList)) ?> data
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <caption class="sr-only">Daftar lengkap pengguna rumah sakit</caption>
                        <thead>
                            <tr class="text-xs font-semibold text-gray-500 uppercase border-b border-gray-200 bg-gray-50">
                                <th scope="col" class="py-3 px-3">ID</th>
                                <th scope="col" class="py-3 px-3">Username</th>
                                <th scope="col" class="py-3 px-3">Email</th>
                                <th scope="col" class="py-3 px-3">Role</th>
                                <th scope="col" class="py-3 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-600">
                            <?php if ($usersList): ?>
                                <?php foreach ($usersList as $user): ?>
                                    <tr class="hover:bg-gray-50 transition align-top">
                                        <td class="py-3 px-3 font-mono text-xs text-gray-400">#<?= (int) $user['user_id'] ?></td>
                                        <td class="py-3 px-3 font-medium text-gray-800"><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="py-3 px-3"><span class="px-2 py-1 bg-sky-50 text-sky-700 text-xs rounded font-medium"><?= htmlspecialchars($user['roles_list'] ?: 'user', ENT_QUOTES, 'UTF-8') ?></span></td>
                                            <td class="py-3 px-3"><div class="flex justify-end gap-2"><details class="relative"><summary class="list-none cursor-pointer inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded text-xs font-medium hover:bg-blue-200"><i class="fas fa-pen"></i>Edit</summary><div class="absolute right-0 z-10 mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-xl p-4 text-left"><form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-edit-user.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" class="space-y-3"><input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>"><label class="block text-xs font-medium text-gray-700">Username<input type="text" name="username" value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" required class="w-full mt-1 px-2 py-1.5 border border-gray-300 rounded"></label><label class="block text-xs font-medium text-gray-700">Email<input type="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required class="w-full mt-1 px-2 py-1.5 border border-gray-300 rounded"></label><label class="block text-xs font-medium text-gray-700">Role<select name="role_id" required class="w-full mt-1 px-2 py-1.5 border border-gray-300 rounded"><?php foreach ($rolesList as $role): ?><option value="<?= (int) $role['id'] ?>"><?= htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label><button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded text-xs font-medium">Simpan Perubahan</button></form></div></details><form action="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/process-delete-user.php', ENT_QUOTES, 'UTF-8') ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"><input type="hidden" name="id" value="<?= (int) $user['user_id'] ?>"><button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-100 text-red-700 rounded text-xs font-medium hover:bg-red-200"><i class="fas fa-trash"></i>Hapus</button></form></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="py-8 text-center text-gray-400 italic">Belum ada data pengguna.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
