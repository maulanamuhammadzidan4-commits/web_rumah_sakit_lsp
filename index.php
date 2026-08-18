<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include 'frontend/components/css.php' ?>
</head>

<body class="starter-page-page">

<?php include 'frontend/components/header.php'; ?>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>Selamat datang!</h1>
              <p class="mb-0">RSUD Majalengka adalah rumah sakit daerah yang berkomitmen memberikan pelayanan kesehatan terbaik bagi masyarakat. Dengan fasilitas modern, tenaga medis profesional, dan berbagai layanan spesialis, RSUD Majalengka hadir untuk memastikan perawatan yang berkualitas, aman, dan terjangkau.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="frontend/home.php">Home</a></li>
            <li class="current">Starter Page</li>
          </ol>
        </div>
      </nav>
    </div><!-- End Page Title -->

    <!-- Starter Section Section -->
    <?php if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] === false): ?>
    <section id="starter-section" class="starter-section section">
      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Daftar</h2>
        <p>Daftar untuk mempercepat proses pendaftaran online di kemudian hari</p>
        <div class="login-card">
          <div id="login-container"></div>
          <div class="alter">
            <p id="daftar">Belum punya akun? <a href="javascript:void(0)" onclick="reg()">Register</a></p>
            <p id="masuk" style="display: none;">Sudah punya akun? <a href="javascript:void(0)" onclick="masuk()">Login</a></p>
          </div>
        </div>
    </section>
    <?php endif; ?>

  </main>

<?php include 'frontend/components/footer.php'; ?>

  <!-- Scroll Top -->
<?php include 'frontend/components/scroll-top.php'; ?>

  <!-- Preloader -->
  <div id="preloader"></div>

  <?php include 'frontend/components/js.php' ?>

  <script>
    const login = `<?php include 'frontend/components/log-form.php' ?>`;
    const daftar = `<?php include 'frontend/components/reg-form.php' ?>`;

    const loginContainer = document.getElementById('login-container');
    const elemDaftar = document.getElementById('daftar');
    const elemMasuk = document.getElementById('masuk');

    // Render awal
    loginContainer.innerHTML = login;

    function reg() {
      loginContainer.innerHTML = daftar;
      elemDaftar.style.display = 'none';
      elemMasuk.style.display = 'block';
      elemMasuk.style.animation = 'fadeInUp 0.3s ease forwards';
    }

    function masuk() {
      loginContainer.innerHTML = login;
      elemMasuk.style.display = 'none';
      elemDaftar.style.display = 'block';
      elemDaftar.style.animation = 'fadeInUp 0.3s ease forwards';
    }
  </script>

</body>
</html>