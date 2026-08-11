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
              <h1>Starter Page</h1>
              <p class="mb-0">Odio et unde deleniti. Deserunt numquam exercitationem. Officiis quo odio sint voluptas consequatur ut a odio voluptatem. Sit dolorum debitis veritatis natus dolores. Quasi ratione sint. Sit quaerat ipsum dolorem.</p>
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
    <section id="starter-section" class="starter-section section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Starter Section</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up">
        <p>Use this page as a starter for your own custom pages.</p>
      </div>

    </section><!-- /Starter Section Section -->

    <section id="login">
    <div class="login-card">
      <div id="login-container"></div>
      <div class="alter">
        <p id="daftar">Belum punya akun? <a href="javascript:void(0)" onclick="reg()">Register</a></p>
        <p id="masuk" style="display: none;">Sudah punya akun? <a href="javascript:void(0)" onclick="masuk()">Login</a></p>
      </div>
    </div>
    </section>

  </main>

<?php include 'frontend/components/footer.php'; ?>

  <!-- Scroll Top -->
<?php include 'frontend/components/scroll-top.php'; ?>

  <!-- Preloader -->
  <div id="preloader"></div>

  <?php include 'frontend/components/js.php' ?>

  <script>
    const login = `<?php include '../components/log-form.php' ?>`;
    const daftar = `<?php include '../components/reg-form.php' ?>`;

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