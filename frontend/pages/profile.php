<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../backend/config/database.php';

function columnExists(PDO $pdo, string $table, string $column): bool
{
  // Sanitasi nama tabel dan kolom dari karakter selain huruf, angka, dan underscore
  $cleanTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
  $cleanColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);

  // Gunakan INFORMATION_SCHEMA agar kompatibel 100% dengan PDO Prepared Statements
  $stmt = $pdo->prepare('
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = :table 
      AND COLUMN_NAME = :column
  ');
    
  $stmt->execute([
    ':table' => $cleanTable,
    ':column' => $cleanColumn
  ]);

  return (int)$stmt->fetchColumn() > 0;
}

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
  header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
  exit;
}

$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

$userId = (int)($_SESSION['user']['id'] ?? 0);
$telpColumnExists = columnExists($pdo, 'users', 'telp');

$query = 'SELECT user_id, username, email, full_name, address, created_at FROM users WHERE user_id = :user_id LIMIT 1';
if ($telpColumnExists) {
  $query = 'SELECT user_id, username, email, full_name, address, telp, created_at FROM users WHERE user_id = :user_id LIMIT 1';
}

$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$medicalStmt = $pdo->prepare('SELECT blood_type, medical_history, updated_at FROM user_medical_profiles WHERE user_id = :user_id LIMIT 1');
$medicalStmt->execute([':user_id' => $userId]);
$medical = $medicalStmt->fetch(PDO::FETCH_ASSOC);

$appointmentStmt = $pdo->prepare('SELECT a.klinik, a.tanggal_temu, d.nama_dokter
  FROM appointments a
  LEFT JOIN dokter d ON d.id = a.id_dokter
  WHERE a.id_user = :user_id
  ORDER BY a.tanggal_temu DESC, a.id_appointment DESC');
$appointmentStmt->execute([':user_id' => $userId]);
$appointments = $appointmentStmt->fetchAll(PDO::FETCH_ASSOC);

if (!$user) {
  echo 'Data user tidak ditemukan.';
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <?php include __DIR__ . '/../components/css.php'; ?>
  <title>Profil Saya</title>
</head>
<body>
<?php include __DIR__ . '/../components/header.php'; ?>

  <main class="main">
    <section class="section" style="padding-top: 80px;">
      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="custom-form-card p-4">
              <div class="section-title text-center">
                <h2>Profil Saya</h2>
                <p>Detail data diri dan riwayat medis Anda.</p>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Username</label>
                  <div class="form-control bg-light"><?= htmlspecialchars($user['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Email</label>
                    <div class="form-control bg-light"><?= htmlspecialchars($user['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Nama Lengkap</label>
                  <div class="form-control bg-light"><?= htmlspecialchars($user['full_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <?php if ($telpColumnExists): ?>
                  <div class="col-md-6">
                    <label class="form-label">No. Telepon</label>
                      <div class="form-control bg-light"><?= htmlspecialchars($user['telp'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                  </div>
                <?php endif; ?>

                <div class="col-12">
                  <label class="form-label">Alamat</label>
                  <div class="form-control bg-light"><?= htmlspecialchars($user['address'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Golongan Darah</label>
                  <div class="form-control bg-light"><?= htmlspecialchars($medical['blood_type'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Terakhir Diperbarui</label>
                  <div class="form-control bg-light">
                    <?= htmlspecialchars(!empty($medical['updated_at']) ? date('d M Y H:i', strtotime($medical['updated_at'])) : '-', ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                </div>

                <div class="col-12">
                  <label class="form-label">Riwayat Penyakit / Alergi</label>
                  <div class="form-control bg-light" style="min-height: 120px; white-space: pre-wrap;"><?= htmlspecialchars($medical['medical_history'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                </div>

                <div class="col-12 text-center mt-3">
                  <a href="<?= BASE_URL; ?>frontend/pages/data-diri.php" class="btn-accent-form">Edit Data Diri</a>
                </div>
              </div>
            </div>

            <div class="custom-form-card p-4 mt-4">
              <div class="section-title text-center">
                <h2>Janji Temu Saya</h2>
                <p>Daftar jadwal konsultasi yang telah Anda buat.</p>
              </div>

              <?php if ($appointments): ?>
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Dokter</th>
                        <th>Klinik</th>
                        <th>Tanggal & Waktu</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($appointments as $appointment): ?>
                        <tr>
                          <td><?= htmlspecialchars($appointment['nama_dokter'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?= htmlspecialchars($appointment['klinik'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?= htmlspecialchars(!empty($appointment['tanggal_temu']) ? date('d M Y H:i', strtotime($appointment['tanggal_temu'])) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <p class="text-center text-muted mb-0">Belum ada janji temu.</p>
              <?php endif; ?>
            </div>

            <div class="text-center mt-3">
              <a class="cta-btn profile-logout-btn" href="<?= BASE_URL; ?>backend/forms/logout.php">Logout</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<?php if ($successMessage): ?>
  <div class="modal fade" id="appointmentSuccessModal" tabindex="-1" aria-labelledby="appointmentSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="appointmentSuccessModalLabel">Janji Temu Berhasil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <i class="bi bi-check-circle-fill text-success fs-1" aria-hidden="true"></i>
            <p class="mt-3 mb-0"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  <noscript>
    <div class="container mt-3">
      <div class="alert alert-success" role="alert"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
  </noscript>
<?php endif; ?>

<div id="preloader"></div>
<?php include __DIR__ . '/../components/js.php'; ?>
<?php if ($successMessage): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const successModal = document.getElementById('appointmentSuccessModal');
      if (successModal && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(successModal).show();
      }
    });
  </script>
<?php endif; ?>
</body>
</html>