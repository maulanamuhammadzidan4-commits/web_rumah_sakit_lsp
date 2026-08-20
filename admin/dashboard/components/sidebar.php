<?php
require_once __DIR__ . '/../../../config.php';

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$navigationLinkClass = 'group flex w-full items-center space-x-3 rounded-lg px-4 py-3 font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-800';
$activeLinkClass = 'bg-emerald-600 text-white shadow-sm';
$inactiveLinkClass = 'text-slate-300 hover:bg-slate-700 hover:text-white';
?>

<style>
    @media (prefers-reduced-motion: no-preference) {
        @keyframes admin-page-enter {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        aside,
        aside + main {
            animation: admin-page-enter 600ms ease-in-out both;
        }

        aside + main {
            animation-delay: 100ms;
        }
    }
</style>

<!-- SIDEBAR -->
        <aside class="w-full md:w-64 bg-slate-800 text-white flex-shrink-0">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-xl font-bold tracking-wider text-emerald-400">ADMIN PANEL</h1>
                <p class="text-xs text-slate-400 mt-1">Sistem Manajemen Rumah Sakit</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/admin-dashboard.php#dashboard-section', ENT_QUOTES, 'UTF-8'); ?>" class="<?= $navigationLinkClass . ' ' . ($currentPage === 'admin-dashboard.php' ? $activeLinkClass : $inactiveLinkClass); ?>">
                    <i class="fas fa-chart-line w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/users.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= $navigationLinkClass . ' ' . ($currentPage === 'users.php' ? $activeLinkClass : $inactiveLinkClass); ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>Kelola User</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/gallery.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= $navigationLinkClass . ' ' . ($currentPage === 'gallery.php' ? $activeLinkClass : $inactiveLinkClass); ?>">
                    <i class="fas fa-images w-5"></i>
                    <span>Kelola Galeri</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/dokter.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= $navigationLinkClass . ' ' . ($currentPage === 'dokter.php' ? $activeLinkClass : $inactiveLinkClass); ?>">
                    <i class="fas fa-user-md w-5"></i>
                    <span>Kelola Dokter</span>
                </a>
                <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/admin/dashboard/pages/appointment.php', ENT_QUOTES, 'UTF-8'); ?>" class="<?= $navigationLinkClass . ' ' . ($currentPage === 'appointment.php' ? $activeLinkClass : $inactiveLinkClass); ?>">
                    <i class="fas fa-calendar-check w-5"></i>
                    <span>Janji Temu</span>
                </a>
                <div class="pt-6 border-t border-slate-700">
                    <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/backend/forms/logout.php', ENT_QUOTES, 'UTF-8'); ?>"
                        class="<?= $navigationLinkClass; ?> text-red-400 hover:bg-slate-700 hover:text-red-300">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Keluar</span>
                    </a>
                    <a href="<?= htmlspecialchars(rtrim(BASE_URL, '/') . '/frontend/home.php', ENT_QUOTES, 'UTF-8'); ?>"
                        class="<?= $navigationLinkClass; ?> text-blue-400 hover:bg-slate-700 hover:text-blue-300">
                        <i class="fas fa-home w-5"></i>
                        <span>Halaman Depan</span>
                    </a>
                </div>
            </nav>
        </aside>