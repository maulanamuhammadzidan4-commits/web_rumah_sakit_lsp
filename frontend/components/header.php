<?php
require_once __DIR__ . '/../../config.php';
?>
<header id="header" class="header sticky-top">

  <div class="topbar d-flex align-items-center">
    <div class="container d-flex justify-content-center justify-content-md-between">
      <div class="contact-info d-flex align-items-center">
        <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:lokanandars1@gmail.com">lokanandars1@gmail.com</a></i>
        <i class="bi bi-phone d-flex align-items-center ms-4"><span>+62 856-1234-5678</span></i>
      </div>
      <div class="social-links d-none d-md-flex align-items-center">
        <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
      </div>
    </div>
  </div><!-- End Top Bar -->

  <div class="branding d-flex align-items-center">

    <div class="container position-relative d-flex align-items-center justify-content-between">
      <!-- Logo -->
      <a href="<?= BASE_URL; ?>frontend/home.php" class="logo d-flex align-items-center me-auto">
        <img src="<?= BASE_URL; ?>frontend/assets/img/logo.png" alt="logo" class="logo-img me-2">
        <div class="brand-text d-flex flex-column align-items-start">
          <span class="sitename title-main">RSUD Majalengka</span>
          <span class="sitename title-sub">Kabupaten Majalengka</span>
        </div>
      </a>

      <!-- Navigation Menu -->
      <nav id="navmenu" class="navmenu order-last order-xl-0">
        <ul>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#hero" class="active">Home<br></a></li>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#about">About</a></li>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#services">Layanan</a></li>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#departments">Departemen</a></li>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#doctors">Dokter</a></li>
          <li class="dropdown"><a href="#"><span>Layanan lain</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="#">Artikel kesehatan</a></li>
              <li class="dropdown"><a href="#"><span>Departemen</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                <ul>
                  <li><a href="#">Kardiologi</a></li>
                  <li><a href="#">Neurologi</a></li>
                  <li><a href="#">Hepatologi</a></li>
                  <li><a href="#">Pediatri</a></li>
                  <li><a href="#">Klinik mata</a></li>
                </ul>
              </li>
              <li><a href="#">Chat Dokter</a></li>
              <li><a href="#">Jadwal praktek</a></li>
              <li><a href="#">Obat</a></li>
              <li><a href="<?= BASE_URL; ?>pages/room.html">Kamar</a></li>
            </ul>
          </li>
          <li><a href="<?= BASE_URL; ?>frontend/home.php#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <!-- Header Action Buttons (IGD & Janji Temu) -->
      <div class="header-action-btns d-flex align-items-center gap-2 ms-3 order-2 order-xl-last">
        <!-- Tombol IGD -->
        <a class="btn-igd" href="<?= BASE_URL; ?>pages/igd.html">
          <i class="bi bi-telephone-fill me-1"></i>
          <span>IGD</span>
        </a>

        <a class="cta-btn" id="register" href="<?= BASE_URL ?>frontend/pages/login-page.php">Login</a>

        <!-- Tombol Buat Janji Temu -->
        <a class="cta-btn" href="<?= BASE_URL; ?>frontend/home.php#appointment">Janji Temu</a>
      </div>

    </div>

  </div>

</header>