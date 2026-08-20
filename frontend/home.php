<?php
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/config/components/doctors.php';
require_once __DIR__ . '/../backend/config/components/gallery.php';

$doctors = get_doctors($pdo);
$galleryImages = get_gallery($pdo);

#---------------------------------------------------
# CHECK LOGIN STATUS & USER DATA
#---------------------------------------------------
$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
$userData = null;
$isDataDiriComplete = false;

if ($isLoggedIn) {
    $userId = (int)($_SESSION['user']['id'] ?? 0);
    if ($userId > 0) {
        try {
            $stmt = $pdo->prepare('SELECT username, email, full_name, telp, address FROM users WHERE user_id = :user_id LIMIT 1');
            $stmt->execute([':user_id' => $userId]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if data diri is complete (full_name, telp, address must be filled)
            if ($userData && !empty($userData['full_name']) && !empty($userData['telp']) && !empty($userData['address'])) {
                $isDataDiriComplete = true;
            }
        } catch (PDOException $e) {
            error_log("Error fetching user data: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php include 'components/css.php' ?>
</head>

<body class="index-page">

  <?php include 'components/header.php'; ?>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section light-background">

      <img src="assets/img/hero-bg.png" alt="" data-aos="fade-in">

      <div class="container position-relative">

        

        <div class="content row gy-4">
          <div class="col-lg-4 d-flex align-items-stretch">
            <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
              <h3>RSUD Majalengka</h3>
              <p>
                RSUD Majalengka adalah rumah sakit daerah yang berkomitmen memberikan pelayanan kesehatan terbaik bagi masyarakat.
                Dengan fasilitas modern, tenaga medis profesional, dan berbagai layanan spesialis, RSUD Majalengka hadir untuk memastikan perawatan yang berkualitas, aman, dan terjangkau.
                Kami terus berinovasi untuk meningkatkan mutu layanan demi kesehatan dan kesejahteraan pasien.
              </p>
              <div class="text-center">
                <a href="#about" class="more-btn"><span>Learn more</span> <i class="bi bi-chevron-right"></i></a>
              </div>
            </div>
          </div><!-- End Why Box -->

          <div class="col-lg-8 d-flex align-items-stretch">
            <div class="d-flex flex-column justify-content-center">
              <div class="row gy-4">

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                    <i class="bi bi-clipboard-data"></i>
                    <h4>Kemudahan akses</h4>
                    <p>Kami menyediakan form pendaftaran online. Dengan sistem yang mudah digunakan, pasien dapat mendaftar secara cepat dan efisien.</p>
                  </div>
                </div><!-- End Icon Box -->

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                    <i class="bi bi-gem"></i>
                    <h4>Kualitas Pelayanan</h4>
                    <p>Kami menjamin kualitas pelayanan terbaik dengan tenaga medis yang berpengalaman dan fasilitas yang modern. </p>
                  </div>
                </div><!-- End Icon Box -->

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                    <i class="bi bi-inboxes"></i>
                    <h4>Menerima Kritik & saran</h4>
                    <p>Kami terbuka terhadap kritik dan saran dari pasien untuk terus meningkatkan pelayanan kami.</p>
                  </div>
                </div><!-- End Icon Box -->

              </div>
            </div>
          </div>
        </div><!-- End  Content-->

      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4 gx-5">

          <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="200">
            <img src="assets/img/about.png" class="img-fluid" alt="">
          </div>

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <h3>Tentang kami</h3>
            <p>
              Kami adalah rumah sakit yang berkomitmen untuk memberikan pelayanan kesehatan terbaik bagi masyarakat. Dengan tenaga medis yang profesional dan fasilitas modern, kami siap membantu Anda dalam menjaga kesehatan dan kesejahteraan.
            </p>
            <ul>
              <li>
                <i class="fa-solid fa-vial-circle-check"></i>
                <div>
                  <h5>Lab terverifikasi</h5>
                  <p>Labolatorium kami telah tersertifikasi oleh lembaga terkemuka.</p>
                </div>
              </li>
              <li>
                <i class="fa-solid fa-pump-medical"></i>
                <div>
                  <h5>Steril</h5>
                  <p>Kami memastikan setiap kamar pasien, ruang perawatan, dan fasilitas medis lainnya tetap steril dan higienis.</p>
                </div>
              </li>
              <li>
                <i class="fa-solid fa-heart-circle-xmark"></i>
                <div>
                  <h5>Menerima Kritik & saran</h5>
                  <p>Kami terbuka terhadap kritik dan saran dari pasien untuk terus meningkatkan pelayanan kami.</p>
                </div>
              </li>
            </ul>
          </div>

        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Appointment Section -->
    <section id="appointment" class="appointment section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Janji temu</h2>
        <p>Perlu jadwal konsultasi? Silakan isi formulir di bawah ini.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <?php if (!$isLoggedIn): ?>
          <!-- FORM UNTUK USER YANG BELUM LOGIN -->
          <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            Silakan <a href="pages/login-page.php" class="alert-link">login terlebih dahulu</a> untuk membuat janji temu.
          </div>

          <form id="appointmentFormGuest" method="post" role="form" class="php-email-form">
            <div class="row">
              <div class="col-md-4 form-group">
                <input type="text" name="name" class="form-control" id="name" placeholder="Masukkan nama Anda" required="">
              </div>
              <div class="col-md-4 form-group mt-3 mt-md-0">
                <input type="email" class="form-control" name="email" id="email" placeholder="Masukkan email Anda" required="">
              </div>
              <div class="col-md-4 form-group mt-3 mt-md-0">
                <input type="tel" class="form-control" name="phone" id="phone" placeholder="Masukkan nomor telepon Anda" required="">
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 form-group mt-3">
                <input type="datetime-local" name="date" class="form-control datepicker" id="date" placeholder="Appointment Date" required="">
              </div>
              <div class="col-md-4 form-group mt-3">
                <select name="department" id="department" class="form-select" required="">
                  <option value="">Pilih Klinik</option>
                  <option value="Klinik Jantung">Klinik Jantung</option>
                  <option value="Klinik Jiwa">Klinik Jiwa</option>
                  <option value="Klinik Paru">Klinik Paru</option>
                  <option value="Klinik THT">Klinik THT</option>
                  <option value="Klinik Mata">Klinik Mata</option>
                </select>
              </div>
              <div class="col-md-4 form-group mt-3">
                <select name="doctor" id="doctor" class="form-select" required="">
                  <option value="">Pilih Dokter</option>
                  <?php foreach ($doctors as $doctor): ?>
                  <option value="<?= htmlspecialchars($doctor['nama_dokter']); ?>"><?= htmlspecialchars($doctor['nama_dokter']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group mt-3">
              <textarea class="form-control" name="message" rows="5" placeholder="Pesan (Opsional)"></textarea>
            </div>
            <div class="mt-3">
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Permintaan janji temu Anda telah dikirimkan dengan sukses. Terima kasih!</div>
              <div class="text-center">
                <button type="button" onclick="handleGuestAppointment()" class="btn btn-primary">Buat Janji Temu</button>
              </div>
            </div>
          </form>
        <?php elseif ($isLoggedIn && !$isDataDiriComplete): ?>
          <!-- FORM UNTUK USER YANG SUDAH LOGIN TAPI BELUM LENGKAP DATA DIRI -->
          <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Anda perlu melengkapi <a href="pages/data-diri.php" class="alert-link">data diri Anda</a> terlebih dahulu sebelum membuat janji temu.
          </div>

          <form id="appointmentFormIncomplete" method="post" role="form" class="php-email-form">
            <div class="row">
              <div class="col-md-4 form-group">
                <input type="text" name="name" class="form-control" id="name2" placeholder="Masukkan nama Anda" required="">
              </div>
              <div class="col-md-4 form-group mt-3 mt-md-0">
                <input type="email" class="form-control" name="email" id="email2" placeholder="Masukkan email Anda" required="">
              </div>
              <div class="col-md-4 form-group mt-3 mt-md-0">
                <input type="tel" class="form-control" name="phone" id="phone2" placeholder="Masukkan nomor telepon Anda" required="">
              </div>
            </div>
            <div class="row">
              <div class="col-md-4 form-group mt-3">
                <input type="datetime-local" name="date" class="form-control datepicker" id="date2" placeholder="Appointment Date" required="">
              </div>
              <div class="col-md-4 form-group mt-3">
                <select name="department" id="department2" class="form-select" required="">
                  <option value="">Pilih Klinik</option>
                  <option value="Klinik Jantung">Klinik Jantung</option>
                  <option value="Klinik Jiwa">Klinik Jiwa</option>
                  <option value="Klinik Paru">Klinik Paru</option>
                  <option value="Klinik THT">Klinik THT</option>
                  <option value="Klinik Mata">Klinik Mata</option>
                </select>
              </div>
              <div class="col-md-4 form-group mt-3">
                <select name="doctor" id="doctor2" class="form-select" required="">
                  <option value="">Pilih Dokter</option>
                  <?php foreach ($doctors as $doctor): ?>
                  <option value="<?= htmlspecialchars($doctor['nama_dokter']); ?>"><?= htmlspecialchars($doctor['nama_dokter']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group mt-3">
              <textarea class="form-control" name="message" rows="5" placeholder="Pesan (Opsional)"></textarea>
            </div>
            <div class="mt-3">
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Permintaan janji temu Anda telah dikirimkan dengan sukses. Terima kasih!</div>
              <div class="text-center">
                <button type="button" onclick="handleIncompleteAppointment()" class="btn btn-primary">Buat Janji Temu</button>
              </div>
            </div>
          </form>
        <?php else: ?>
          <!-- FORM UNTUK USER YANG SUDAH LOGIN DAN DATA DIRI LENGKAP -->
          <form action="../backend/forms/process-add-appointment.php" method="post" role="form" class="php-email-form">

            <!-- Display user info as read-only -->
            <div class="row">
              <div class="col-md-4 form-group">
                <label for="display_name" class="form-label">Nama</label>
                <input type="text" class="form-control bg-light" id="display_name" value="<?= htmlspecialchars($userData['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly="">
              </div>
              <div class="col-md-4 form-group">
                <label for="display_email" class="form-label">Email</label>
                <input type="email" class="form-control bg-light" id="display_email" value="<?= htmlspecialchars($userData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly="">
              </div>
              <div class="col-md-4 form-group">
                <label for="display_phone" class="form-label">No. Telepon</label>
                <input type="tel" class="form-control bg-light" id="display_phone" value="<?= htmlspecialchars($userData['telp'] ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly="">
              </div>
            </div>

            <div class="row">
              <div class="col-md-4 form-group mt-3">
                <label for="date_logged_in" class="form-label">Tanggal & Waktu <span class="text-danger">*</span></label>
                <input type="datetime-local" name="tanggal_temu" class="form-control datepicker" id="date_logged_in" placeholder="Appointment Date" required="">
              </div>
              <div class="col-md-4 form-group mt-3">
                <label for="department_logged_in" class="form-label">Klinik <span class="text-danger">*</span></label>
                <select name="klinik" id="department_logged_in" class="form-select" required="">
                  <option value="">Pilih Klinik</option>
                  <option value="Klinik Jantung">Klinik Jantung</option>
                  <option value="Klinik Jiwa">Klinik Jiwa</option>
                  <option value="Klinik Paru">Klinik Paru</option>
                  <option value="Klinik THT">Klinik THT</option>
                  <option value="Klinik Mata">Klinik Mata</option>
                </select>
              </div>
              <div class="col-md-4 form-group mt-3">
                <label for="doctor_logged_in" class="form-label">Dokter <span class="text-danger">*</span></label>
                <select name="id_dokter" id="doctor_logged_in" class="form-select" required="">
                  <option value="">Pilih Dokter</option>
                  <?php foreach ($doctors as $doctor): ?>
                  <option value="<?= htmlspecialchars($doctor['id']); ?>"><?= htmlspecialchars($doctor['nama_dokter']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group mt-3">
              <label for="message_logged_in" class="form-label">Pesan (Opsional)</label>
              <textarea class="form-control" name="message" id="message_logged_in" rows="5" placeholder="Pesan (Opsional)"></textarea>
            </div>
            <div class="mt-3">
              <div class="loading">Loading</div>
              <div class="error-message"></div>
              <div class="sent-message">Permintaan janji temu Anda telah dikirimkan dengan sukses. Terima kasih!</div>
              <div class="text-center"><button type="submit">Buat Janji Temu</button></div>
            </div>
          </form>
        <?php endif; ?>

      </div>

    </section><!-- /Appointment Section -->

    <!-- Departments Section -->
    <section id="klinik" class="departments section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Klinik</h2>
        <p>Penjelasan lebih lanjut tentang klinik spesialis kami.</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-3">
            <ul class="nav nav-tabs flex-column">
              <li class="nav-item">
                <a class="nav-link active show" data-bs-toggle="tab" href="#klinik-tab-1">Klinik Jantung</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#klinik-tab-2">Klinik Jiwa</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#klinik-tab-3">Klinik Paru</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#klinik-tab-4">Klinik THT</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#klinik-tab-5">Klinik Mata</a>
              </li>
            </ul>
          </div>
          <div class="col-lg-9 mt-4 mt-lg-0">
            <div class="tab-content">
              <div class="tab-pane active show" id="klinik-tab-1">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Klinik Jantung</h3>
                    <p>Klinik Jantung di RSUD Majalengka memberikan layanan spesialis untuk diagnosis, pengobatan, dan perawatan berbagai masalah kesehatan jantung. Dengan dukungan fasilitas medis yang lengkap dan tenaga ahli, seperti dokter spesialis jantung, klinik ini menangani berbagai kondisi jantung seperti hipertensi, gangguan irama jantung, penyakit jantung koroner, dan masalah kardiovaskular lainnya. Klinik ini bertujuan untuk memberikan perawatan yang optimal dalam menjaga kesehatan jantung pasien serta meningkatkan kualitas hidup mereka.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/klinik/klinik-jantung.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="klinik-tab-2">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Klinik Jiwa</h3>
                    <p>Klinik Jiwa di RSUD Majalengka merupakan layanan kesehatan mental yang menyediakan perawatan dan pengobatan untuk pasien dengan gangguan jiwa atau masalah psikologis. Dengan dukungan tenaga medis profesional, klinik ini menawarkan berbagai layanan seperti konseling, terapi, serta pengobatan untuk kondisi seperti depresi, kecemasan, dan gangguan mental lainnya. Tujuan dari layanan ini adalah untuk mendukung pemulihan pasien dan meningkatkan kualitas hidup mereka secara menyeluruh.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/klinik/klinik-jiwa.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="klinik-tab-3">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Klinik Paru</h3>
                    <p>Klinik Paru kami menyediakan layanan medis spesialistik yang fokus pada diagnosis, perawatan, dan pengelolaan berbagai penyakit pada sistem pernapasan, terutama yang memengaruhi paru-paru dan saluran pernapasan. Kami berkomitmen untuk memberikan perawatan yang komprehensif bagi pasien dengan gangguan pernapasan, mulai dari infeksi saluran pernapasan hingga penyakit paru kronis.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/klinik/klinik-paru.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="klinik-tab-4">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Klinik Telinga, Hidung, Tenggorokan</h3>
                    <p>Klinik Telinga, Hidung, Tenggorokan (THT) kami menyediakan layanan medis spesialistik untuk diagnosis, pengobatan, dan perawatan berbagai masalah kesehatan yang terkait dengan telinga, hidung, dan tenggorokan. Kami didukung oleh dokter spesialis THT yang berpengalaman serta peralatan medis canggih untuk memberikan perawatan yang efektif dan tepat sesuai kebutuhan pasien.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/klinik/telinga-hidung-tenggorokan.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
              <div class="tab-pane" id="klinik-tab-5">
                <div class="row">
                  <div class="col-lg-8 details order-2 order-lg-1">
                    <h3>Klinik Mata</h3>
                    <p>Klinik Mata di RSUD Majalengka menyediakan layanan spesialis untuk diagnosis, pengobatan, dan perawatan berbagai masalah kesehatan mata. Dilengkapi dengan fasilitas medis yang modern, klinik ini menangani berbagai kondisi mata seperti rabun jauh, rabun dekat, glaukoma, katarak, dan infeksi mata. Dengan dukungan tenaga medis profesional, seperti dokter spesialis mata, klinik ini berkomitmen untuk memberikan perawatan terbaik guna meningkatkan kesehatan dan kenyamanan penglihatan pasien.</p>
                  </div>
                  <div class="col-lg-4 text-center order-1 order-lg-2">
                    <img src="assets/img/klinik/klinik-mata.png" alt="" class="img-fluid">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Departments Section -->

    <!-- Doctors Section -->
    <section id="doctors" class="doctors section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Dokter</h2>
        <p>Kami memiliki tim dokter yang berpengalaman dan terlatih dalam berbagai bidang medis.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4">

        <?php foreach ($doctors as $doctor): ?>
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-member d-flex align-items-start">
              <div class="pic"><img src="assets/img/doctors/<?= htmlspecialchars($doctor['foto']); ?>" class="img-fluid" alt=""></div>
              <div class="member-info">
                <h4><?= htmlspecialchars($doctor['nama_dokter']); ?></h4>
                <span><?= htmlspecialchars($doctor['spesialis']); ?></span>
                <p><?= htmlspecialchars($doctor['klinik']); ?></p>
                <div class="social">
                  <a href=""><i class="bi bi-twitter-x"></i></a>
                  <a href=""><i class="bi bi-facebook"></i></a>
                  <a href=""><i class="bi bi-instagram"></i></a>
                  <a href=""> <i class="bi bi-linkedin"></i> </a>
                </div>
              </div>
            </div>
          </div><!-- End Team Member -->
        <?php endforeach; ?>

        </div>

      </div>

    </section><!-- /Doctors Section -->

    <!-- Gallery Section -->
    <section id="gallery" class="gallery section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Gallery</h2>
        <p>Ini adalah galeri foto dari fasilitas dan layanan kami</p>
      </div><!-- End Section Title -->

      <div class="container-fluid" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-0">

          <?php foreach ($galleryImages as $image): ?>
          <div class="col-lg-3 col-md-4">
            <div class="gallery-item">
              <a href="assets/img/gallery/<?= htmlspecialchars($image['file_name']) ?>" class="glightbox" data-gallery="images-gallery" data-title="<?= htmlspecialchars($image['title']) ?>" data-description="<?= htmlspecialchars($image['description']) ?>">
                <img src="assets/img/gallery/<?= htmlspecialchars($image['file_name']) ?>" alt="<?= htmlspecialchars($image['title']) ?>" class="img-fluid">
              </a>
            </div>
          </div><!-- End Gallery Item -->
          <?php endforeach; ?>

        </div>

      </div>

    </section><!-- /Gallery Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Hubungi kami untuk informasi lebih lanjut atau untuk menjadwalkan janji temu</p>
      </div><!-- End Section Title -->

      <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3961.4725866063422!2d108.23094787399657!3d-6.833801793164202!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6f2f598beec1fd%3A0x60127300a96f15f6!2sRSUD%20Majalengka!5e0!3m2!1sen!2sid!4v1787096891502!5m2!1sen!2sid" width="100%" height="270px" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
      </div><!-- End Google Maps -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-4">
            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Lokasi</h3>
                <p>Jl. Kesehatan No. 7 Majalengka Jawa Barat</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Hubungi Kami</h3>
                <p>+6233281043</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>Email Us</h3>
                <p>info@rsudmajalengka.co.id</p>
              </div>
            </div><!-- End Info Item -->

          </div>

          <div class="col-lg-8">
            <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">

                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Nama Anda" required="">
                </div>

                <div class="col-md-6 ">
                  <input type="email" class="form-control" name="email" placeholder="Email Anda" required="">
                </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Subjek" required="">
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Pesan Anda" required=""></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Pesan Anda telah dikirim. Terima kasih!</div>

                  <button type="submit">Kirim Pesan</button>
                </div>

              </div>
            </form>
          </div><!-- End Contact Form -->

        </div>

      </div>

    </section><!-- /Contact Section -->

    <!-- Developer Profile Section -->
    <section id="developer-profile" class="developer-profile section light-background">
      <div class="container">
        <div class="section-title" data-aos="fade-up">
          <h2>Profil Developer</h2>
          <p>Informasi tentang developer</p>
        </div>

        <div class="developer-summary" data-aos="fade-up" data-aos-delay="100">
          <div class="developer-summary-icon">
            <img src="assets/img/dev.jpg" alt="Foto M. Zidan Maulana">
          </div>
          <div class="developer-summary-content">
            <span class="developer-eyebrow">Full stack</span>
            <h3>M. Zidan Maulana</h3>
            <div class="developer-summary-details">
              <span><i class="bi bi-envelope"></i> mzidanmaulana4@gmail.com</span>
              <span><i class="bi bi-geo-alt"></i> Jl. Desa Argalingga, blok Argalingga, desa Argalingga kec. Argapura kab. Majalengka</span>
            </div>
          </div>
          <a href="pages/developer-profile.html" class="btn-get-started developer-summary-button">
            Lihat Profil <i class="bi bi-arrow-up-right"></i>
          </a>
        </div>
      </div>
    </section><!-- /Developer Profile Section -->

  </main>

<?php include 'components/footer.php'; ?>

  <!-- Scroll Top -->
<?php include 'components/scroll-top.php'; ?>

  <!-- Preloader -->
  <div id="preloader"></div>

<?php include 'components/js.php' ?>

<script>
  /**
   * Handle appointment form submission for guest users
   * Redirects to login page when submit is clicked
   */
  function handleGuestAppointment() {
    window.location.href = '<?= rtrim(BASE_URL, '/') ?>/frontend/pages/login-page.php';
  }

  /**
   * Handle appointment form submission for users with incomplete data
   * Redirects to data-diri page when submit is clicked
   */
  function handleIncompleteAppointment() {
    window.location.href = '<?= rtrim(BASE_URL, '/') ?>/frontend/pages/data-diri.php';
  }
</script>

</body>

</html>