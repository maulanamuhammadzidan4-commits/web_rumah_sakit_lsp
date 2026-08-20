<?php
require_once __DIR__ . '/../../../config.php';
?>

<!-- SIDEBAR -->
        <aside class="w-full md:w-64 bg-slate-800 text-white flex-shrink-0">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-xl font-bold tracking-wider text-emerald-400">ADMIN PANEL</h1>
                <p class="text-xs text-slate-400 mt-1">Sistem Manajemen Rumah Sakit</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php#dashboard-section', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center space-x-3 px-4 py-3 bg-emerald-600 rounded-lg text-white font-medium">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/users.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-users w-5"></i>
                    <span>Kelola User</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/gallery.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-images w-5"></i>
                    <span>Kelola Galeri</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/dokter.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
                    <i class="fas fa-user-md w-5"></i>
                    <span>Kelola Dokter</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/appointment.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center space-x-3 px-4 py-3 text-slate-300 hover:bg-slate-700 rounded-lg transition">
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