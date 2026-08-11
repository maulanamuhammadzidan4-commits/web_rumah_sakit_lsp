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
      <div id="login-container"></div>
      <div id="daftar-akun">
        <p>Belum punya akun? <a href="#login" onclick="reg()">Buat akun</a></p>
      </div>
      <div id="masuk" style="display: none;">
        <p>Sudah punya akun? <a href="#login" onclick="masuk()">Masuk</a></p>
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
    const login = `<?php include 'frontend/components/log-form.php' ?>`;
    const daftar = `<?php include 'frontend/components/reg-form.php' ?>`;

    document.getElementById('login-container').innerHTML = login;

    function reg(){
      document.getElementById('login-container').innerHTML = daftar;
      document.getElementById('daftar-akun').style.display = 'none';
      document.getElementById('masuk').style.display = 'block';
    }

    function masuk() {
      document.getElementById('login-container').innerHTML = login;
      document.getElementById('daftar-akun').style.display = 'block';
      document.getElementById('masuk').style.display = 'none';
    }
  </script>

</body>
</html>