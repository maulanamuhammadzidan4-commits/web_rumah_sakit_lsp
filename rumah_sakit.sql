-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 20 Agu 2026 pada 06.06
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `rumah_sakit`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `appointments`
--

CREATE TABLE `appointments` (
  `id_appointment` int NOT NULL,
  `id_user` int NOT NULL,
  `id_dokter` int NOT NULL,
  `klinik` varchar(255) DEFAULT NULL,
  `tanggal_temu` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `appointments`
--

INSERT INTO `appointments` (`id_appointment`, `id_user`, `id_dokter`, `klinik`, `tanggal_temu`, `created_at`) VALUES
(1, 3, 4, 'Klinik Jantung', '2026-08-28 07:38:00', '2026-08-19 21:39:17'),
(2, 3, 4, 'Klinik Jantung', '2026-08-28 21:40:00', '2026-08-19 21:40:11'),
(3, 3, 3, 'Klinik Paru', '2026-08-06 06:35:00', '2026-08-20 06:35:51'),
(4, 3, 3, 'Klinik THT', '2026-09-17 06:41:00', '2026-08-20 06:41:28'),
(5, 3, 1, 'Klinik Jantung', '2026-08-21 06:45:00', '2026-08-20 06:45:52'),
(6, 3, 3, 'Klinik Jantung', '2030-07-19 07:10:00', '2026-08-20 07:10:43'),
(7, 3, 2, 'Klinik Paru', '2026-08-13 07:36:00', '2026-08-20 07:37:03'),
(8, 3, 2, 'Klinik Jantung', '2026-08-20 07:40:00', '2026-08-20 07:40:37'),
(9, 3, 3, 'Klinik Paru', '2026-08-15 07:43:00', '2026-08-20 07:44:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id` int NOT NULL,
  `nama_dokter` varchar(75) NOT NULL,
  `spesialis` varchar(50) NOT NULL,
  `klinik` varchar(50) NOT NULL,
  `foto` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id`, `nama_dokter`, `spesialis`, `klinik`, `foto`) VALUES
(1, 'dr. Tony Hermawan, Sp.S', 'Spesialis Saraf', 'Klinik Saraf', 'dr_tony_hermawan.png'),
(2, 'drg. Fachrul Razi, Sp.BM', 'Spesialis Bedah Mulut', 'Klinik Bedah Mulut', 'drg-fachrul-razi.png'),
(3, 'dr. Melindah, Sp. Pd', 'Spesialis Penyakit Dalam', 'Klinik dalam', 'dr-melindah.png'),
(4, 'dr. Faris Yuflih Fihaya, Sp. JP', 'Spesialis Jantung', 'Klinik Jantung', 'doctor_1787068773_40efcb03.jpg'),
(5, 'dr. Henny L., Sp.KFR', 'Spesialis Rehabilitasi Medik', 'Klinik Rehabilitasi Medik', 'doctor_1787096297_7d2a90ec.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `gallery`
--

CREATE TABLE `gallery` (
  `id` int NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `title` varchar(67) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `gallery`
--

INSERT INTO `gallery` (`id`, `file_name`, `title`, `description`) VALUES
(3, 'gallery_1787064397_305397f8.jpg', 'Loby', 'Area tunggu pengunjung'),
(4, 'gallery_1787064533_74179713.jpg', 'NICU', 'Ruang perawatan intensif yang dikhususkan untuk bayi yang baru lahir dengan kondisi yang kritis'),
(5, 'gallery_1787064609_84c9467c.jpg', 'Ruang Melati', '-'),
(6, 'gallery_1787064723_6c3ee6c1.jpg', 'IBS', 'Area ruang operasi yang lengkap dari persiapan pasien, ruang pulih sadar, dan ruang tunggu keluarga');

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` int NOT NULL,
  `permission_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`) VALUES
(1, 'access_admin_panel'),
(3, 'create_post'),
(2, 'manage_users'),
(4, 'view_dashboard');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(2, 'editor'),
(3, 'user');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 3),
(1, 4),
(2, 4),
(3, 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `telp` varchar(18) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `address`, `created_at`, `telp`) VALUES
(1, 'takao11', 'dramekutaka@gmail.com', '$2y$10$j3f2JzDvtIBc1F/J4uCujO7FL37mh5mb3hbwP10vPE/SDc8tsQLO6', NULL, NULL, '2026-08-13 06:26:47', NULL),
(2, 'Uchok', 'didismisbahudin17@gmail.com', '$2y$10$mEYDzF8CKcttayDsytR2xOdYYyQSwOpT6imzD.jXHYgYbCDyzUUVS', NULL, NULL, '2026-08-13 06:40:39', NULL),
(3, 'Tanaka', 'tanaka67@gmail.com', '$2y$10$w9HICHOo4606/1Me6libH.dq2HFbU3rNGZbdPoREaepOOVfJpMJ1i', 'Tanaka Yamaguchi', 'Jl. Raya Bogor, Jawa Barat', '2026-08-17 05:08:50', '08342567859'),
(5, 'Kouhei', 'kouhei43@gmail.com', '$2y$10$t1CgH68WD12Ui1uRG.xayeywyUPLTD/yqyJbeoNwNSq.6EFcZ/k.K', NULL, NULL, '2026-08-17 06:53:28', NULL),
(6, 'Dummy', 'akunboneka1@gmail.com', '$2y$10$WdJBq4sN3GPtNFzqjdPPWOIm7kCLkwOoruT5Wz/CC3XvNAhwQuBmW', NULL, NULL, '2026-08-17 07:25:26', NULL),
(7, 'Dummy2', 'akunboneka10@gmail.com', '$2y$10$lT3Db2tRejZFy4yXkWWg7e4eo/HEnH5T8RGmPWUNVgLxPN0fSsnK.', 'Dummy anak ke 2', 'Jl. Pabrik teh Cirebon', '2026-08-17 07:38:42', '0812345743'),
(8, 'Ameku Takao', 'maulanamuhammadzidan4@gmail.com', '$2y$10$z8HJOR2/4JCed97odLoS/eSxxB8dTAfFiB2MmV/RHchUiglreMCBW', NULL, NULL, '2026-08-17 08:16:55', NULL),
(9, 'Lukman', 'gakpakepengaman@gmail.com', '$2y$10$PO5oM8LWUSu5dVzbn2Me9epSb00BZho11Pp3JYvLLWb1vQUDVKLYG', NULL, NULL, '2026-08-18 07:03:37', NULL),
(10, 'Rafi', 'rafitra12@gmail.com', '$2y$10$VDABM2A0dZFVP3Mdl/7ZQ.KBuBCtXMdxlXQMBn2VqmxPhuVvdIEVO', NULL, NULL, '2026-08-18 23:24:55', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_medical_profiles`
--

CREATE TABLE `user_medical_profiles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `blood_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `medical_history` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `user_medical_profiles`
--

INSERT INTO `user_medical_profiles` (`id`, `user_id`, `blood_type`, `medical_history`, `updated_at`) VALUES
(1, 7, 'AB Positive (AB+)', 'Saya pernah mengidap kanker paru-paru stadium lima akibat keseringan merokok', '2026-08-18 16:23:04'),
(2, 3, 'O Positive (O+)', '', '2026-08-18 12:07:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(8, 1),
(1, 3),
(2, 3),
(3, 3),
(5, 3),
(6, 3),
(7, 3),
(9, 3),
(10, 3);

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id_appointment`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_name` (`file_name`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indeks untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `user_medical_profiles`
--
ALTER TABLE `user_medical_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id_appointment` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `user_medical_profiles`
--
ALTER TABLE `user_medical_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_medical_profiles`
--
ALTER TABLE `user_medical_profiles`
  ADD CONSTRAINT `user_medical_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
