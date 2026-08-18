<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);
$stmt = $pdo->prepare('SELECT username, email, full_name, telp, address FROM users WHERE user_id = :user_id LIMIT 1');
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$medicalStmt = $pdo->prepare('SELECT blood_type, medical_history FROM user_medical_profiles WHERE user_id = :user_id LIMIT 1');
$medicalStmt->execute([':user_id' => $userId]);
$medical = $medicalStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  echo 'Data user tidak ditemukan.';
  exit;
}

$formError = $_SESSION['form_error'] ?? '';
unset($_SESSION['form_error']);
$profileSuccess = $_SESSION['profile_success'] ?? '';
unset($_SESSION['profile_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php include '../components/css.php'; ?>
  <title>Pengisian Data Diri</title>
</head>
<body>

  <?php include '../components/header.php'; ?>

  <main class="main">
    <section id="data-diri" class="data-diri section data-diri-bg">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Pengisian Data Diri</h2>
          <p>Silakan lengkapi formulir data diri pasien di bawah ini dengan benar.</p>
        </div>

        <?php if ($formError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($profileSuccess !== ''): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($profileSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="custom-form-card">
              <form action="../../backend/forms/process-data-diri.php" method="post" class="php-email-form-style" id="usData">
                <div class="row g-3">
                  <div id="error-box" class="alert alert-danger" role="alert" style="display: none;"></div>
                  <div class="col-md-6">
                    <label for="user_name" class="form-label">Username</label>
                    <input type="text" id="user_name" class="form-control bg-light" value="<?= htmlspecialchars($_SESSION['user']['username'] ?? ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" readonly>
                  </div>

                  <div class="col-md-6">
                    <label for="user_email" class="form-label">Email</label>
                    <input type="email" id="user_email" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly>
                  </div>

                  <div class="col-md-6">
                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="fullname" id="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nama lengkap sesuai KTP/KK" required>
                  </div>

                  <div class="col-md-6">
                    <label for="telp" class="form-label">No. Telepon / Whatsapp <span class="text-danger">*</span></label>
                    <input type="tel" name="telp" id="telp" class="form-control" value="<?= htmlspecialchars($user['telp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="08xxxxxxxxxx" required>
                  </div>

                  <div class="col-12">
                    <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="address" id="address" class="form-control" value="<?= htmlspecialchars($user['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Jl. Raya No. XX, Kota/Kabupaten" required>
                  </div>

                  <div class="col-md-12">
                    <label for="blood_type" class="form-label">Golongan Darah</label>
                    <select name="bloodtype" id="blood_type" class="form-select">
                      <option value="" <?= empty($medical['blood_type'] ?? '') ? 'selected' : ''; ?>>-- Pilih Golongan Darah --</option>
                      <option value="A Positive (A+)" <?= (($medical['blood_type'] ?? '') === 'A Positive (A+)') ? 'selected' : ''; ?>>A Positive (A+)</option>
                      <option value="A Negative (A-)" <?= (($medical['blood_type'] ?? '') === 'A Negative (A-)') ? 'selected' : ''; ?>>A Negative (A-)</option>
                      <option value="B Positive (B+)" <?= (($medical['blood_type'] ?? '') === 'B Positive (B+)') ? 'selected' : ''; ?>>B Positive (B+)</option>
                      <option value="B Negative (B-)" <?= (($medical['blood_type'] ?? '') === 'B Negative (B-)') ? 'selected' : ''; ?>>B Negative (B-)</option>
                      <option value="AB Positive (AB+)" <?= (($medical['blood_type'] ?? '') === 'AB Positive (AB+)') ? 'selected' : ''; ?>>AB Positive (AB+)</option>
                      <option value="AB Negative (AB-)" <?= (($medical['blood_type'] ?? '') === 'AB Negative (AB-)') ? 'selected' : ''; ?>>AB Negative (AB-)</option>
                      <option value="O Positive (O+)" <?= (($medical['blood_type'] ?? '') === 'O Positive (O+)') ? 'selected' : ''; ?>>O Positive (O+)</option>
                      <option value="O Negative (O-)" <?= (($medical['blood_type'] ?? '') === 'O Negative (O-)') ? 'selected' : ''; ?>>O Negative (O-)</option>
                    </select>
                  </div>

                  <div class="col-12">
                    <label for="medical_history" class="form-label">Riwayat Penyakit / Alergi</label>
                    <textarea name="medicalhistory" id="medical_history" class="form-control" rows="4" placeholder="Tuliskan riwayat penyakit, operasi, atau alergi obat jika ada..."><?= htmlspecialchars($medical['medical_history'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                  </div>

                  <div class="col-12 text-center mt-4">
                    <a href="<?= BASE_URL; ?>frontend/home.php" class="text-center me-3">Lewati</a>
                    <button type="submit" class="btn-accent-form">Simpan Data!</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div>
    </section>
  </main>

  <?php include '../components/footer.php'; ?>
  <div id="preloader"></div>
  <?php include '../components/js.php'; ?>

</body>
</html>