<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../components/css.php' ?>
</head>
<body>

  <?php include '../components/header.php' ?>

  <main class="container">
    <div class="login-card">
      <div id="login-container"></div>
      <div class="alter">
        <p id="daftar">Belum punya akun? <a href="javascript:void(0)" onclick="reg()">Register</a></p>
        <p id="masuk" style="display: none;">Sudah punya akun? <a href="javascript:void(0)" onclick="masuk()">Login</a></p>
      </div>
    </div>
  </main>

  <?php include '../components/footer.php' ?>
  <div id="preloader"></div>
  <?php include '../components/js.php' ?>

  
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

  <script src="../assets/js/validation-reg.js"></script>
</body>
</html>