<?php
require_once __DIR__ . '/../../config.php';
?>

<footer id="footer" class="footer custom-dark-footer">

  <div class="container footer-top">
    <div class="row gy-4 justify-content-between">
      
      <!-- Kolom 1: Logo, Alamat, Kontak -->
      <div class="col-lg-4 col-md-6 footer-about">
        <a href="<?= BASE_URL ?>frontend/home.php" class="logo d-flex align-items-center mb-3">
          <!-- Kamu bisa ganti SVGs/Img dengan logo RSUD asli jika ada -->
          <img src="<?= BASE_URL; ?>frontend/assets/img/logo.png" alt="logo" class="logo-img me-2">
          <div class="brand-text">
            <h1 class="sitename-main m-0">RSUD Majalengka</h1>
            <p class="sitename-sub m-0">Kabupaten Majalengka</p>
          </div>
        </a>
        
        <p class="footer-address">
          Jl. Kesehatan No. 7 Majalengka Jawa Barat
        </p>
        
        <div class="footer-contact-info pt-2">
          <p class="d-flex align-items-center mb-2">
            <i class="bi bi-telephone me-2 text-warning"></i>
            <span>+6233281043</span>
          </p>
          <p class="d-flex align-items-center mb-0">
            <i class="bi bi-envelope me-2 text-warning"></i>
            <span>info@rsudmajalengka.co.id</span>
          </p>
        </div>
      </div>

      <!-- Kolom 2: Layanan -->
      <div class="col-lg-2 col-md-3 footer-links">
        <h4 class="title-with-line">Layanan</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>frontend/pages/darurat.html">IGD</a></li>
          <li><a href="<?= BASE_URL ?>frontend/home.php#appointment">Janji Temu</a></li>
        </ul>
      </div>

      <!-- Kolom 3: Menu -->
      <div class="col-lg-2 col-md-3 footer-links">
        <h4 class="title-with-line">Menu</h4>
        <ul>
          <li><a href="<?= BASE_URL ?>frontend/home.php#doctors">Dokter</a></li>
          <li><a href="<?= BASE_URL ?>frontend/home.php#gallery ">Galeri</a></li>
        </ul>
      </div>

      <!-- Kolom 4: Sosial Media -->
      <div class="col-lg-3 col-md-3 footer-social-col">
        <h4 class="title-with-line">Sosial Media</h4>
        <div class="social-media-icons d-flex gap-3 mt-3">
          <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
          <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>

    </div>
  </div>

  <!-- Copyright Section -->
  <div class="container copyright text-center mt-4">
    <p>&copy; 2026 RSUD Majalengka</p>
  </div>

  <!-- Scroll to top button (Jika ingin ditaruh di dalam footer atau tetap pakai milik bawaan) -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center active">
    <i class="bi bi-arrow-up-short"></i>
  </a>

</footer>