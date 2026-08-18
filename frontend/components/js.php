<?php 
require_once __DIR__ . '/../../config.php';
?>
  <!-- Vendor JS Files -->
  <script src="<?= BASE_URL ?>frontend/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>frontend/assets/vendor/php-email-form/validate.js"></script>
  <script src="<?= BASE_URL ?>frontend/assets/vendor/aos/aos.js"></script>
  <script src="<?= BASE_URL ?>frontend/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="<?= BASE_URL ?>frontend/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="<?= BASE_URL ?>frontend/assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="<?= BASE_URL ?>frontend/assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      function handleKlinikRouting(isPageLoad = false) {
        const hash = window.location.hash;
        const klinikSection = document.getElementById('klinik');
        
        // Guard: Jika tidak ada section #klinik di halaman ini, hentikan fungsi
        if (!klinikSection) return;

        if (hash && hash.startsWith('#klinik-tab-')) {
          // Perbaikan Selector: Mencari tombol .nav-link di dalam #klinik secara presisi
          const targetTabBtn = klinikSection.querySelector(`.nav-link[href="${hash}"]`);
          
          if (targetTabBtn) {
            // Cek apakah tab yang dituju SEBELUMNYA sudah aktif
            const isAlreadyActive = targetTabBtn.classList.contains('active');

            // Jika tab belum aktif, ganti tampilannya via Bootstrap Tab API
            if (!isAlreadyActive) {
              const tabInstance = bootstrap.Tab.getOrCreateInstance(targetTabBtn);
              tabInstance.show();
            }

            // Scroll HANYA JIKA datang dari halaman lain (isPageLoad) OR tab yang diklik belum aktif
            if (isPageLoad || !isAlreadyActive) {
              const headerOffset = 80; // Offet header sticky
              const elementPosition = klinikSection.getBoundingClientRect().top;
              const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

              setTimeout(() => {
                window.scrollTo({
                  top: offsetPosition,
                  behavior: 'smooth'
                });
              }, isPageLoad ? 300 : 0); 
            }
          }
        }
      }

      // 1. Eksekusi saat halaman pertama kali dimuat
      window.addEventListener('load', function() {
        handleKlinikRouting(true);
      });

      // 2. Eksekusi jika terjadi perubahan hash URL tanpa reload
      window.addEventListener('hashchange', function() {
        handleKlinikRouting(false);
      });
    });
  </script>