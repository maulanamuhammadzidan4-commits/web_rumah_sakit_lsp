<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../components/css.php' ?>
</head>
<body>

  <?php include '../components/header.php' ?>

  <main class="main">

    <!-- Section Form Data Diri -->
    <section id="data-diri" class="data-diri section data-diri-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Pengisian Data Diri</h2>
          <p>Silakan lengkapi formulir data diri pasien di bawah ini dengan benar.</p>
        </div>

        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="custom-form-card">
              
              <form action="../../backend/forms/process-data-diri.php" method="post" class="php-email-form-style">
                
                <div class="row g-3">
                  <!-- Username -->
                  <div class="col-md-6">
                    <label for="user_name" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" name="usname" id="user_name" class="form-control" placeholder="Masukkan username" <?= isset($_SESSION['username']) ? 'value="' . $_SESSION['username'] . '"' : '' ?> required>
                  </div>

                  <!-- Email -->
                  <div class="col-md-6">
                    <label for="user_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="usemail" id="user_email" class="form-control" placeholder="contoh@email.com" <?= isset($_SESSION['email']) ? 'value="' . $_SESSION['email'] . '"' : '' ?> required>
                  </div>

                  <?php if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] === false): ?>
                  <div class="col-md-6">
                    <label for="user_password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="uspass" id="user_password" class="form-control" placeholder="Masukkan password" required>
                  </div>
                  <?php endif; ?>

                  <!-- Nama Lengkap -->
                  <div class="col-md-6">
                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="fullname" id="full_name" class="form-control" placeholder="Nama lengkap sesuai KTP" required>
                  </div>

                  <!-- No. Telepon -->
                  <div class="col-md-6">
                    <label for="telp" class="form-label">No. Telepon / Whatsapp <span class="text-danger">*</span></label>
                    <input type="tel" name="telp" id="telp" class="form-control" placeholder="08xxxxxxxxxx" required>
                  </div>

                  <!-- Alamat -->
                  <div class="col-12">
                    <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="address" class="form-control" placeholder="Jl. Raya No. XX, Kota/Kabupaten" required>
                  </div>

                  <!-- Golongan Darah -->
                  <div class="col-md-12">
                    <label for="blood_type" class="form-label">Golongan Darah</label>
                    <select name="bloodtype" id="blood_type" class="form-select">
                      <option value="" selected disabled>-- Pilih Golongan Darah --</option>
                      <option value="A Positive (A+)">A Positive (A+)</option>
                      <option value="A Negative (A-)">A Negative (A-)</option>
                      <option value="B Positive (B+)">B Positive (B+)</option>
                      <option value="B Negative (B-)">B Negative (B-)</option>
                      <option value="AB Positive (AB+)">AB Positive (AB+)</option>
                      <option value="AB Negative (AB-)">AB Negative (AB-)</option>
                      <option value="O Positive (O+)">O Positive (O+)</option>
                      <option value="O Negative (O-)">O Negative (O-)</option>
                    </select>
                  </div>

                  <!-- Riwayat Medis -->
                  <div class="col-12">
                    <label for="medical_history" class="form-label">Riwayat Penyakit / Alergi</label>
                    <textarea name="medicalhistory" id="medical_history" class="form-control" rows="4" placeholder="Tuliskan riwayat penyakit, operasi, atau alergi obat jika ada..."></textarea>
                  </div>

                  <!-- Tombol Submit -->
                    <a href="<?= BASE_URL ?>frontend/home.php" class="text-center">lewati</a>
                  <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn-accent-form">Kirim Data!</button>
                  </div>
                </div>

              </form>

            </div>
          </div>
        </div>

      </div>
    </section>

  </main>

  <?php include '../components/footer.php' ?>
  <div id="preloader"></div>
  <?php include '../components/js.php' ?>

</body>
</html>