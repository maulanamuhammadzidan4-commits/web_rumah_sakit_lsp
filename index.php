<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login page</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="frontend/assets/img/favicon.png" rel="icon">
  <link href="frontend/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="frontend/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="frontend/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="frontend/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="frontend/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="frontend/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="frontend/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="frontend/assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Medilab
  * Template URL: https://bootstrapmade.com/medilab-free-medical-bootstrap-theme/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
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

    <section>
      <form action="backend/forms/login.php" method="post">
        
      </form>
    </section>

  </main>

<?php include 'frontend/components/footer.php'; ?>

  <!-- Scroll Top -->
<?php include 'frontend/components/scroll-top.php'; ?>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="frontend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="frontend/assets/vendor/php-email-form/validate.js"></script>
  <script src="frontend/assets/vendor/aos/aos.js"></script>
  <script src="frontend/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="frontend/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="frontend/assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="frontend/assets/js/main.js"></script>

</body>
</html>