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

    document.getElementById('login-container').innerHTML = login;

    function reg() {
      document.getElementById('login-container').innerHTML = daftar;
      document.getElementById('daftar').style.display = 'none';
      document.getElementById('masuk').style.display = 'block';
    }

    function masuk() {
      document.getElementById('login-container').innerHTML = login;
      document.getElementById('daftar').style.display = 'block';
      document.getElementById('masuk').style.display = 'none';
    }
  </script>

</body>
</html>