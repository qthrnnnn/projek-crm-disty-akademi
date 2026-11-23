-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2026 at 01:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm_9`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `activity_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `entity_type` varchar(60) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `actor_name` varchar(255) DEFAULT NULL,
  `lead_name` varchar(255) DEFAULT NULL,
  `owner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `lead_id`, `user_id`, `type`, `subject`, `description`, `activity_at`, `created_at`, `updated_at`, `entity_type`, `entity_id`, `entity_name`, `actor_name`, `lead_name`, `owner_id`, `changes`) VALUES
(1, 1, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Mochammad Rizky Pratama', 2, NULL),
(2, 2, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Dwi Lestari Wulandari', 2, NULL),
(3, 3, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Hendra Gunawan', 2, NULL),
(4, 4, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Maya Anggraini', 2, NULL),
(5, 5, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Bagus Setiawan', 2, NULL),
(6, 6, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Rina Kusuma Dewi', 2, NULL),
(7, 7, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-14 12:30:32', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL, NULL, NULL, 'Budi Santoso', 'Aditya Tri Nugroho', 2, NULL),
(8, 1, 2, 'status_change', 'Pendaftaran Pelatihan Berhasil - Otomatis Menjadi Customer', 'Peserta didaftarkan ke program pelatihan dan otomatis terkonversi menjadi Customer resmi (Kode: CUST-202611-0001).', '2026-11-14 12:57:44', '2026-11-14 05:57:44', '2026-11-14 05:57:44', NULL, NULL, NULL, 'Budi Santoso', 'Mochammad Rizky Pratama', 2, NULL),
(9, 3, 2, 'status_change', 'Pendaftaran Pelatihan Berhasil - Otomatis Menjadi Customer', 'Peserta didaftarkan ke program pelatihan dan otomatis terkonversi menjadi Customer resmi (Kode: CUST-202611-0002).', '2026-11-14 13:03:59', '2026-11-14 06:03:59', '2026-11-14 06:03:59', NULL, NULL, NULL, 'Budi Santoso', 'Hendra Gunawan', 2, NULL),
(10, 2, 2, 'note', 'Pendaftaran Peserta (Menunggu Pembayaran)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Akan resmi masuk ke Daftar Customer setelah pembayaran terkonfirmasi.', '2026-11-14 15:10:53', '2026-11-14 08:10:53', '2026-11-14 08:10:53', NULL, NULL, NULL, 'Budi Santoso', 'Dwi Lestari Wulandari', 2, NULL),
(11, 4, 2, 'note', 'Pendaftaran Peserta (Menunggu Pembayaran)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Akan resmi masuk ke Daftar Customer setelah pembayaran terkonfirmasi.', '2026-11-14 16:17:17', '2026-11-14 09:17:17', '2026-11-14 09:17:17', NULL, NULL, NULL, 'Budi Santoso', 'Maya Anggraini', 2, NULL),
(12, 4, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-14 16:18:12', '2026-11-14 09:18:12', '2026-11-14 09:18:12', NULL, NULL, NULL, 'Admin CRM', 'Maya Anggraini', 2, NULL),
(13, 5, 2, 'note', 'Pendaftaran Peserta (Menunggu Pembayaran)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Akan resmi masuk ke Daftar Customer setelah pembayaran terkonfirmasi.', '2026-11-14 16:55:52', '2026-11-14 09:55:52', '2026-11-14 09:55:52', NULL, NULL, NULL, 'Budi Santoso', 'Bagus Setiawan', 2, NULL),
(14, 5, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 1.831.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-14 16:56:22', '2026-11-14 09:56:22', '2026-11-14 09:56:22', NULL, NULL, NULL, 'Admin CRM', 'Bagus Setiawan', 2, NULL),
(15, 8, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Fajar Ramadhan', 3, NULL),
(16, 9, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Nurul Hidayati', 3, NULL),
(17, 10, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Arif Wicaksono', 3, NULL),
(18, 11, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Dewi Sartika', 3, NULL),
(19, 12, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Eko Prasetyo Utomo', 3, NULL),
(20, 13, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Indah Permatasari', 3, NULL),
(21, 14, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-14 17:48:49', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL, NULL, NULL, 'Sari Dewi', 'Wahyu Tri Hidayat', 3, NULL),
(22, 8, 3, 'note', 'Pendaftaran Peserta (Menunggu Pembayaran)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Akan resmi masuk ke Daftar Customer setelah pembayaran terkonfirmasi.', '2026-11-14 19:20:57', '2026-11-14 12:20:57', '2026-11-14 12:20:57', NULL, NULL, NULL, 'Sari Dewi', 'Fajar Ramadhan', 3, NULL),
(23, 8, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-14 19:26:33', '2026-11-14 12:26:33', '2026-11-14 12:26:33', NULL, NULL, NULL, 'Admin CRM', 'Fajar Ramadhan', 3, NULL),
(24, 15, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Dimas Arya Pamungkas', 4, NULL),
(25, 16, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Ratna Juwita', 4, NULL),
(26, 17, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Kevin Sanjaya Putra', 4, NULL),
(27, 18, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Anisa Rahmawati', 4, NULL),
(28, 19, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Galang Fajar Kurnia', 4, NULL),
(29, 20, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Putri Ayu Maharani', 4, NULL),
(30, 21, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-15 02:34:27', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL, NULL, NULL, 'Andi Wijaya', 'Yudha Pratama Wicaksono', 4, NULL),
(31, 15, 4, 'status_change', 'Pendaftaran Terkonfirmasi - Resmi Menjadi Customer', 'Peserta didaftarkan dengan status terkonfirmasi dan resmi masuk ke Daftar Customer (Kode: CUST-202611-0007).', '2026-11-15 03:33:46', '2026-11-14 20:33:46', '2026-11-14 20:33:46', NULL, NULL, NULL, 'Andi Wijaya', 'Dimas Arya Pamungkas', 4, NULL),
(32, 15, 1, 'status_change', 'Pembayaran Ditolak (Tidak Valid)', 'Pembayaran Rp 1.831.500 dinyatakan TIDAK VALID oleh admin. Alasan: \"bukti palsu\". Notifikasi telah dikirimkan ke akun sales penanggung jawab.', '2026-11-15 03:43:53', '2026-11-14 20:43:53', '2026-11-14 20:43:53', NULL, NULL, NULL, 'Admin CRM', 'Dimas Arya Pamungkas', 4, NULL),
(33, 15, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 1.831.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-15 03:44:38', '2026-11-14 20:44:38', '2026-11-14 20:44:38', NULL, NULL, NULL, 'Admin CRM', 'Dimas Arya Pamungkas', 4, NULL),
(34, 15, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 1.831.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-15 03:44:47', '2026-11-14 20:44:47', '2026-11-14 20:44:47', NULL, NULL, NULL, 'Admin CRM', 'Dimas Arya Pamungkas', 4, NULL),
(35, 16, 2, 'status_change', 'Status Diperbarui ke Ditolak (Lost)', 'Hasil follow-up dicatat: \"Respon prospek: Tidak Tertarik. Dapat di-follow up ulang di masa depan.\". Status prospek otomatis diperbarui menjadi Ditolak (Lost).', '2026-11-15 04:02:06', '2026-11-14 21:02:06', '2026-11-14 21:02:06', NULL, NULL, NULL, 'Budi Santoso', 'Ratna Juwita', 4, NULL),
(36, 17, 4, 'status_change', 'Status Diperbarui ke Ditolak (Lost)', 'Hasil follow-up dicatat: \"Respon prospek: Nomor Salah. Calon peserta dapat di-follow up ulang di masa mendatang.\". Status prospek otomatis diperbarui menjadi Ditolak (Lost).', '2026-11-15 04:07:19', '2026-11-14 21:07:19', '2026-11-14 21:07:19', NULL, NULL, NULL, 'Andi Wijaya', 'Kevin Sanjaya Putra', 4, NULL),
(37, 16, 2, 'status_change', 'Status Diperbarui ke Negosiasi', 'Hasil follow-up dicatat: \"Prospek sedang negosiasi penawaran harga.\". Status prospek otomatis diperbarui menjadi Negosiasi.', '2026-11-15 04:15:38', '2026-11-14 21:15:38', '2026-11-14 21:15:38', NULL, NULL, NULL, 'Budi Santoso', 'Ratna Juwita', 4, NULL),
(38, 16, 2, 'status_change', 'Status Diperbarui ke Ditolak (Lost)', 'Hasil follow-up dicatat: \"Respon prospek: Tidak Tertarik. Belum ada kebutuhan pelatihan saat ini.\". Status prospek otomatis diperbarui menjadi Ditolak (Lost).', '2026-11-15 04:15:38', '2026-11-14 21:15:38', '2026-11-14 21:15:38', NULL, NULL, NULL, 'Budi Santoso', 'Ratna Juwita', 4, NULL),
(39, 18, 4, 'status_change', 'Status Diperbarui ke Aktif', 'Hasil follow-up dicatat: \"Prospek berminat dan meminta informasi penawaran lebih lanjut.\". Status prospek otomatis diperbarui menjadi Aktif.', '2026-11-15 04:22:29', '2026-11-14 21:22:29', '2026-11-14 21:22:29', NULL, NULL, NULL, 'Andi Wijaya', 'Anisa Rahmawati', 4, NULL),
(40, 18, 4, 'status_change', 'Pendaftaran Terkonfirmasi - Resmi Menjadi Customer', 'Peserta didaftarkan dengan status terkonfirmasi dan resmi masuk ke Daftar Customer (Kode: CUST-202611-0008).', '2026-11-15 04:23:02', '2026-11-14 21:23:02', '2026-11-14 21:23:02', NULL, NULL, NULL, 'Andi Wijaya', 'Anisa Rahmawati', 4, NULL),
(41, 18, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-15 04:28:33', '2026-11-14 21:28:33', '2026-11-14 21:28:33', NULL, NULL, NULL, 'Admin CRM', 'Anisa Rahmawati', 4, NULL),
(42, 18, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-15 04:31:38', '2026-11-14 21:31:38', '2026-11-14 21:31:38', NULL, NULL, NULL, 'Admin CRM', 'Anisa Rahmawati', 4, NULL),
(43, 26, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Dimas Arya Pamungkas', 2, NULL),
(44, 27, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Ratna Juwita', 2, NULL),
(45, 28, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Kevin Sanjaya Putra', 2, NULL),
(46, 29, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Anisa Rahmawati', 2, NULL),
(47, 30, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Galang Fajar Kurnia', 2, NULL),
(48, 31, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Putri Ayu Maharani', 2, NULL),
(49, 32, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-15 15:33:08', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL, NULL, NULL, 'Budi Santoso', 'Yudha Pratama Wicaksono', 2, NULL),
(50, 9, 3, 'status_change', 'Status Diperbarui ke Berhasil (Won)', 'Hasil follow-up dicatat: \"Closing berhasil! Calon peserta bersedia mendaftar.\". Status prospek otomatis diperbarui menjadi Berhasil (Won).', '2026-11-15 15:42:10', '2026-11-15 08:42:10', '2026-11-15 08:42:10', NULL, NULL, NULL, 'Sari Dewi', 'Nurul Hidayati', 3, NULL),
(51, 9, 3, 'note', 'Pendaftaran Peserta (Menunggu Verifikasi Pembayaran Admin)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Status prospek akan resmi menjadi Berhasil (Closing) setelah pembayaran diverifikasi valid oleh admin.', '2026-11-15 15:51:06', '2026-11-15 08:51:06', '2026-11-15 08:51:06', NULL, NULL, NULL, 'Sari Dewi', 'Nurul Hidayati', 3, NULL),
(52, 9, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-15 15:55:35', '2026-11-15 08:55:35', '2026-11-15 08:55:35', NULL, NULL, NULL, 'Admin CRM', 'Nurul Hidayati', 3, NULL),
(53, 26, 1, 'admin_reminder', 'Instruksi Admin ke Budi Santoso', 'Mohon segera tindak lanjuti prospek \"Dimas Arya Pamungkas\" (WA: 081552334401) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', '2026-11-16 07:07:01', '2026-11-16 00:07:01', '2026-11-16 00:07:01', NULL, NULL, NULL, 'Admin CRM', 'Dimas Arya Pamungkas', 2, NULL),
(54, 26, 2, 'note', 'Pendaftaran Peserta (Menunggu Verifikasi Pembayaran Admin)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Status prospek akan resmi menjadi Berhasil (Closing) setelah pembayaran diverifikasi valid oleh admin.', '2026-11-16 11:27:33', '2026-11-16 04:27:33', '2026-11-16 04:27:33', NULL, NULL, NULL, 'Budi Santoso', 'Dimas Arya Pamungkas', 2, NULL),
(55, 1, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-16 11:28:11', '2026-11-16 04:28:11', '2026-11-16 04:28:11', NULL, NULL, NULL, 'Admin CRM', 'Mochammad Rizky Pratama', 2, NULL),
(56, 26, 2, 'status_change', 'Status Diperbarui ke Ditolak (Lost)', 'Hasil follow-up dicatat: \"Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.\". Status prospek otomatis diperbarui menjadi Ditolak (Lost).', '2026-11-16 11:29:41', '2026-11-16 04:29:41', '2026-11-16 04:29:41', NULL, NULL, NULL, 'Budi Santoso', 'Dimas Arya Pamungkas', 2, NULL),
(60, 26, 2, 'note', 'Pendaftaran Peserta (Menunggu Verifikasi Pembayaran Admin)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Status prospek akan resmi menjadi Berhasil (Closing) setelah pembayaran diverifikasi valid oleh admin.', '2026-11-22 15:13:49', '2026-11-22 08:13:49', '2026-11-22 08:13:49', NULL, NULL, NULL, 'Budi Santoso', 'Dimas Arya Pamungkas', 2, NULL),
(61, 37, 2, 'note', 'Lead baru ditambahkan', 'Lead ditambahkan secara manual oleh Budi Santoso.', '2026-11-22 17:33:54', '2026-11-22 10:33:54', '2026-11-22 10:33:54', NULL, NULL, NULL, 'Budi Santoso', 'niki', 2, NULL),
(62, 38, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Mochammad Rizky Pratama', 2, NULL),
(63, 39, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Dwi Lestari Wulandari', 2, NULL),
(64, 40, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Hendra Gunawan', 2, NULL),
(65, 41, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Maya Anggraini', 2, NULL),
(66, 42, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Bagus Setiawan', 2, NULL),
(67, 43, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Rina Kusuma Dewi', 2, NULL),
(68, 44, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-22 17:34:27', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL, NULL, NULL, 'Budi Santoso', 'Aditya Tri Nugroho', 2, NULL),
(69, 45, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Fajar Ramadhan', 3, NULL),
(70, 46, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Nurul Hidayati', 3, NULL),
(71, 47, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Arif Wicaksono', 3, NULL),
(72, 48, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Dewi Sartika', 3, NULL),
(73, 49, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Eko Prasetyo Utomo', 3, NULL),
(74, 50, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Indah Permatasari', 3, NULL),
(75, 51, 3, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Sari Dewi sebagai Sales PIC.', '2026-11-22 17:35:40', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL, NULL, NULL, 'Sari Dewi', 'Wahyu Tri Hidayat', 3, NULL),
(76, 52, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Dimas Arya Pamungkas', 4, NULL),
(77, 53, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Ratna Juwita', 4, NULL),
(78, 54, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Kevin Sanjaya Putra', 4, NULL),
(79, 55, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Anisa Rahmawati', 4, NULL),
(80, 56, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Galang Fajar Kurnia', 4, NULL),
(81, 57, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Putri Ayu Maharani', 4, NULL),
(82, 58, 4, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Andi Wijaya sebagai Sales PIC.', '2026-11-22 17:36:15', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL, NULL, NULL, 'Andi Wijaya', 'Yudha Pratama Wicaksono', 4, NULL),
(83, 53, 1, 'admin_reminder', 'Instruksi Admin ke Andi Wijaya', 'Mohon segera tindak lanjuti prospek \"Ratna Juwita\" (WA: 081552334402) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', '2026-11-22 17:40:56', '2026-11-22 10:40:56', '2026-11-22 10:40:56', NULL, NULL, NULL, 'Admin CRM', 'Ratna Juwita', 4, NULL),
(84, 59, 4, 'note', 'Lead baru ditambahkan', 'Lead ditambahkan secara manual oleh Andi Wijaya.', '2026-11-22 18:14:47', '2026-11-22 11:14:47', '2026-11-22 11:14:47', NULL, NULL, NULL, 'Andi Wijaya', 'gilang', 4, NULL),
(88, NULL, 1, 'updated', 'Instansi diubah', 'Instansi \"Maspion Group Indonesia\" diubah.', '2026-11-22 18:35:25', '2026-11-22 11:35:25', '2026-11-22 11:35:25', 'institutions', 23, 'Maspion Group Indonesia', 'Admin CRM', NULL, NULL, '{\"type\":{\"before\":\"company\",\"after\":\"Perusahaan\"}}'),
(89, NULL, 1, 'created', 'Jadwal pelatihan ditambahkan', 'Jadwal pelatihan \"Jadwal pelatihan #3\" ditambahkan.', '2026-11-22 18:47:30', '2026-11-22 11:47:30', '2026-11-22 11:47:30', 'training-schedules', 3, 'Jadwal pelatihan #3', 'Admin CRM', NULL, NULL, '{\"product_id\":{\"before\":null,\"after\":3},\"trainer_id\":{\"before\":null,\"after\":6},\"training_date\":{\"before\":null,\"after\":\"2026-11-25 00:00:00\"},\"start_time\":{\"before\":null,\"after\":\"09:00\"},\"end_time\":{\"before\":null,\"after\":\"16:00\"},\"location\":{\"before\":null,\"after\":\"Zoom Meeting\"},\"capacity\":{\"before\":null,\"after\":30},\"status\":{\"before\":null,\"after\":\"scheduled\"}}'),
(90, 1, 1, 'updated', 'Pembayaran diubah', 'Pembayaran \"Pembayaran #22\" diubah.', '2026-11-22 18:58:46', '2026-11-22 11:58:46', '2026-11-22 11:58:46', 'payments', 22, 'Pembayaran #22', 'Admin CRM', 'Mochammad Rizky Pratama', 2, '{\"verified_by\":{\"before\":null,\"after\":1},\"status\":{\"before\":\"pending\",\"after\":\"valid\"},\"verified_at\":{\"before\":null,\"after\":\"2026-11-22 18:58:46\"}}'),
(91, 1, 1, 'updated', 'Pendaftaran peserta diubah', 'Pendaftaran peserta \"REG-20261122-0002\" diubah.', '2026-11-22 18:58:46', '2026-11-22 11:58:46', '2026-11-22 11:58:46', 'registrations', 13, 'REG-20261122-0002', 'Admin CRM', 'Mochammad Rizky Pratama', 2, '{\"status\":{\"before\":\"registered\",\"after\":\"confirmed\"}}'),
(92, 1, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-22 18:58:46', '2026-11-22 11:58:46', '2026-11-22 11:58:46', NULL, NULL, NULL, 'Admin CRM', 'Mochammad Rizky Pratama', 2, NULL),
(93, 1, 1, 'updated', 'Komisi diubah', 'Komisi \"Komisi #15\" diubah.', '2026-11-22 18:59:41', '2026-11-22 11:59:41', '2026-11-22 11:59:41', 'commissions', 15, 'Komisi #15', 'Admin CRM', 'Mochammad Rizky Pratama', 2, '{\"status\":{\"before\":\"pending\",\"after\":\"approved\"},\"approved_at\":{\"before\":null,\"after\":\"2026-11-22 18:59:41\"}}'),
(94, 1, 1, 'updated', 'Komisi diubah', 'Komisi \"Komisi #15\" diubah.', '2026-11-22 18:59:55', '2026-11-22 11:59:55', '2026-11-22 11:59:55', 'commissions', 15, 'Komisi #15', 'Admin CRM', 'Mochammad Rizky Pratama', 2, '{\"status\":{\"before\":\"approved\",\"after\":\"paid\"},\"paid_at\":{\"before\":null,\"after\":\"2026-11-23 01:59:00\"}}'),
(95, 1, 1, 'created', 'Pengingat ditambahkan', 'Pengingat \"Pengingat #16\" ditambahkan.', '2026-11-22 18:59:55', '2026-11-22 11:59:55', '2026-11-22 11:59:55', 'reminders', 16, 'Pengingat #16', 'Admin CRM', 'Mochammad Rizky Pratama', 2, '{\"type\":{\"before\":null,\"after\":\"commission_paid\"},\"commission_id\":{\"before\":null,\"after\":15},\"lead_id\":{\"before\":null,\"after\":1},\"admin_id\":{\"before\":null,\"after\":1},\"sales_id\":{\"before\":null,\"after\":2},\"message\":{\"before\":null,\"after\":\"Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Mochammad Rizky Pratama telah berhasil ditransfer pada 23 Nov 2026, 01:59 WIB.\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"is_read\":{\"before\":null,\"after\":false},\"is_completed\":{\"before\":null,\"after\":true}}'),
(96, 1, NULL, 'updated', 'Komisi diubah', 'Komisi \"Komisi #15\" diubah.', '2026-11-22 19:04:20', '2026-11-22 12:04:20', '2026-11-22 12:04:20', 'commissions', 15, 'Komisi #15', 'Sistem', 'Mochammad Rizky Pratama', 2, '{\"paid_at\":{\"before\":\"2026-11-23 01:59:00\",\"after\":\"2026-11-22 18:59:00\"}}'),
(97, 1, 2, 'updated', 'Pengingat diubah', 'Pengingat \"Pengingat #16\" diubah.', '2026-11-23 11:06:09', '2026-11-23 04:06:09', '2026-11-23 04:06:09', 'reminders', 16, 'Pengingat #16', 'Budi Santoso', 'Mochammad Rizky Pratama', 2, '{\"is_read\":{\"before\":0,\"after\":true},\"read_at\":{\"before\":null,\"after\":\"2026-11-23 11:06:08\"}}'),
(98, 43, 2, 'created', 'Follow-up ditambahkan', 'Follow-up \"Follow-Up (WhatsApp) - Rina Kusuma Dewi\" ditambahkan.', '2026-11-23 11:55:07', '2026-11-23 04:55:07', '2026-11-23 04:55:07', 'follow-ups', 11, 'Follow-Up (WhatsApp) - Rina Kusuma Dewi', 'Budi Santoso', 'Rina Kusuma Dewi', 2, '{\"lead_id\":{\"before\":null,\"after\":43},\"title\":{\"before\":null,\"after\":\"Follow-Up (WhatsApp) - Rina Kusuma Dewi\"},\"follow_up_at\":{\"before\":null,\"after\":\"2026-11-24 18:57:00\"},\"method\":{\"before\":null,\"after\":\"whatsapp\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"status\":{\"before\":null,\"after\":\"pending\"},\"assigned_to\":{\"before\":null,\"after\":2}}'),
(99, 43, 2, 'updated', 'Follow-up diubah', 'Follow-up \"Follow-Up (WhatsApp) - Rina Kusuma Dewi\" diubah.', '2026-11-23 11:55:26', '2026-11-23 04:55:26', '2026-11-23 04:55:26', 'follow-ups', 11, 'Follow-Up (WhatsApp) - Rina Kusuma Dewi', 'Budi Santoso', 'Rina Kusuma Dewi', 2, '{\"status\":{\"before\":\"pending\",\"after\":\"completed\"},\"result_notes\":{\"before\":null,\"after\":\"Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.\"},\"completed_at\":{\"before\":null,\"after\":\"2026-11-23 11:55:26\"}}'),
(100, 43, 2, 'updated', 'Lead diubah', 'Lead \"Rina Kusuma Dewi\" diubah.', '2026-11-23 11:55:26', '2026-11-23 04:55:26', '2026-11-23 04:55:26', 'leads', 43, 'Rina Kusuma Dewi', 'Budi Santoso', 'Rina Kusuma Dewi', 2, '{\"status\":{\"before\":\"new\",\"after\":\"lost\"},\"rejection_reason_id\":{\"before\":null,\"after\":8},\"rejection_notes\":{\"before\":null,\"after\":\"Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.\"},\"first_contacted_at\":{\"before\":null,\"after\":\"2026-11-23 11:55:26\"},\"last_contacted_at\":{\"before\":null,\"after\":\"2026-11-23 11:55:26\"},\"lost_at\":{\"before\":null,\"after\":\"2026-11-23 11:55:26\"}}'),
(101, 43, 2, 'status_change', 'Status Diperbarui ke Ditolak (Lost)', 'Hasil follow-up dicatat: \"Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.\". Status prospek otomatis diperbarui menjadi Ditolak (Lost).', '2026-11-23 11:55:26', '2026-11-23 04:55:26', '2026-11-23 04:55:26', NULL, NULL, NULL, 'Budi Santoso', 'Rina Kusuma Dewi', 2, NULL),
(102, NULL, 2, 'created', 'Impor data ditambahkan', 'Impor data \"sample_import_leads.csv\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-batches', 8, 'sample_import_leads.csv', 'Budi Santoso', NULL, 2, '{\"user_id\":{\"before\":null,\"after\":2},\"file_name\":{\"before\":null,\"after\":\"sample_import_leads.csv\"},\"file_path\":{\"before\":null,\"after\":\"imports\\/3mLUojg7rJK537ioCwZ542uNi2sZ0f9DSozfMZby.csv\"},\"total_rows\":{\"before\":null,\"after\":5},\"status\":{\"before\":null,\"after\":\"validating\"}}'),
(103, 60, 2, 'created', 'Lead baru ditambahkan', 'Lead \"Nadia Kusuma Putri\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'leads', 60, 'Nadia Kusuma Putri', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"lead_code\":{\"before\":null,\"after\":\"LDA-20261123-0001\"},\"name\":{\"before\":null,\"after\":\"Nadia Kusuma Putri\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1996-05-12\"},\"email\":{\"before\":null,\"after\":\"nadia.kusuma@pelindodatasolusi.co.id\"},\"phone\":{\"before\":null,\"after\":\"081234501101\"},\"whatsapp\":{\"before\":null,\"after\":\"081234501101\"},\"company\":{\"before\":null,\"after\":\"PT Pelindo Data Solusi\"},\"job_title\":{\"before\":null,\"after\":\"Staf Administrasi Arsip\"},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"product_id\":{\"before\":null,\"after\":1},\"lead_source_id\":{\"before\":null,\"after\":\"3\"},\"assigned_to\":{\"before\":null,\"after\":2},\"assigned_at\":{\"before\":null,\"after\":\"2026-11-23 12:00:29\"},\"status\":{\"before\":null,\"after\":\"new\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"notes\":{\"before\":null,\"after\":\"Tertarik ikut batch pelatihan Digital Filing bulan depan\"}}'),
(104, NULL, 2, 'created', 'Instansi ditambahkan', 'Instansi \"PT Pelindo Data Solusi\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'institutions', 28, 'PT Pelindo Data Solusi', 'Budi Santoso', NULL, NULL, '{\"name\":{\"before\":null,\"after\":\"PT Pelindo Data Solusi\"},\"type\":{\"before\":null,\"after\":\"company\"},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"address\":{\"before\":null,\"after\":\"Surabaya, Jawa Timur\"}}'),
(105, 60, 2, 'created', 'Baris impor ditambahkan', 'Baris impor \"Baris impor #50\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-rows', 50, 'Baris impor #50', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"import_batch_id\":{\"before\":null,\"after\":8},\"row_number\":{\"before\":null,\"after\":2},\"data\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"status\":{\"before\":null,\"after\":\"imported\"},\"lead_id\":{\"before\":null,\"after\":60}}'),
(106, 60, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL, NULL, NULL, 'Budi Santoso', 'Nadia Kusuma Putri', 2, NULL),
(107, 61, 2, 'created', 'Lead baru ditambahkan', 'Lead \"Raka Aditya Pratama\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'leads', 61, 'Raka Aditya Pratama', 'Budi Santoso', 'Raka Aditya Pratama', 2, '{\"lead_code\":{\"before\":null,\"after\":\"LDA-20261123-0002\"},\"name\":{\"before\":null,\"after\":\"Raka Aditya Pratama\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1993-08-23\"},\"email\":{\"before\":null,\"after\":\"raka.aditya@rsuddrsoetomo.go.id\"},\"phone\":{\"before\":null,\"after\":\"082145602202\"},\"whatsapp\":{\"before\":null,\"after\":\"082145602202\"},\"company\":{\"before\":null,\"after\":\"RSUD Dr. Soetomo Surabaya\"},\"job_title\":{\"before\":null,\"after\":\"Koordinator Layanan Pasien\"},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"product_id\":{\"before\":null,\"after\":2},\"lead_source_id\":{\"before\":null,\"after\":\"3\"},\"assigned_to\":{\"before\":null,\"after\":2},\"assigned_at\":{\"before\":null,\"after\":\"2026-11-23 12:00:29\"},\"status\":{\"before\":null,\"after\":\"new\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"notes\":{\"before\":null,\"after\":\"Ingin mendaftarkan tim front office untuk pelatihan pelayanan prima\"}}'),
(108, 61, 2, 'created', 'Baris impor ditambahkan', 'Baris impor \"Baris impor #51\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-rows', 51, 'Baris impor #51', 'Budi Santoso', 'Raka Aditya Pratama', 2, '{\"import_batch_id\":{\"before\":null,\"after\":8},\"row_number\":{\"before\":null,\"after\":3},\"data\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"status\":{\"before\":null,\"after\":\"imported\"},\"lead_id\":{\"before\":null,\"after\":61}}'),
(109, 61, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL, NULL, NULL, 'Budi Santoso', 'Raka Aditya Pratama', 2, NULL),
(110, 62, 2, 'created', 'Lead baru ditambahkan', 'Lead \"Dewi Kartika Sari\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'leads', 62, 'Dewi Kartika Sari', 'Budi Santoso', 'Dewi Kartika Sari', 2, '{\"lead_code\":{\"before\":null,\"after\":\"LDA-20261123-0003\"},\"name\":{\"before\":null,\"after\":\"Dewi Kartika Sari\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1991-11-14\"},\"email\":{\"before\":null,\"after\":\"dewi.kartika@its.ac.id\"},\"phone\":{\"before\":null,\"after\":\"085678903303\"},\"whatsapp\":{\"before\":null,\"after\":\"085678903303\"},\"company\":{\"before\":null,\"after\":\"Institut Teknologi Sepuluh Nopember\"},\"job_title\":{\"before\":null,\"after\":\"Staf Tata Usaha\"},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"product_id\":{\"before\":null,\"after\":3},\"lead_source_id\":{\"before\":null,\"after\":\"3\"},\"assigned_to\":{\"before\":null,\"after\":2},\"assigned_at\":{\"before\":null,\"after\":\"2026-11-23 12:00:29\"},\"status\":{\"before\":null,\"after\":\"new\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"notes\":{\"before\":null,\"after\":\"Menanyakan jadwal uji kompetensi BNSP dan rincian biaya\"}}'),
(111, NULL, 2, 'created', 'Instansi ditambahkan', 'Instansi \"Institut Teknologi Sepuluh Nopember\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'institutions', 29, 'Institut Teknologi Sepuluh Nopember', 'Budi Santoso', NULL, NULL, '{\"name\":{\"before\":null,\"after\":\"Institut Teknologi Sepuluh Nopember\"},\"type\":{\"before\":null,\"after\":\"Perguruan Tinggi\"},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"address\":{\"before\":null,\"after\":\"Surabaya, Jawa Timur\"}}'),
(112, 62, 2, 'created', 'Baris impor ditambahkan', 'Baris impor \"Baris impor #52\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-rows', 52, 'Baris impor #52', 'Budi Santoso', 'Dewi Kartika Sari', 2, '{\"import_batch_id\":{\"before\":null,\"after\":8},\"row_number\":{\"before\":null,\"after\":4},\"data\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"status\":{\"before\":null,\"after\":\"imported\"},\"lead_id\":{\"before\":null,\"after\":62}}'),
(113, 62, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL, NULL, NULL, 'Budi Santoso', 'Dewi Kartika Sari', 2, NULL),
(114, 63, 2, 'created', 'Lead baru ditambahkan', 'Lead \"Fajar Nugroho\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'leads', 63, 'Fajar Nugroho', 'Budi Santoso', 'Fajar Nugroho', 2, '{\"lead_code\":{\"before\":null,\"after\":\"LDA-20261123-0004\"},\"name\":{\"before\":null,\"after\":\"Fajar Nugroho\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1994-02-09\"},\"email\":{\"before\":null,\"after\":\"fajar.nugroho@malangkota.go.id\"},\"phone\":{\"before\":null,\"after\":\"081398704404\"},\"whatsapp\":{\"before\":null,\"after\":\"081398704404\"},\"company\":{\"before\":null,\"after\":\"Dinas Komunikasi dan Informatika Kota Malang\"},\"job_title\":{\"before\":null,\"after\":\"Pranata Komputer\"},\"city\":{\"before\":null,\"after\":\"Malang\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"product_id\":{\"before\":null,\"after\":1},\"lead_source_id\":{\"before\":null,\"after\":\"3\"},\"assigned_to\":{\"before\":null,\"after\":2},\"assigned_at\":{\"before\":null,\"after\":\"2026-11-23 12:00:29\"},\"status\":{\"before\":null,\"after\":\"new\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"notes\":{\"before\":null,\"after\":\"Meminta penawaran resmi dan silabus untuk pengajuan anggaran dinas\"}}'),
(115, NULL, 2, 'created', 'Instansi ditambahkan', 'Instansi \"Dinas Komunikasi dan Informatika Kota Malang\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'institutions', 30, 'Dinas Komunikasi dan Informatika Kota Malang', 'Budi Santoso', NULL, NULL, '{\"name\":{\"before\":null,\"after\":\"Dinas Komunikasi dan Informatika Kota Malang\"},\"type\":{\"before\":null,\"after\":\"company\"},\"city\":{\"before\":null,\"after\":\"Malang\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"address\":{\"before\":null,\"after\":\"Malang, Jawa Timur\"}}'),
(116, 63, 2, 'created', 'Baris impor ditambahkan', 'Baris impor \"Baris impor #53\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-rows', 53, 'Baris impor #53', 'Budi Santoso', 'Fajar Nugroho', 2, '{\"import_batch_id\":{\"before\":null,\"after\":8},\"row_number\":{\"before\":null,\"after\":5},\"data\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"status\":{\"before\":null,\"after\":\"imported\"},\"lead_id\":{\"before\":null,\"after\":63}}'),
(117, 63, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL, NULL, NULL, 'Budi Santoso', 'Fajar Nugroho', 2, NULL),
(118, 64, 2, 'created', 'Lead baru ditambahkan', 'Lead \"Aulia Rahmawati\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'leads', 64, 'Aulia Rahmawati', 'Budi Santoso', 'Aulia Rahmawati', 2, '{\"lead_code\":{\"before\":null,\"after\":\"LDA-20261123-0005\"},\"name\":{\"before\":null,\"after\":\"Aulia Rahmawati\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1997-07-27\"},\"email\":{\"before\":null,\"after\":\"aulia.rahma@bankjatim.co.id\"},\"phone\":{\"before\":null,\"after\":\"087712305505\"},\"whatsapp\":{\"before\":null,\"after\":\"087712305505\"},\"company\":{\"before\":null,\"after\":\"PT Bank Pembangunan Daerah Jawa Timur Tbk\"},\"job_title\":{\"before\":null,\"after\":\"Customer Service Officer\"},\"city\":{\"before\":null,\"after\":\"Sidoarjo\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"product_id\":{\"before\":null,\"after\":2},\"lead_source_id\":{\"before\":null,\"after\":\"3\"},\"assigned_to\":{\"before\":null,\"after\":2},\"assigned_at\":{\"before\":null,\"after\":\"2026-11-23 12:00:29\"},\"status\":{\"before\":null,\"after\":\"new\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"notes\":{\"before\":null,\"after\":\"Siap mengikuti kelas pelayanan prima terdekat\"}}'),
(119, NULL, 2, 'created', 'Instansi ditambahkan', 'Instansi \"PT Bank Pembangunan Daerah Jawa Timur Tbk\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'institutions', 31, 'PT Bank Pembangunan Daerah Jawa Timur Tbk', 'Budi Santoso', NULL, NULL, '{\"name\":{\"before\":null,\"after\":\"PT Bank Pembangunan Daerah Jawa Timur Tbk\"},\"type\":{\"before\":null,\"after\":\"company\"},\"city\":{\"before\":null,\"after\":\"Sidoarjo\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"address\":{\"before\":null,\"after\":\"Sidoarjo, Jawa Timur\"}}'),
(120, 64, 2, 'created', 'Baris impor ditambahkan', 'Baris impor \"Baris impor #54\" ditambahkan.', '2026-11-23 12:00:29', '2026-11-23 05:00:29', '2026-11-23 05:00:29', 'import-rows', 54, 'Baris impor #54', 'Budi Santoso', 'Aulia Rahmawati', 2, '{\"import_batch_id\":{\"before\":null,\"after\":8},\"row_number\":{\"before\":null,\"after\":6},\"data\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"status\":{\"before\":null,\"after\":\"imported\"},\"lead_id\":{\"before\":null,\"after\":64}}'),
(121, 64, 2, 'note', 'Import Leads (Excel)', 'Prospek berhasil diimpor dari file Excel dan otomatis ditugaskan ke Budi Santoso sebagai Sales PIC.', '2026-11-23 12:00:29', '2026-11-23 05:00:30', '2026-11-23 05:00:30', NULL, NULL, NULL, 'Budi Santoso', 'Aulia Rahmawati', 2, NULL),
(122, NULL, 2, 'updated', 'Impor data diubah', 'Impor data \"sample_import_leads.csv\" diubah.', '2026-11-23 12:00:30', '2026-11-23 05:00:30', '2026-11-23 05:00:30', 'import-batches', 8, 'sample_import_leads.csv', 'Budi Santoso', NULL, 2, '{\"status\":{\"before\":\"validating\",\"after\":\"completed\"},\"success_rows\":{\"before\":null,\"after\":5},\"failed_rows\":{\"before\":null,\"after\":0}}'),
(123, 60, 2, 'created', 'Customer ditambahkan', 'Customer \"Nadia Kusuma Putri\" ditambahkan.', '2026-11-23 12:01:11', '2026-11-23 05:01:11', '2026-11-23 05:01:11', 'customers', 10, 'Nadia Kusuma Putri', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"customer_code\":{\"before\":null,\"after\":\"CUST-202611-0010\"},\"name\":{\"before\":null,\"after\":\"Nadia Kusuma Putri\"},\"email\":{\"before\":null,\"after\":\"nadia.kusuma@pelindodatasolusi.co.id\"},\"phone\":{\"before\":null,\"after\":\"081234501101\"},\"nik\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"birth_date\":{\"before\":null,\"after\":\"1996-05-12 00:00:00\"},\"document_number\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"institution_id\":{\"before\":null,\"after\":28},\"lead_id\":{\"before\":null,\"after\":60},\"city\":{\"before\":null,\"after\":\"Surabaya\"},\"province\":{\"before\":null,\"after\":\"Jawa Timur\"},\"status\":{\"before\":null,\"after\":\"active\"},\"source\":{\"before\":null,\"after\":\"import_csv\"},\"notes\":{\"before\":null,\"after\":\"Otomatis masuk dari Lead LDA-20261123-0001 (Pelatihan dan Sertifikasi Digital Filing)\"}}'),
(124, 60, 2, 'updated', 'Customer diubah', 'Customer \"Nadia Kusuma Putri\" diubah.', '2026-11-23 12:01:11', '2026-11-23 05:01:11', '2026-11-23 05:01:11', 'customers', 10, 'Nadia Kusuma Putri', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"active\",\"after\":\"prospect\"}}'),
(125, 60, 2, 'updated', 'Lead diubah', 'Lead \"Nadia Kusuma Putri\" diubah.', '2026-11-23 12:01:11', '2026-11-23 05:01:11', '2026-11-23 05:01:11', 'leads', 60, 'Nadia Kusuma Putri', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"deal_price\":{\"before\":null,\"after\":4273500},\"status\":{\"before\":\"new\",\"after\":\"negotiation\"}}'),
(126, 60, 2, 'note', 'Pendaftaran Peserta (Menunggu Verifikasi Pembayaran Admin)', 'Calon peserta didaftarkan ke program pelatihan (Status: Registered). Status prospek akan resmi menjadi Berhasil (Closing) setelah pembayaran diverifikasi valid oleh admin.', '2026-11-23 12:01:11', '2026-11-23 05:01:11', '2026-11-23 05:01:11', NULL, NULL, NULL, 'Budi Santoso', 'Nadia Kusuma Putri', 2, NULL),
(127, 60, 2, 'created', 'Pendaftaran peserta ditambahkan', 'Pendaftaran peserta \"REG-20261123-0001\" ditambahkan.', '2026-11-23 12:01:11', '2026-11-23 05:01:11', '2026-11-23 05:01:11', 'registrations', 15, 'REG-20261123-0001', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"product_id\":{\"before\":null,\"after\":1},\"training_schedule_id\":{\"before\":null,\"after\":1},\"status\":{\"before\":null,\"after\":\"registered\"},\"amount\":{\"before\":null,\"after\":4273500},\"registration_number\":{\"before\":null,\"after\":\"REG-20261123-0001\"},\"user_id\":{\"before\":null,\"after\":2},\"customer_id\":{\"before\":null,\"after\":10},\"training_date\":{\"before\":null,\"after\":\"2026-11-15 00:00:00\"}}'),
(128, 60, 2, 'created', 'Pembayaran ditambahkan', 'Pembayaran \"Pembayaran #23\" ditambahkan.', '2026-11-23 12:01:42', '2026-11-23 05:01:42', '2026-11-23 05:01:42', 'payments', 23, 'Pembayaran #23', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"lead_id\":{\"before\":null,\"after\":60},\"registration_id\":{\"before\":null,\"after\":\"15\"},\"product_id\":{\"before\":null,\"after\":1},\"amount\":{\"before\":null,\"after\":\"4273500\"},\"bank_name\":{\"before\":null,\"after\":\"BCA\"},\"account_name\":{\"before\":null,\"after\":\"Nadia Kusuma Putri\"},\"proof_file\":{\"before\":null,\"after\":\"payments\\/OkwVX033fuj4TmUL4sCZzeaTo8Lgp8pDLSzRWlfu.jpg\"}}'),
(129, 60, 2, 'created', 'Komisi ditambahkan', 'Komisi \"Komisi #16\" ditambahkan.', '2026-11-23 12:01:42', '2026-11-23 05:01:42', '2026-11-23 05:01:42', 'commissions', 16, 'Komisi #16', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"user_id\":{\"before\":null,\"after\":2},\"lead_id\":{\"before\":null,\"after\":60},\"payment_id\":{\"before\":null,\"after\":23},\"deal_amount\":{\"before\":null,\"after\":4273500},\"commission_rate\":{\"before\":null,\"after\":10},\"commission_amount\":{\"before\":null,\"after\":388500},\"bank_name\":{\"before\":null,\"after\":\"BNI\"},\"bank_account_number\":{\"before\":null,\"after\":\"[disembunyikan]\"},\"bank_account_name\":{\"before\":null,\"after\":\"Budi Santoso\"},\"status\":{\"before\":null,\"after\":\"pending\"}}'),
(130, 60, 1, 'updated', 'Pembayaran diubah', 'Pembayaran \"Pembayaran #23\" diubah.', '2026-11-23 12:01:57', '2026-11-23 05:01:57', '2026-11-23 05:01:57', 'payments', 23, 'Pembayaran #23', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"verified_by\":{\"before\":null,\"after\":1},\"status\":{\"before\":\"pending\",\"after\":\"valid\"},\"verified_at\":{\"before\":null,\"after\":\"2026-11-23 12:01:57\"}}'),
(131, 60, 1, 'updated', 'Lead diubah', 'Lead \"Nadia Kusuma Putri\" diubah.', '2026-11-23 12:01:57', '2026-11-23 05:01:57', '2026-11-23 05:01:57', 'leads', 60, 'Nadia Kusuma Putri', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"negotiation\",\"after\":\"won\"},\"converted_at\":{\"before\":null,\"after\":\"2026-11-23 12:01:57\"}}'),
(132, 60, 1, 'updated', 'Customer diubah', 'Customer \"Nadia Kusuma Putri\" diubah.', '2026-11-23 12:01:57', '2026-11-23 05:01:57', '2026-11-23 05:01:57', 'customers', 10, 'Nadia Kusuma Putri', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"prospect\",\"after\":\"active\"}}');
INSERT INTO `activities` (`id`, `lead_id`, `user_id`, `type`, `subject`, `description`, `activity_at`, `created_at`, `updated_at`, `entity_type`, `entity_id`, `entity_name`, `actor_name`, `lead_name`, `owner_id`, `changes`) VALUES
(133, 60, 1, 'updated', 'Pendaftaran peserta diubah', 'Pendaftaran peserta \"REG-20261123-0001\" diubah.', '2026-11-23 12:01:57', '2026-11-23 05:01:57', '2026-11-23 05:01:57', 'registrations', 15, 'REG-20261123-0001', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"registered\",\"after\":\"confirmed\"}}'),
(134, 60, 1, 'status_change', 'Pembayaran Valid - Status diubah ke Berhasil (Won)', 'Pembayaran Rp 4.273.500 diverifikasi valid oleh admin. Pendaftaran peserta otomatis berstatus confirmed.', '2026-11-23 12:01:57', '2026-11-23 05:01:57', '2026-11-23 05:01:57', NULL, NULL, NULL, 'Admin CRM', 'Nadia Kusuma Putri', 2, NULL),
(135, 60, 1, 'updated', 'Komisi diubah', 'Komisi \"Komisi #16\" diubah.', '2026-11-23 12:02:04', '2026-11-23 05:02:04', '2026-11-23 05:02:04', 'commissions', 16, 'Komisi #16', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"pending\",\"after\":\"approved\"},\"approved_at\":{\"before\":null,\"after\":\"2026-11-23 12:02:04\"}}'),
(136, 60, 1, 'updated', 'Komisi diubah', 'Komisi \"Komisi #16\" diubah.', '2026-11-23 12:02:14', '2026-11-23 05:02:14', '2026-11-23 05:02:14', 'commissions', 16, 'Komisi #16', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"status\":{\"before\":\"approved\",\"after\":\"paid\"},\"paid_at\":{\"before\":null,\"after\":\"2026-11-23 12:02:00\"}}'),
(137, 60, 1, 'created', 'Pengingat ditambahkan', 'Pengingat \"Pengingat #17\" ditambahkan.', '2026-11-23 12:02:14', '2026-11-23 05:02:14', '2026-11-23 05:02:14', 'reminders', 17, 'Pengingat #17', 'Admin CRM', 'Nadia Kusuma Putri', 2, '{\"type\":{\"before\":null,\"after\":\"commission_paid\"},\"commission_id\":{\"before\":null,\"after\":16},\"lead_id\":{\"before\":null,\"after\":60},\"admin_id\":{\"before\":null,\"after\":1},\"sales_id\":{\"before\":null,\"after\":2},\"message\":{\"before\":null,\"after\":\"Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Nadia Kusuma Putri telah berhasil ditransfer pada 23 Nov 2026, 19:02 WIB.\"},\"priority\":{\"before\":null,\"after\":\"normal\"},\"is_read\":{\"before\":null,\"after\":false},\"is_completed\":{\"before\":null,\"after\":true}}'),
(138, 60, 2, 'updated', 'Pengingat diubah', 'Pengingat \"Pengingat #17\" diubah.', '2026-11-23 12:02:32', '2026-11-23 05:02:32', '2026-11-23 05:02:32', 'reminders', 17, 'Pengingat #17', 'Budi Santoso', 'Nadia Kusuma Putri', 2, '{\"is_read\":{\"before\":0,\"after\":true},\"read_at\":{\"before\":null,\"after\":\"2026-11-23 12:02:32\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deal_amount` decimal(15,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `commission_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(150) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `approved_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `commissions`
--

INSERT INTO `commissions` (`id`, `user_id`, `lead_id`, `payment_id`, `deal_amount`, `commission_rate`, `commission_amount`, `bank_name`, `bank_account_number`, `bank_account_name`, `status`, `approved_at`, `paid_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 1, 1831500.00, 10.00, 166500.00, 'BCA', '883019281', 'Budi Santoso', 'paid', '2026-11-14 13:41:26', '2026-11-14 20:41:00', '81978000', '2026-11-14 06:38:38', '2026-11-14 07:10:02'),
(2, 2, 2, 10, 4273500.00, 10.00, 388500.00, 'BCA', '883019281', 'Budi Santoso', 'paid', '2026-11-14 15:53:35', '2026-11-14 22:54:00', NULL, '2026-11-14 08:11:46', '2026-11-14 08:54:34'),
(3, 2, 4, 11, 4273500.00, 10.00, 388500.00, 'BCA', '883019281', 'Budi Santoso', 'paid', '2026-11-14 16:18:53', '2026-11-14 23:18:00', '8889990000', '2026-11-14 09:17:47', '2026-11-14 09:19:09'),
(4, 2, 5, 12, 1831500.00, 10.00, 166500.00, 'BNI', '883019281', 'Budi Santoso', 'paid', '2026-11-14 17:12:06', '2026-11-15 00:12:00', NULL, '2026-11-14 09:56:08', '2026-11-14 10:12:12'),
(5, 3, 8, 13, 4273500.00, 10.00, 388500.00, 'Bank Mandiri', '141002938192', 'Sari Dewi', 'paid', '2026-11-14 19:21:39', '2026-11-15 02:21:00', NULL, '2026-11-14 12:21:18', '2026-11-14 12:21:42'),
(7, 4, 15, 14, 1831500.00, 10.00, 166500.00, 'BNI', '0928371928', 'Andi Wijaya', 'paid', '2026-11-15 03:44:59', '2026-11-15 10:45:00', NULL, '2026-11-14 20:44:38', '2026-11-14 20:45:02'),
(8, 4, 18, 15, 4273500.00, 10.00, 388500.00, 'BNI', '0928371928', 'Andi Wijaya', 'paid', '2026-11-15 04:32:09', '2026-11-15 11:32:00', NULL, '2026-11-14 21:23:27', '2026-11-14 21:32:21'),
(9, 3, 9, 16, 4273500.00, 10.00, 388500.00, 'Bank Mandiri', '141002938192', 'Sari Dewi', 'paid', '2026-11-15 15:55:47', '2026-11-15 22:55:00', NULL, '2026-11-15 08:51:37', '2026-11-15 08:56:07'),
(10, 2, 1, 17, 4273500.00, 10.00, 388500.00, 'BNI', '883019281', 'Budi Santoso', 'paid', '2026-11-16 11:28:20', '2026-11-16 18:28:00', NULL, '2026-11-16 04:27:57', '2026-11-16 04:28:24'),
(12, 2, 4, 19, 976800.00, 10.00, 88800.00, 'BNI', '883019281', 'Budi Santoso', 'paid', '2026-11-16 12:14:03', '2026-11-16 19:14:00', NULL, '2026-11-16 05:12:42', '2026-11-16 05:36:13'),
(13, 4, 15, 20, 4273500.00, 10.00, 388500.00, 'BNI', '0928371928', 'Andi Wijaya', 'pending', NULL, NULL, NULL, '2026-11-22 08:04:16', '2026-11-22 08:04:16'),
(14, 2, 4, 21, 1831500.00, 10.00, 166500.00, 'BNI', '883019281', 'Budi Santoso', 'pending', NULL, NULL, NULL, '2026-11-22 09:07:55', '2026-11-22 09:07:55'),
(15, 2, 1, 22, 4273500.00, 10.00, 388500.00, 'BNI', '883019281', 'Budi Santoso', 'paid', '2026-11-22 18:59:41', '2026-11-22 18:59:00', NULL, '2026-11-22 09:11:36', '2026-11-22 11:59:55'),
(16, 2, 60, 23, 4273500.00, 10.00, 388500.00, 'BNI', '883019281', 'Budi Santoso', 'paid', '2026-11-23 12:02:04', '2026-11-23 12:02:00', NULL, '2026-11-23 05:01:42', '2026-11-23 05:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `institution_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `source` varchar(50) NOT NULL DEFAULT 'manual',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_code`, `name`, `email`, `phone`, `nik`, `birth_date`, `document_number`, `institution_id`, `lead_id`, `city`, `province`, `status`, `source`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CUST-202611-0001', 'Mochammad Rizky Pratama', 'rizky.pratama@jatimprov.go.id', '081230112201', '3578011204920001', '1992-04-12', '3578011204920001', 5, 1, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0001 (Program)', '2026-11-14 05:57:44', '2026-11-16 04:28:11', NULL),
(2, 'CUST-202611-0002', 'Hendra Gunawan', 'hendra.gunawan@surabaya.go.id', '081230112203', '3578032101900003', '1990-01-21', '3578032101900003', 7, 3, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0003 (Program)', '2026-11-14 06:03:59', '2026-11-14 06:03:59', NULL),
(3, 'CUST-202611-0003', 'Dwi Lestari Wulandari', 'dwi.lestari@pelindo.co.id', '081230112202', '3578025608940002', '1994-08-16', '3578025608940002', 6, 2, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0002 (Program)', '2026-11-14 08:10:53', '2026-11-14 09:06:25', NULL),
(4, 'CUST-202611-0004', 'Maya Anggraini', 'maya.anggraini@pln.co.id', '081230112204', '3578046709950004', '1995-09-27', '3578046709950004', 8, 4, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0004 (Program)', '2026-11-14 09:17:17', '2026-11-16 05:36:13', NULL),
(5, 'CUST-202611-0005', 'Bagus Setiawan', 'bagus.setiawan@kemendagri.go.id', '081230112205', '3578051803930005', '1993-03-18', '3578051803930005', 9, 5, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0005 (Program)', '2026-11-14 09:55:52', '2026-11-14 09:56:22', NULL),
(6, 'CUST-202611-0006', 'Fajar Ramadhan', 'fajar.ramadhan@bankmandiri.co.id', '081331223301', '3578081402920008', '1992-02-14', '3578081402920008', 12, 8, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0008 (Program)', '2026-11-14 12:20:57', '2026-11-14 12:26:33', NULL),
(7, 'CUST-202611-0007', 'Dimas Arya Pamungkas', 'dimas.arya@its.ac.id', '081552334401', '3578151908940015', '1994-08-19', '3578151908940015', 19, 15, 'Surabaya', 'Jawa Timur', 'prospect', 'import_excel', 'Otomatis masuk dari Lead LDA-20261115-0001 (Program)', '2026-11-14 20:33:46', '2026-11-16 04:27:33', NULL),
(8, 'CUST-202611-0008', 'Anisa Rahmawati', 'anisa.rahmawati@unesa.ac.id', '081552334404', '3578184511970018', '1997-11-05', '3578184511970018', 22, 18, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261115-0004 (Program)', '2026-11-14 21:23:02', '2026-11-14 21:31:38', NULL),
(9, 'CUST-202611-0009', 'Nurul Hidayati', 'nurul.hidayati@rsudsoetomo.id', '081331223302', '3578095507950009', '1995-07-15', '3578095507950009', 13, 9, 'Surabaya', 'Jawa Timur', 'active', 'import_excel', 'Otomatis masuk dari Lead LDA-20261114-0009 (Program)', '2026-11-15 08:51:06', '2026-11-15 08:55:35', NULL),
(10, 'CUST-202611-0010', 'Nadia Kusuma Putri', 'nadia.kusuma@pelindodatasolusi.co.id', '081234501101', '3578041205960001', '1996-05-12', '3578041205960001', 28, 60, 'Surabaya', 'Jawa Timur', 'active', 'import_csv', 'Otomatis masuk dari Lead LDA-20261123-0001 (Pelatihan dan Sertifikasi Digital Filing)', '2026-11-23 05:01:11', '2026-11-23 05:01:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `follow_ups`
--

CREATE TABLE `follow_ups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `follow_up_at` datetime NOT NULL,
  `method` varchar(30) NOT NULL DEFAULT 'whatsapp',
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `result_notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `follow_ups`
--

INSERT INTO `follow_ups` (`id`, `lead_id`, `assigned_to`, `title`, `description`, `follow_up_at`, `method`, `priority`, `status`, `result_notes`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 15, 4, 'Follow-Up (WhatsApp) - Dimas Arya Pamungkas', 'mengingatkan kembali', '2026-11-16 10:46:00', 'whatsapp', 'normal', 'completed', 'Otomatis selesai: Prospek telah berhasil (closing) / terdaftar program', '2026-11-15 04:42:33', '2026-11-14 20:49:06', '2026-11-14 21:42:33'),
(2, 15, 4, 'Follow-Up (WhatsApp) - Dimas Arya Pamungkas', 'mengingatkan kembali', '2026-11-16 10:49:00', 'whatsapp', 'normal', 'completed', NULL, '2026-11-15 03:50:43', '2026-11-14 20:49:57', '2026-11-14 20:50:43'),
(3, 16, 4, 'Follow-Up (WhatsApp) - Ratna Juwita', NULL, '2026-11-17 10:51:00', 'whatsapp', 'normal', 'completed', 'Respon prospek: Tidak Tertarik. Belum ada kebutuhan pelatihan saat ini.', '2026-11-15 04:15:38', '2026-11-14 20:51:08', '2026-11-14 21:15:38'),
(4, 17, 4, 'Follow-Up (WhatsApp) - Kevin Sanjaya Putra', NULL, '2026-11-15 11:06:00', 'whatsapp', 'normal', 'completed', 'Respon prospek: Nomor Salah. Calon peserta dapat di-follow up ulang di masa mendatang.', '2026-11-15 04:07:19', '2026-11-14 21:07:06', '2026-11-14 21:07:19'),
(5, 18, 4, 'Follow-Up (WhatsApp) - Anisa Rahmawati', NULL, '2026-11-16 11:21:00', 'whatsapp', 'normal', 'completed', 'Prospek berminat dan meminta informasi penawaran lebih lanjut.', '2026-11-15 04:22:29', '2026-11-14 21:22:02', '2026-11-14 21:22:29'),
(6, 9, 3, 'Follow-Up (WhatsApp) - Nurul Hidayati', NULL, '2026-11-16 22:41:00', 'whatsapp', 'normal', 'completed', 'Closing berhasil! Calon peserta bersedia mendaftar.', '2026-11-15 15:42:10', '2026-11-15 08:41:24', '2026-11-15 08:42:10'),
(7, 26, 2, 'Pengingat Follow-up Admin', 'Mohon segera tindak lanjuti prospek \"Dimas Arya Pamungkas\" (WA: 081552334401) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', '2026-11-23 00:00:00', 'whatsapp', 'high', 'completed', 'Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.', '2026-11-16 11:29:41', '2026-11-16 00:07:01', '2026-11-16 04:29:41'),
(8, 27, 2, 'Follow-Up (WhatsApp) - Ratna Juwita', NULL, '2026-11-16 18:28:00', 'whatsapp', 'normal', 'pending', NULL, NULL, '2026-11-16 04:28:58', '2026-11-16 04:28:58'),
(9, 34, 2, 'Follow-Up (WhatsApp) - Qothrun Nada S.', NULL, '2026-11-24 04:27:00', 'whatsapp', 'normal', 'pending', NULL, NULL, '2026-11-22 10:24:01', '2026-11-22 10:24:01'),
(10, 53, 4, 'Pengingat Follow-up Admin', 'Mohon segera tindak lanjuti prospek \"Ratna Juwita\" (WA: 081552334402) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', '2026-11-25 00:00:00', 'whatsapp', 'high', 'pending', NULL, NULL, '2026-11-22 10:40:56', '2026-11-22 10:40:56'),
(11, 43, 2, 'Follow-Up (WhatsApp) - Rina Kusuma Dewi', NULL, '2026-11-24 18:57:00', 'whatsapp', 'normal', 'completed', 'Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.', '2026-11-23 11:55:26', '2026-11-23 04:55:07', '2026-11-23 04:55:26');

-- --------------------------------------------------------

--
-- Table structure for table `import_batches`
--

CREATE TABLE `import_batches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `total_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `success_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `failed_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `import_batches`
--

INSERT INTO `import_batches` (`id`, `user_id`, `file_name`, `file_path`, `total_rows`, `success_rows`, `failed_rows`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'leads_batch_1.xlsx', 'imports/Lqc0y8i7z675gDuQeAahHNwAJfMsHLctxesTKjoi.xlsx', 7, 7, 0, 'completed', '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(2, 3, 'leads_batch_2.xlsx', 'imports/zDVka95rgzebv0nqOfiSQpOJmdA0a3axEGw6TioP.xlsx', 7, 7, 0, 'completed', '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(3, 4, 'leads_batch_3.xlsx', 'imports/TvSKSeLReP4SF58j4aOOkRwateHtMeQvt8R2JLBg.xlsx', 7, 7, 0, 'completed', '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(4, 2, 'leads_batch_3.xlsx', 'imports/emKDESaVaCCBA5J6RfDvEi3weyQYVD42UQbncjhK.xlsx', 7, 7, 0, 'completed', '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(5, 2, 'leads_batch_1.xlsx', 'imports/9gdfW9pEzP27pBe5ZRPDuRuEKKfBWb38iKrXLn0x.xlsx', 7, 7, 0, 'completed', '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(6, 3, 'leads_batch_2.xlsx', 'imports/RED7aFGsVBWVFdj9ytFWCiY24iLtQOmz8QKeAZsq.xlsx', 7, 7, 0, 'completed', '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(7, 4, 'leads_batch_3.xlsx', 'imports/qC7BVdFg90Thlhj5M45DPbC3lcPOJ929HQ5h30g8.xlsx', 7, 7, 0, 'completed', '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(8, 2, 'sample_import_leads.csv', 'imports/3mLUojg7rJK537ioCwZ542uNi2sZ0f9DSozfMZby.csv', 5, 5, 0, 'completed', '2026-11-23 05:00:29', '2026-11-23 05:00:30');

-- --------------------------------------------------------

--
-- Table structure for table `import_rows`
--

CREATE TABLE `import_rows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `import_batch_id` bigint(20) UNSIGNED NOT NULL,
  `row_number` int(10) UNSIGNED NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`errors`)),
  `lead_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `import_rows`
--

INSERT INTO `import_rows` (`id`, `import_batch_id`, `row_number`, `data`, `status`, `errors`, `lead_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '{\"name\":\"Mochammad Rizky Pratama\",\"phone\":\"081230112201\",\"email\":\"rizky.pratama@jatimprov.go.id\",\"company\":\"Bappeda Provinsi Jawa Timur\"}', 'imported', NULL, 1, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(2, 1, 3, '{\"name\":\"Dwi Lestari Wulandari\",\"phone\":\"081230112202\",\"email\":\"dwi.lestari@pelindo.co.id\",\"company\":\"PT Pelabuhan Indonesia (Pelindo)\"}', 'imported', NULL, 2, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(3, 1, 4, '{\"name\":\"Hendra Gunawan\",\"phone\":\"081230112203\",\"email\":\"hendra.gunawan@surabaya.go.id\",\"company\":\"Dinas Komunikasi dan Informatika Surabaya\"}', 'imported', NULL, 3, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(4, 1, 5, '{\"name\":\"Maya Anggraini\",\"phone\":\"081230112204\",\"email\":\"maya.anggraini@pln.co.id\",\"company\":\"PT PLN (Persero) UID Jawa Timur\"}', 'imported', NULL, 4, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(5, 1, 6, '{\"name\":\"Bagus Setiawan\",\"phone\":\"081230112205\",\"email\":\"bagus.setiawan@kemendagri.go.id\",\"company\":\"Balai Diklat Keagamaan Surabaya\"}', 'imported', NULL, 5, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(6, 1, 7, '{\"name\":\"Rina Kusuma Dewi\",\"phone\":\"081230112206\",\"email\":\"rina.kusuma@telkom.co.id\",\"company\":\"PT Telkom Indonesia Witel Jatim\"}', 'imported', NULL, 6, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(7, 1, 8, '{\"name\":\"Aditya Tri Nugroho\",\"phone\":\"081230112207\",\"email\":\"aditya.tri@pertamina.com\",\"company\":\"PT Pertamina Patra Niaga Jatimbalinus\"}', 'imported', NULL, 7, '2026-11-14 05:30:32', '2026-11-14 05:30:32'),
(8, 2, 2, '{\"name\":\"Fajar Ramadhan\",\"phone\":\"081331223301\",\"email\":\"fajar.ramadhan@bankmandiri.co.id\",\"company\":\"Bank Mandiri Kantor Cabang Surabaya\"}', 'imported', NULL, 8, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(9, 2, 3, '{\"name\":\"Nurul Hidayati\",\"phone\":\"081331223302\",\"email\":\"nurul.hidayati@rsudsoetomo.id\",\"company\":\"RSUD Dr. Soetomo Surabaya\"}', 'imported', NULL, 9, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(10, 2, 4, '{\"name\":\"Arif Wicaksono\",\"phone\":\"081331223303\",\"email\":\"arif.wicaksono@bca.co.id\",\"company\":\"Bank Central Asia (BCA) KCU Darmo\"}', 'imported', NULL, 10, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(11, 2, 5, '{\"name\":\"Dewi Sartika\",\"phone\":\"081331223304\",\"email\":\"dewi.sartika@bpjs-kesehatan.go.id\",\"company\":\"BPJS Kesehatan Cabang Surabaya\"}', 'imported', NULL, 11, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(12, 2, 6, '{\"name\":\"Eko Prasetyo Utomo\",\"phone\":\"081331223305\",\"email\":\"eko.utomo@bankjatim.co.id\",\"company\":\"PT Bank Pembangunan Daerah Jawa Timur (Bank Jatim)\"}', 'imported', NULL, 12, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(13, 2, 7, '{\"name\":\"Indah Permatasari\",\"phone\":\"081331223306\",\"email\":\"indah.permatasari@silooamhospitals.com\",\"company\":\"Siloam Hospitals Surabaya\"}', 'imported', NULL, 13, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(14, 2, 8, '{\"name\":\"Wahyu Tri Hidayat\",\"phone\":\"081331223307\",\"email\":\"wahyu.hidayat@bni.co.id\",\"company\":\"PT Bank Negara Indonesia (Persero) Tbk\"}', 'imported', NULL, 14, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(15, 3, 2, '{\"name\":\"Dimas Arya Pamungkas\",\"phone\":\"081552334401\",\"email\":\"dimas.arya@its.ac.id\",\"company\":\"Institut Teknologi Sepuluh Nopember (ITS)\"}', 'imported', NULL, 15, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(16, 3, 3, '{\"name\":\"Ratna Juwita\",\"phone\":\"081552334402\",\"email\":\"ratna.juwita@unair.ac.id\",\"company\":\"Universitas Airlangga (UNAIR)\"}', 'imported', NULL, 16, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(17, 3, 4, '{\"name\":\"Kevin Sanjaya Putra\",\"phone\":\"081552334403\",\"email\":\"kevin.sanjaya@wingscorp.com\",\"company\":\"PT Wings Surya Surabaya\"}', 'imported', NULL, 17, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(18, 3, 5, '{\"name\":\"Anisa Rahmawati\",\"phone\":\"081552334404\",\"email\":\"anisa.rahmawati@unesa.ac.id\",\"company\":\"Universitas Negeri Surabaya (UNESA)\"}', 'imported', NULL, 18, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(19, 3, 6, '{\"name\":\"Galang Fajar Kurnia\",\"phone\":\"081552334405\",\"email\":\"galang.fajar@maspion.com\",\"company\":\"Maspion Group Indonesia\"}', 'imported', NULL, 19, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(20, 3, 7, '{\"name\":\"Putri Ayu Maharani\",\"phone\":\"081552334406\",\"email\":\"putri.ayu@ubaya.ac.id\",\"company\":\"Universitas Surabaya (UBAYA)\"}', 'imported', NULL, 20, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(21, 3, 8, '{\"name\":\"Yudha Pratama Wicaksono\",\"phone\":\"081552334407\",\"email\":\"yudha.pratama@pakuwon.com\",\"company\":\"PT Pakuwon Jati Tbk\"}', 'imported', NULL, 21, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(22, 4, 2, '{\"name\":\"Dimas Arya Pamungkas\",\"phone\":\"081552334401\",\"email\":\"dimas.arya@its.ac.id\",\"company\":\"Institut Teknologi Sepuluh Nopember (ITS)\"}', 'imported', NULL, 26, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(23, 4, 3, '{\"name\":\"Ratna Juwita\",\"phone\":\"081552334402\",\"email\":\"ratna.juwita@unair.ac.id\",\"company\":\"Universitas Airlangga (UNAIR)\"}', 'imported', NULL, 27, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(24, 4, 4, '{\"name\":\"Kevin Sanjaya Putra\",\"phone\":\"081552334403\",\"email\":\"kevin.sanjaya@wingscorp.com\",\"company\":\"PT Wings Surya Surabaya\"}', 'imported', NULL, 28, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(25, 4, 5, '{\"name\":\"Anisa Rahmawati\",\"phone\":\"081552334404\",\"email\":\"anisa.rahmawati@unesa.ac.id\",\"company\":\"Universitas Negeri Surabaya (UNESA)\"}', 'imported', NULL, 29, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(26, 4, 6, '{\"name\":\"Galang Fajar Kurnia\",\"phone\":\"081552334405\",\"email\":\"galang.fajar@maspion.com\",\"company\":\"Maspion Group Indonesia\"}', 'imported', NULL, 30, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(27, 4, 7, '{\"name\":\"Putri Ayu Maharani\",\"phone\":\"081552334406\",\"email\":\"putri.ayu@ubaya.ac.id\",\"company\":\"Universitas Surabaya (UBAYA)\"}', 'imported', NULL, 31, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(28, 4, 8, '{\"name\":\"Yudha Pratama Wicaksono\",\"phone\":\"081552334407\",\"email\":\"yudha.pratama@pakuwon.com\",\"company\":\"PT Pakuwon Jati Tbk\"}', 'imported', NULL, 32, '2026-11-15 08:33:08', '2026-11-15 08:33:08'),
(29, 5, 2, '{\"name\":\"Mochammad Rizky Pratama\",\"phone\":\"081230112201\",\"email\":\"rizky.pratama@jatimprov.go.id\",\"company\":\"Bappeda Provinsi Jawa Timur\"}', 'imported', NULL, 38, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(30, 5, 3, '{\"name\":\"Dwi Lestari Wulandari\",\"phone\":\"081230112202\",\"email\":\"dwi.lestari@pelindo.co.id\",\"company\":\"PT Pelabuhan Indonesia (Pelindo)\"}', 'imported', NULL, 39, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(31, 5, 4, '{\"name\":\"Hendra Gunawan\",\"phone\":\"081230112203\",\"email\":\"hendra.gunawan@surabaya.go.id\",\"company\":\"Dinas Komunikasi dan Informatika Surabaya\"}', 'imported', NULL, 40, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(32, 5, 5, '{\"name\":\"Maya Anggraini\",\"phone\":\"081230112204\",\"email\":\"maya.anggraini@pln.co.id\",\"company\":\"PT PLN (Persero) UID Jawa Timur\"}', 'imported', NULL, 41, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(33, 5, 6, '{\"name\":\"Bagus Setiawan\",\"phone\":\"081230112205\",\"email\":\"bagus.setiawan@kemendagri.go.id\",\"company\":\"Balai Diklat Keagamaan Surabaya\"}', 'imported', NULL, 42, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(34, 5, 7, '{\"name\":\"Rina Kusuma Dewi\",\"phone\":\"081230112206\",\"email\":\"rina.kusuma@telkom.co.id\",\"company\":\"PT Telkom Indonesia Witel Jatim\"}', 'imported', NULL, 43, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(35, 5, 8, '{\"name\":\"Aditya Tri Nugroho\",\"phone\":\"081230112207\",\"email\":\"aditya.tri@pertamina.com\",\"company\":\"PT Pertamina Patra Niaga Jatimbalinus\"}', 'imported', NULL, 44, '2026-11-22 10:34:27', '2026-11-22 10:34:27'),
(36, 6, 2, '{\"name\":\"Fajar Ramadhan\",\"phone\":\"081331223301\",\"email\":\"fajar.ramadhan@bankmandiri.co.id\",\"company\":\"Bank Mandiri Kantor Cabang Surabaya\"}', 'imported', NULL, 45, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(37, 6, 3, '{\"name\":\"Nurul Hidayati\",\"phone\":\"081331223302\",\"email\":\"nurul.hidayati@rsudsoetomo.id\",\"company\":\"RSUD Dr. Soetomo Surabaya\"}', 'imported', NULL, 46, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(38, 6, 4, '{\"name\":\"Arif Wicaksono\",\"phone\":\"081331223303\",\"email\":\"arif.wicaksono@bca.co.id\",\"company\":\"Bank Central Asia (BCA) KCU Darmo\"}', 'imported', NULL, 47, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(39, 6, 5, '{\"name\":\"Dewi Sartika\",\"phone\":\"081331223304\",\"email\":\"dewi.sartika@bpjs-kesehatan.go.id\",\"company\":\"BPJS Kesehatan Cabang Surabaya\"}', 'imported', NULL, 48, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(40, 6, 6, '{\"name\":\"Eko Prasetyo Utomo\",\"phone\":\"081331223305\",\"email\":\"eko.utomo@bankjatim.co.id\",\"company\":\"PT Bank Pembangunan Daerah Jawa Timur (Bank Jatim)\"}', 'imported', NULL, 49, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(41, 6, 7, '{\"name\":\"Indah Permatasari\",\"phone\":\"081331223306\",\"email\":\"indah.permatasari@silooamhospitals.com\",\"company\":\"Siloam Hospitals Surabaya\"}', 'imported', NULL, 50, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(42, 6, 8, '{\"name\":\"Wahyu Tri Hidayat\",\"phone\":\"081331223307\",\"email\":\"wahyu.hidayat@bni.co.id\",\"company\":\"PT Bank Negara Indonesia (Persero) Tbk\"}', 'imported', NULL, 51, '2026-11-22 10:35:40', '2026-11-22 10:35:40'),
(43, 7, 2, '{\"name\":\"Dimas Arya Pamungkas\",\"phone\":\"081552334401\",\"email\":\"dimas.arya@its.ac.id\",\"company\":\"Institut Teknologi Sepuluh Nopember (ITS)\"}', 'imported', NULL, 52, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(44, 7, 3, '{\"name\":\"Ratna Juwita\",\"phone\":\"081552334402\",\"email\":\"ratna.juwita@unair.ac.id\",\"company\":\"Universitas Airlangga (UNAIR)\"}', 'imported', NULL, 53, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(45, 7, 4, '{\"name\":\"Kevin Sanjaya Putra\",\"phone\":\"081552334403\",\"email\":\"kevin.sanjaya@wingscorp.com\",\"company\":\"PT Wings Surya Surabaya\"}', 'imported', NULL, 54, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(46, 7, 5, '{\"name\":\"Anisa Rahmawati\",\"phone\":\"081552334404\",\"email\":\"anisa.rahmawati@unesa.ac.id\",\"company\":\"Universitas Negeri Surabaya (UNESA)\"}', 'imported', NULL, 55, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(47, 7, 6, '{\"name\":\"Galang Fajar Kurnia\",\"phone\":\"081552334405\",\"email\":\"galang.fajar@maspion.com\",\"company\":\"Maspion Group Indonesia\"}', 'imported', NULL, 56, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(48, 7, 7, '{\"name\":\"Putri Ayu Maharani\",\"phone\":\"081552334406\",\"email\":\"putri.ayu@ubaya.ac.id\",\"company\":\"Universitas Surabaya (UBAYA)\"}', 'imported', NULL, 57, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(49, 7, 8, '{\"name\":\"Yudha Pratama Wicaksono\",\"phone\":\"081552334407\",\"email\":\"yudha.pratama@pakuwon.com\",\"company\":\"PT Pakuwon Jati Tbk\"}', 'imported', NULL, 58, '2026-11-22 10:36:15', '2026-11-22 10:36:15'),
(50, 8, 2, '{\"name\":\"Nadia Kusuma Putri\",\"phone\":\"081234501101\",\"email\":\"nadia.kusuma@pelindodatasolusi.co.id\",\"company\":\"PT Pelindo Data Solusi\"}', 'imported', NULL, 60, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(51, 8, 3, '{\"name\":\"Raka Aditya Pratama\",\"phone\":\"082145602202\",\"email\":\"raka.aditya@rsuddrsoetomo.go.id\",\"company\":\"RSUD Dr. Soetomo Surabaya\"}', 'imported', NULL, 61, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(52, 8, 4, '{\"name\":\"Dewi Kartika Sari\",\"phone\":\"085678903303\",\"email\":\"dewi.kartika@its.ac.id\",\"company\":\"Institut Teknologi Sepuluh Nopember\"}', 'imported', NULL, 62, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(53, 8, 5, '{\"name\":\"Fajar Nugroho\",\"phone\":\"081398704404\",\"email\":\"fajar.nugroho@malangkota.go.id\",\"company\":\"Dinas Komunikasi dan Informatika Kota Malang\"}', 'imported', NULL, 63, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(54, 8, 6, '{\"name\":\"Aulia Rahmawati\",\"phone\":\"087712305505\",\"email\":\"aulia.rahma@bankjatim.co.id\",\"company\":\"PT Bank Pembangunan Daerah Jawa Timur Tbk\"}', 'imported', NULL, 64, '2026-11-23 05:00:29', '2026-11-23 05:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`id`, `name`, `type`, `email`, `phone`, `address`, `city`, `province`, `industry`, `notes`, `created_at`, `updated_at`) VALUES
(2, 'PT Budi Maju', 'company', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-14 04:48:11', '2026-11-14 04:48:11'),
(3, 'CV Budi Sejahtera', 'company', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-14 04:48:11', '2026-11-14 04:48:11'),
(4, 'PT Sari Sukses', 'company', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-14 04:48:11', '2026-11-14 04:48:11'),
(5, 'Bappeda Provinsi Jawa Timur', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(6, 'PT Pelabuhan Indonesia (Pelindo)', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(7, 'Dinas Komunikasi dan Informatika Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(8, 'PT PLN (Persero) UID Jawa Timur', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(9, 'Balai Diklat Keagamaan Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(10, 'PT Telkom Indonesia Witel Jatim', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(11, 'PT Pertamina Patra Niaga Jatimbalinus', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 05:16:19', '2026-11-14 05:16:19'),
(12, 'Bank Mandiri Kantor Cabang Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(13, 'RSUD Dr. Soetomo Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(14, 'Bank Central Asia (BCA) KCU Darmo', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(15, 'BPJS Kesehatan Cabang Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(16, 'PT Bank Pembangunan Daerah Jawa Timur (Bank Jatim)', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(17, 'Siloam Hospitals Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(18, 'PT Bank Negara Indonesia (Persero) Tbk', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 10:48:49', '2026-11-14 10:48:49'),
(19, 'Institut Teknologi Sepuluh Nopember (ITS)', 'Perguruan Tinggi', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-22 10:59:54'),
(20, 'Universitas Airlangga (UNAIR)', 'Perguruan Tinggi', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-22 11:10:46'),
(21, 'PT Wings Surya Surabaya', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(22, 'Universitas Negeri Surabaya (UNESA)', 'Perguruan Tinggi', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(23, 'Maspion Group Indonesia', 'Perusahaan', NULL, NULL, 'Sidoarjo, Jawa Timur', 'Sidoarjo', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-22 11:35:25'),
(24, 'Universitas Surabaya (UBAYA)', 'Perguruan Tinggi', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(25, 'PT Pakuwon Jati Tbk', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-14 19:34:27', '2026-11-14 19:34:27'),
(26, 'Institut Teknologi Bandung', 'Perguruan Tinggi', NULL, NULL, 'Bandung, Jawa Barat', NULL, NULL, 'Pendidikan', 'Otomatis tersimpan dari input Prospek / Lead CRM.', '2026-11-22 11:18:17', '2026-11-22 11:19:33'),
(28, 'PT Pelindo Data Solusi', 'company', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(29, 'Institut Teknologi Sepuluh Nopember', 'Perguruan Tinggi', NULL, NULL, 'Surabaya, Jawa Timur', 'Surabaya', 'Jawa Timur', NULL, NULL, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(30, 'Dinas Komunikasi dan Informatika Kota Malang', 'company', NULL, NULL, 'Malang, Jawa Timur', 'Malang', 'Jawa Timur', NULL, NULL, '2026-11-23 05:00:29', '2026-11-23 05:00:29'),
(31, 'PT Bank Pembangunan Daerah Jawa Timur Tbk', 'company', NULL, NULL, 'Sidoarjo, Jawa Timur', 'Sidoarjo', 'Jawa Timur', NULL, NULL, '2026-11-23 05:00:29', '2026-11-23 05:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `lead_source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deal_price` decimal(15,2) DEFAULT NULL COMMENT 'Harga kesepakatan negosiasi sales dengan pelanggan',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `rejection_reason_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rejection_notes` text DEFAULT NULL,
  `lead_score` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `first_contacted_at` datetime DEFAULT NULL,
  `last_contacted_at` datetime DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `lost_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `lead_code`, `name`, `email`, `phone`, `whatsapp`, `nik`, `birth_date`, `company`, `job_title`, `city`, `province`, `address`, `lead_source_id`, `product_id`, `deal_price`, `assigned_to`, `assigned_at`, `status`, `rejection_reason_id`, `rejection_notes`, `lead_score`, `priority`, `first_contacted_at`, `last_contacted_at`, `converted_at`, `lost_at`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'LDA-20261114-0001', 'Mochammad Rizky Pratama', 'rizky.pratama@jatimprov.go.id', '081230112201', '081230112201', '3578011204920001', '1992-04-12', 'Bappeda Provinsi Jawa Timur', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 2, '2026-11-14 12:30:32', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-16 11:28:11', NULL, 'Prospek instansi pemprov, belum tentukan program minat', '2026-11-14 05:30:32', '2026-11-16 04:28:11', NULL),
(2, 'LDA-20261114-0002', 'Dwi Lestari Wulandari', 'dwi.lestari@pelindo.co.id', '081230112202', '081230112202', '3578025608940002', '1994-08-16', 'PT Pelabuhan Indonesia (Pelindo)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 2, '2026-11-14 12:30:32', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-14 16:06:25', NULL, 'Kebutuhan in-house training BUMN', '2026-11-14 05:30:32', '2026-11-14 09:06:25', NULL),
(3, 'LDA-20261114-0003', 'Hendra Gunawan', 'hendra.gunawan@surabaya.go.id', '081230112203', '081230112203', '3578032101900003', '1990-01-21', 'Dinas Komunikasi dan Informatika Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 2, 1831500.00, 2, '2026-11-14 12:30:32', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-14 13:03:59', NULL, 'Follow up kebutuhan diklat staf', '2026-11-14 05:30:32', '2026-11-14 07:10:02', NULL),
(4, 'LDA-20261114-0004', 'Maya Anggraini', 'maya.anggraini@pln.co.id', '081230112204', '081230112204', '3578046709950004', '1995-09-27', 'PT PLN (Persero) UID Jawa Timur', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 2, '2026-11-14 12:30:32', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-14 16:18:12', NULL, 'Menunggu penawaran katalog program terbaru', '2026-11-14 05:30:32', '2026-11-16 05:06:04', NULL),
(5, 'LDA-20261114-0005', 'Bagus Setiawan', 'bagus.setiawan@kemendagri.go.id', '081230112205', '081230112205', '3578051803930005', '1993-03-18', 'Balai Diklat Keagamaan Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 2, 1831500.00, 2, '2026-11-14 12:30:32', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-14 16:56:22', NULL, 'Perlu konsultasi silabus materi pelatihan', '2026-11-14 05:30:32', '2026-11-14 09:56:22', NULL),
(6, 'LDA-20261114-0006', 'Rina Kusuma Dewi', 'rina.kusuma@telkom.co.id', '081230112206', '081230112206', '3578064911960006', '1996-11-09', 'PT Telkom Indonesia Witel Jatim', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-14 12:30:32', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta dikirimi proposal umum', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL),
(7, 'LDA-20261114-0007', 'Aditya Tri Nugroho', 'aditya.tri@pertamina.com', '081230112207', '081230112207', '3578070505910007', '1991-05-05', 'PT Pertamina Patra Niaga Jatimbalinus', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-14 12:30:32', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Diskusi lanjutan jadwal ketersediaan kelas', '2026-11-14 05:30:32', '2026-11-14 05:30:32', NULL),
(8, 'LDA-20261114-0008', 'Fajar Ramadhan', 'fajar.ramadhan@bankmandiri.co.id', '081331223301', '081331223301', '3578081402920008', '1992-02-14', 'Bank Mandiri Kantor Cabang Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 3, '2026-11-14 17:48:49', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-14 19:26:33', NULL, 'Eksplorasi pelatihan perbankan', '2026-11-14 10:48:49', '2026-11-14 12:26:33', NULL),
(9, 'LDA-20261114-0009', 'Nurul Hidayati', 'nurul.hidayati@rsudsoetomo.id', '081331223302', '081331223302', '3578095507950009', '1995-07-15', 'RSUD Dr. Soetomo Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 3, '2026-11-14 17:48:49', 'won', NULL, NULL, 0, 'normal', '2026-11-15 15:42:10', '2026-11-15 15:42:10', '2026-11-15 15:55:35', NULL, 'Rencana peningkatan mutu tenaga kesehatan', '2026-11-14 10:48:49', '2026-11-15 08:55:35', NULL),
(10, 'LDA-20261114-0010', 'Arif Wicaksono', 'arif.wicaksono@bca.co.id', '081331223303', '081331223303', '3578101010890010', '1989-10-10', 'Bank Central Asia (BCA) KCU Darmo', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-14 17:48:49', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menunggu konfirmasi anggaran bagian HRD', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL),
(11, 'LDA-20261114-0011', 'Dewi Sartika', 'dewi.sartika@bpjs-kesehatan.go.id', '081331223304', '081331223304', '3578116204970011', '1997-04-22', 'BPJS Kesehatan Cabang Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-14 17:48:49', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Kebutuhan pelatihan internal divisi operasional', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL),
(12, 'LDA-20261114-0012', 'Eko Prasetyo Utomo', 'eko.utomo@bankjatim.co.id', '081331223305', '081331223305', '3578120806930012', '1993-06-08', 'PT Bank Pembangunan Daerah Jawa Timur (Bank Jatim)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-14 17:48:49', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Koordinasi awal via WhatsApp', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL),
(13, 'LDA-20261114-0013', 'Indah Permatasari', 'indah.permatasari@silooamhospitals.com', '081331223306', '081331223306', '3578134301980013', '1998-01-03', 'Siloam Hospitals Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-14 17:48:49', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta info perbandingan program reguler vs private', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL),
(14, 'LDA-20261114-0014', 'Wahyu Tri Hidayat', 'wahyu.hidayat@bni.co.id', '081331223307', '081331223307', '3578142512900014', '1990-12-25', 'PT Bank Negara Indonesia (Persero) Tbk', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-14 17:48:49', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Rencana pelatihan kuartal depan', '2026-11-14 10:48:49', '2026-11-14 10:48:49', NULL),
(15, 'LDA-20261115-0001', 'Dimas Arya Pamungkas', 'dimas.arya@its.ac.id', '081552334401', '081552334401', '3578151908940015', '1994-08-19', 'Institut Teknologi Sepuluh Nopember (ITS)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 2, 1831500.00, 4, '2026-11-15 02:34:27', 'won', NULL, NULL, 0, 'normal', '2026-11-15 03:50:43', '2026-11-15 03:50:43', '2026-11-15 03:33:46', NULL, 'Prospek kampus, menanyakan akreditasi pelatihan', '2026-11-14 19:34:27', '2026-11-14 20:50:43', NULL),
(16, 'LDA-20261115-0002', 'Ratna Juwita', 'ratna.juwita@unair.ac.id', '081552334402', '081552334402', '3578166010960016', '1996-10-20', 'Universitas Airlangga (UNAIR)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-15 02:34:27', 'lost', 9, 'Respon prospek: Tidak Tertarik. Belum ada kebutuhan pelatihan saat ini.', 0, 'normal', '2026-11-15 03:51:25', '2026-11-15 04:15:38', NULL, '2026-11-15 04:15:38', 'Penjajakan kerja sama pelatihan staf tendik', '2026-11-14 19:34:27', '2026-11-14 21:15:38', NULL),
(17, 'LDA-20261115-0003', 'Kevin Sanjaya Putra', 'kevin.sanjaya@wingscorp.com', '081552334403', '081552334403', '3578170203950017', '1995-03-02', 'PT Wings Surya Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-15 02:34:27', 'lost', 6, 'Respon prospek: Nomor Salah. Calon peserta dapat di-follow up ulang di masa mendatang.', 0, 'normal', '2026-11-15 04:07:19', '2026-11-15 04:07:19', NULL, '2026-11-15 04:07:19', 'Kebutuhan pelatihan corporate sales', '2026-11-14 19:34:27', '2026-11-14 21:07:19', NULL),
(18, 'LDA-20261115-0004', 'Anisa Rahmawati', 'anisa.rahmawati@unesa.ac.id', '081552334404', '081552334404', '3578184511970018', '1997-11-05', 'Universitas Negeri Surabaya (UNESA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 4, '2026-11-15 02:34:27', 'won', NULL, NULL, 0, 'normal', '2026-11-15 04:22:29', '2026-11-15 04:22:29', '2026-11-15 04:31:38', NULL, 'Menunggu penawaran harga rombongan', '2026-11-14 19:34:27', '2026-11-14 21:31:38', NULL),
(19, 'LDA-20261115-0005', 'Galang Fajar Kurnia', 'galang.fajar@maspion.com', '081552334405', '081552334405', '3578191107930019', '1993-07-11', 'Maspion Group Indonesia', NULL, 'Sidoarjo', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-15 02:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta presentasi online dari tim sales', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL),
(20, 'LDA-20261115-0006', 'Putri Ayu Maharani', 'putri.ayu@ubaya.ac.id', '081552334406', '081552334406', '3578205409980020', '1998-09-14', 'Universitas Surabaya (UBAYA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-15 02:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Follow up jadwal pendaftaran batch berikutnya', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL),
(21, 'LDA-20261115-0007', 'Yudha Pratama Wicaksono', 'yudha.pratama@pakuwon.com', '081552334407', '081552334407', '3578213005910021', '1991-05-30', 'PT Pakuwon Jati Tbk', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-15 02:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Belum tentukan program pelatihan yang diambil', '2026-11-14 19:34:27', '2026-11-14 19:34:27', NULL),
(22, 'LDA-20261115-0008', 'huni', 'huni@gmail.com', '085812304685', '085812304685', '3517085909040001', '2008-07-15', 'Universitas Indonesia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-15 07:50:14', '2026-11-15 07:50:14', NULL),
(23, 'LDA-20261115-0009', 'sadam', 'sadam@gmail.com', '085812304685', '085812304685', '3517085909040001', '2026-11-17', 'Universitas Lampung', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-15 08:02:17', '2026-11-15 08:02:17', NULL),
(24, 'LDA-20261115-0010', 'julia', 'julia@gmail.com', '085812304685', '085812304685', '3517085909040001', '2026-11-20', 'Universitas Kartika', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-15 08:03:16', '2026-11-15 08:03:16', NULL),
(25, 'LDA-20261115-0011', 'roman', 'roman@gmail.com', '085812304685', '085812304685', '3517085909040001', '2026-11-04', 'Universitas bangka', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-15 08:15:35', '2026-11-15 08:15:35', NULL),
(26, 'LDA-20261115-0012', 'Dimas Arya Pamungkas', 'dimas.arya@its.ac.id', '081552334401', '081552334401', '3578151908940015', '1994-08-19', 'Institut Teknologi Sepuluh Nopember (ITS)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, 1, 4273500.00, 2, '2026-11-15 15:33:08', 'negotiation', 8, 'Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.', 0, 'normal', '2026-11-16 11:29:41', '2026-11-16 11:29:41', NULL, '2026-11-16 11:29:41', 'Prospek kampus, menanyakan akreditasi pelatihan', '2026-11-15 08:33:08', '2026-11-22 08:13:49', NULL),
(27, 'LDA-20261115-0013', 'Ratna Juwita', 'ratna.juwita@unair.ac.id', '081552334402', '081552334402', '3578166010960016', '1996-10-20', 'Universitas Airlangga (UNAIR)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Penjajakan kerja sama pelatihan staf tendik', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(28, 'LDA-20261115-0014', 'Kevin Sanjaya Putra', 'kevin.sanjaya@wingscorp.com', '081552334403', '081552334403', '3578170203950017', '1995-03-02', 'PT Wings Surya Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Kebutuhan pelatihan corporate sales', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(29, 'LDA-20261115-0015', 'Anisa Rahmawati', 'anisa.rahmawati@unesa.ac.id', '081552334404', '081552334404', '3578184511970018', '1997-11-05', 'Universitas Negeri Surabaya (UNESA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menunggu penawaran harga rombongan', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(30, 'LDA-20261115-0016', 'Galang Fajar Kurnia', 'galang.fajar@maspion.com', '081552334405', '081552334405', '3578191107930019', '1993-07-11', 'Maspion Group Indonesia', NULL, 'Sidoarjo', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta presentasi online dari tim sales', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(31, 'LDA-20261115-0017', 'Putri Ayu Maharani', 'putri.ayu@ubaya.ac.id', '081552334406', '081552334406', '3578205409980020', '1998-09-14', 'Universitas Surabaya (UBAYA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Follow up jadwal pendaftaran batch berikutnya', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(32, 'LDA-20261115-0018', 'Yudha Pratama Wicaksono', 'yudha.pratama@pakuwon.com', '081552334407', '081552334407', '3578213005910021', '1991-05-30', 'PT Pakuwon Jati Tbk', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-15 15:33:08', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Belum tentukan program pelatihan yang diambil', '2026-11-15 08:33:08', '2026-11-15 08:33:08', NULL),
(34, 'LDA-20261122-0001', 'Qothrun Nada S.', 'qothrun2006@gmail.com', '085336326912', '085336326912', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-22 10:23:34', '2026-11-22 10:23:34', NULL),
(35, 'LDA-20261122-0002', 'yaya', NULL, '087888999222', '087888999222', NULL, NULL, 'Universitas Indonesia', NULL, NULL, NULL, NULL, NULL, 2, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-22 10:29:43', '2026-11-22 10:29:43', NULL),
(36, 'LDA-20261122-0003', 'BAMBANG', NULL, '086777888444', '086777888444', NULL, NULL, 'Universitas Gajah Mada', NULL, NULL, NULL, NULL, NULL, 2, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-22 10:31:44', '2026-11-22 10:31:44', NULL),
(37, 'LDA-20261122-0004', 'niki', NULL, '086555444222', '086555444222', NULL, NULL, 'PT Telkomsel', NULL, NULL, NULL, NULL, NULL, 1, NULL, 2, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-22 10:33:54', '2026-11-22 10:33:54', NULL),
(38, 'LDA-20261122-0005', 'Mochammad Rizky Pratama', 'rizky.pratama@jatimprov.go.id', '081230112201', '081230112201', '3578011204920001', '1992-04-12', 'Bappeda Provinsi Jawa Timur', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Prospek instansi pemprov, belum tentukan program minat', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(39, 'LDA-20261122-0006', 'Dwi Lestari Wulandari', 'dwi.lestari@pelindo.co.id', '081230112202', '081230112202', '3578025608940002', '1994-08-16', 'PT Pelabuhan Indonesia (Pelindo)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Kebutuhan in-house training BUMN', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(40, 'LDA-20261122-0007', 'Hendra Gunawan', 'hendra.gunawan@surabaya.go.id', '081230112203', '081230112203', '3578032101900003', '1990-01-21', 'Dinas Komunikasi dan Informatika Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Follow up kebutuhan diklat staf', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(41, 'LDA-20261122-0008', 'Maya Anggraini', 'maya.anggraini@pln.co.id', '081230112204', '081230112204', '3578046709950004', '1995-09-27', 'PT PLN (Persero) UID Jawa Timur', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menunggu penawaran katalog program terbaru', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(42, 'LDA-20261122-0009', 'Bagus Setiawan', 'bagus.setiawan@kemendagri.go.id', '081230112205', '081230112205', '3578051803930005', '1993-03-18', 'Balai Diklat Keagamaan Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Perlu konsultasi silabus materi pelatihan', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(43, 'LDA-20261122-0010', 'Rina Kusuma Dewi', 'rina.kusuma@telkom.co.id', '081230112206', '081230112206', '3578064911960006', '1996-11-09', 'PT Telkom Indonesia Witel Jatim', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'lost', 8, 'Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.', 0, 'normal', '2026-11-23 11:55:26', '2026-11-23 11:55:26', NULL, '2026-11-23 11:55:26', 'Minta dikirimi proposal umum', '2026-11-22 10:34:27', '2026-11-23 04:55:26', NULL),
(44, 'LDA-20261122-0011', 'Aditya Tri Nugroho', 'aditya.tri@pertamina.com', '081230112207', '081230112207', '3578070505910007', '1991-05-05', 'PT Pertamina Patra Niaga Jatimbalinus', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 2, '2026-11-22 17:34:27', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Diskusi lanjutan jadwal ketersediaan kelas', '2026-11-22 10:34:27', '2026-11-22 10:34:27', NULL),
(45, 'LDA-20261122-0012', 'Fajar Ramadhan', 'fajar.ramadhan@bankmandiri.co.id', '081331223301', '081331223301', '3578081402920008', '1992-02-14', 'Bank Mandiri Kantor Cabang Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Eksplorasi pelatihan perbankan', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(46, 'LDA-20261122-0013', 'Nurul Hidayati', 'nurul.hidayati@rsudsoetomo.id', '081331223302', '081331223302', '3578095507950009', '1995-07-15', 'RSUD Dr. Soetomo Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Rencana peningkatan mutu tenaga kesehatan', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(47, 'LDA-20261122-0014', 'Arif Wicaksono', 'arif.wicaksono@bca.co.id', '081331223303', '081331223303', '3578101010890010', '1989-10-10', 'Bank Central Asia (BCA) KCU Darmo', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menunggu konfirmasi anggaran bagian HRD', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(48, 'LDA-20261122-0015', 'Dewi Sartika', 'dewi.sartika@bpjs-kesehatan.go.id', '081331223304', '081331223304', '3578116204970011', '1997-04-22', 'BPJS Kesehatan Cabang Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Kebutuhan pelatihan internal divisi operasional', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(49, 'LDA-20261122-0016', 'Eko Prasetyo Utomo', 'eko.utomo@bankjatim.co.id', '081331223305', '081331223305', '3578120806930012', '1993-06-08', 'PT Bank Pembangunan Daerah Jawa Timur (Bank Jatim)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Koordinasi awal via WhatsApp', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(50, 'LDA-20261122-0017', 'Indah Permatasari', 'indah.permatasari@silooamhospitals.com', '081331223306', '081331223306', '3578134301980013', '1998-01-03', 'Siloam Hospitals Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta info perbandingan program reguler vs private', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(51, 'LDA-20261122-0018', 'Wahyu Tri Hidayat', 'wahyu.hidayat@bni.co.id', '081331223307', '081331223307', '3578142512900014', '1990-12-25', 'PT Bank Negara Indonesia (Persero) Tbk', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 3, '2026-11-22 17:35:40', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Rencana pelatihan kuartal depan', '2026-11-22 10:35:40', '2026-11-22 10:35:40', NULL),
(52, 'LDA-20261122-0019', 'Dimas Arya Pamungkas', 'dimas.arya@its.ac.id', '081552334401', '081552334401', '3578151908940015', '1994-08-19', 'Institut Teknologi Sepuluh Nopember (ITS)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Prospek kampus, menanyakan akreditasi pelatihan', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(53, 'LDA-20261122-0020', 'Ratna Juwita', 'ratna.juwita@unair.ac.id', '081552334402', '081552334402', '3578166010960016', '1996-10-20', 'Universitas Airlangga (UNAIR)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Penjajakan kerja sama pelatihan staf tendik', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(54, 'LDA-20261122-0021', 'Kevin Sanjaya Putra', 'kevin.sanjaya@wingscorp.com', '081552334403', '081552334403', '3578170203950017', '1995-03-02', 'PT Wings Surya Surabaya', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Kebutuhan pelatihan corporate sales', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(55, 'LDA-20261122-0022', 'Anisa Rahmawati', 'anisa.rahmawati@unesa.ac.id', '081552334404', '081552334404', '3578184511970018', '1997-11-05', 'Universitas Negeri Surabaya (UNESA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menunggu penawaran harga rombongan', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(56, 'LDA-20261122-0023', 'Galang Fajar Kurnia', 'galang.fajar@maspion.com', '081552334405', '081552334405', '3578191107930019', '1993-07-11', 'Maspion Group Indonesia', NULL, 'Sidoarjo', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Minta presentasi online dari tim sales', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(57, 'LDA-20261122-0024', 'Putri Ayu Maharani', 'putri.ayu@ubaya.ac.id', '081552334406', '081552334406', '3578205409980020', '1998-09-14', 'Universitas Surabaya (UBAYA)', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Follow up jadwal pendaftaran batch berikutnya', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(58, 'LDA-20261122-0025', 'Yudha Pratama Wicaksono', 'yudha.pratama@pakuwon.com', '081552334407', '081552334407', '3578213005910021', '1991-05-30', 'PT Pakuwon Jati Tbk', NULL, 'Surabaya', 'Jawa Timur', NULL, 2, NULL, NULL, 4, '2026-11-22 17:36:15', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Belum tentukan program pelatihan yang diambil', '2026-11-22 10:36:15', '2026-11-22 10:36:15', NULL),
(59, 'LDA-20261122-0026', 'gilang', NULL, '087888222111', '087888222111', NULL, NULL, 'Institut Teknologi Bandung', NULL, NULL, NULL, NULL, NULL, 1, NULL, 4, NULL, 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, NULL, '2026-11-22 11:14:47', '2026-11-22 11:14:47', NULL),
(60, 'LDA-20261123-0001', 'Nadia Kusuma Putri', 'nadia.kusuma@pelindodatasolusi.co.id', '081234501101', '081234501101', '3578041205960001', '1996-05-12', 'PT Pelindo Data Solusi', 'Staf Administrasi Arsip', 'Surabaya', 'Jawa Timur', NULL, 3, 1, 4273500.00, 2, '2026-11-23 12:00:29', 'won', NULL, NULL, 0, 'normal', NULL, NULL, '2026-11-23 12:01:57', NULL, 'Tertarik ikut batch pelatihan Digital Filing bulan depan', '2026-11-23 05:00:29', '2026-11-23 05:01:57', NULL),
(61, 'LDA-20261123-0002', 'Raka Aditya Pratama', 'raka.aditya@rsuddrsoetomo.go.id', '082145602202', '082145602202', '3578052308930002', '1993-08-23', 'RSUD Dr. Soetomo Surabaya', 'Koordinator Layanan Pasien', 'Surabaya', 'Jawa Timur', NULL, 3, 2, NULL, 2, '2026-11-23 12:00:29', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Ingin mendaftarkan tim front office untuk pelatihan pelayanan prima', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL),
(62, 'LDA-20261123-0003', 'Dewi Kartika Sari', 'dewi.kartika@its.ac.id', '085678903303', '085678903303', '3578061411910003', '1991-11-14', 'Institut Teknologi Sepuluh Nopember', 'Staf Tata Usaha', 'Surabaya', 'Jawa Timur', NULL, 3, 3, NULL, 2, '2026-11-23 12:00:29', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Menanyakan jadwal uji kompetensi BNSP dan rincian biaya', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL),
(63, 'LDA-20261123-0004', 'Fajar Nugroho', 'fajar.nugroho@malangkota.go.id', '081398704404', '081398704404', '3573010902940004', '1994-02-09', 'Dinas Komunikasi dan Informatika Kota Malang', 'Pranata Komputer', 'Malang', 'Jawa Timur', NULL, 3, 1, NULL, 2, '2026-11-23 12:00:29', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Meminta penawaran resmi dan silabus untuk pengajuan anggaran dinas', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL),
(64, 'LDA-20261123-0005', 'Aulia Rahmawati', 'aulia.rahma@bankjatim.co.id', '087712305505', '087712305505', '3515082707970005', '1997-07-27', 'PT Bank Pembangunan Daerah Jawa Timur Tbk', 'Customer Service Officer', 'Sidoarjo', 'Jawa Timur', NULL, 3, 2, NULL, 2, '2026-11-23 12:00:29', 'new', NULL, NULL, 0, 'normal', NULL, NULL, NULL, NULL, 'Siap mengikuti kelas pelayanan prima terdekat', '2026-11-23 05:00:29', '2026-11-23 05:00:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lead_sources`
--

CREATE TABLE `lead_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_sources`
--

INSERT INTO `lead_sources` (`id`, `name`, `label`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'manual', 'Input Manual', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(2, 'import_excel', 'Import Excel', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(3, 'import_csv', 'Import CSV', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(4, 'website', 'Website', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(5, 'whatsapp', 'WhatsApp', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_22_000001_create_products_table', 1),
(5, '2026_10_22_000002_create_lead_sources_table', 1),
(6, '2026_10_22_000003_create_rejection_reasons_table', 1),
(7, '2026_10_22_000004_create_leads_table', 1),
(8, '2026_10_22_000005_create_activities_table', 1),
(9, '2026_10_22_000006_create_follow_ups_table', 1),
(10, '2026_10_22_000007_create_payments_table', 1),
(11, '2026_10_22_000008_create_commissions_table', 1),
(12, '2026_10_22_000009_create_import_tables', 1),
(13, '2026_10_22_000010_create_institutions_table', 1),
(14, '2026_10_22_000011_create_customers_table', 1),
(15, '2026_10_22_000012_create_trainers_table', 1),
(16, '2026_10_22_000013_create_training_schedules_table', 1),
(17, '2026_10_22_000014_create_registrations_table', 1),
(18, '2026_11_02_000001_add_deal_price_to_leads_table', 2),
(19, '2026_11_02_000002_create_sales_reminders_table', 3),
(20, '2026_11_02_000003_add_type_and_commission_to_sales_reminders_table', 4),
(21, '2019_12_14_000001_create_personal_access_tokens_table', 5),
(22, '2026_11_05_000001_add_product_type_to_products_table', 5),
(23, '2026_11_14_000001_add_nik_and_birth_date_to_leads_table', 6),
(24, '2026_11_14_000002_add_nik_and_birth_date_to_customers_table', 7),
(25, '2026_11_14_163030_add_bank_account_to_users_table', 8),
(26, '2026_11_14_170047_add_bank_snapshot_to_commissions_table', 9),
(27, '2026_11_15_000001_add_user_id_to_registrations_table', 10),
(28, '2026_11_22_000001_add_registration_id_to_payments_table', 11),
(29, '2026_11_23_000001_classify_higher_education_institutions', 12),
(30, '2026_11_23_000002_expand_activity_audit_history', 13);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `verification_notes` text DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `registration_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `lead_id`, `product_id`, `verified_by`, `amount`, `payment_method`, `bank_name`, `account_number`, `account_name`, `proof_file`, `payment_date`, `status`, `verification_notes`, `verified_at`, `notes`, `created_at`, `updated_at`, `registration_id`) VALUES
(1, 3, NULL, 1, 1831500.00, NULL, 'BCA', '5420192811', 'Hendra Gunawan', 'payments/to1pp7QQzoAMJxSlW9qkpsWbHxr2bQAo0edZm9UV.jpg', '2026-11-14 00:00:00', 'valid', 'Diverifikasi valid: Transaksi disetujui dan komisi telah dicairkan.', '2026-11-14 14:01:38', 'Transfer pelatihan Rp 1.665.000 + komisi sales 10% (Rp 166.500) = Rp 1.831.500', '2026-11-14 06:39:23', '2026-11-14 07:10:02', NULL),
(10, 2, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Dwi Lestari Wulandari', 'payments/C92JXXoiwc5R4Je0qYB8Tqvg8Hpjn3K1F6aN37Ic.jpg', NULL, 'valid', NULL, '2026-11-14 16:06:24', NULL, '2026-11-14 08:25:06', '2026-11-14 09:06:24', NULL),
(11, 4, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Maya Anggraini', 'payments/nR9LlNPijQJAyaMJVQYBsL8rPg42uUAyluq8y0Dz.avif', NULL, 'valid', NULL, '2026-11-14 16:18:12', NULL, '2026-11-14 09:17:47', '2026-11-14 09:18:12', NULL),
(12, 5, 2, 1, 1831500.00, NULL, 'BCA', NULL, 'Bagus Setiawan', 'payments/c24NiWBOSXN7yJK6IdjNqsfnnUffqFzNgxDX04Sz.jpg', NULL, 'valid', NULL, '2026-11-14 16:56:22', NULL, '2026-11-14 09:56:08', '2026-11-14 09:56:22', NULL),
(13, 8, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Fajar Ramadhan', 'payments/0eRGP6oaKYWUJjMIkyxejzI6Uoso7s7agn1tVR5b.jpg', NULL, 'valid', 'Test verify', '2026-11-14 19:26:33', NULL, '2026-11-14 12:21:18', '2026-11-14 12:26:33', NULL),
(14, 15, 2, 1, 1831500.00, NULL, 'BCA', NULL, 'Dimas Arya Pamungkas', 'payments/j5nESNXbq9sN6Nd5Mq7JuYVhjKwDU6lzcwHpqkXN.jpg', NULL, 'valid', NULL, '2026-11-15 03:44:47', NULL, '2026-11-14 20:34:04', '2026-11-14 20:44:47', NULL),
(15, 18, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Anisa Rahmawati', 'payments/lwivSDPxyqLVYEeFsPm3L7cHM3BidaQ2TDG51VMm.jpg', NULL, 'valid', 'Transfer BCA valid dan sudah masuk rekening Disty Akademi.', '2026-11-15 04:31:38', NULL, '2026-11-14 21:23:27', '2026-11-14 21:31:38', NULL),
(16, 9, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Nurul Hidayati', 'payments/plpU6xvs0elwyykad8EDfblcoq362RhTL81j2ZUA.jpg', NULL, 'valid', NULL, '2026-11-15 15:55:35', NULL, '2026-11-15 08:51:37', '2026-11-15 08:55:35', NULL),
(17, 1, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Dimas Arya Pamungkas', 'payments/LkVlnXCacaaqEjiKQroENzjvmus9wFUA0ufdl7O9.jpg', NULL, 'valid', NULL, '2026-11-16 11:28:11', NULL, '2026-11-16 04:27:57', '2026-11-16 04:28:11', 1),
(19, 4, 3, 1, 976800.00, NULL, 'BCA', NULL, 'Maya Anggraini', NULL, NULL, 'valid', NULL, '2026-11-16 12:13:56', NULL, '2026-11-16 05:12:42', '2026-11-16 05:36:13', NULL),
(20, 15, 1, NULL, 4273500.00, NULL, 'BCA', NULL, 'Dimas Arya Pamungkas', 'payments/wC5bZAR2hzDbO9QLNyW9IFmnx1gbfzCrTyzqQLlj.jpg', NULL, 'pending', NULL, NULL, NULL, '2026-11-22 08:04:16', '2026-11-22 08:04:16', NULL),
(21, 4, 2, NULL, 1831500.00, NULL, 'BCA', NULL, 'Maya Anggraini', 'payments/pq6DAHxtlvopeV7R4QnpEKnpOlzNoHjxdvRrbIWo.jpg', NULL, 'pending', NULL, NULL, NULL, '2026-11-22 09:07:55', '2026-11-22 09:07:55', 14),
(22, 1, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Mochammad Rizky Pratama', 'payments/RmqAKL220r8F85sXs1SZuERVoK5lOJdHN4xzefOX.jpg', NULL, 'valid', NULL, '2026-11-22 18:58:46', NULL, '2026-11-22 09:11:36', '2026-11-22 11:58:46', 13),
(23, 60, 1, 1, 4273500.00, NULL, 'BCA', NULL, 'Nadia Kusuma Putri', 'payments/OkwVX033fuj4TmUL4sCZzeaTo8Lgp8pDLSzRWlfu.jpg', NULL, 'valid', NULL, '2026-11-23 12:01:57', NULL, '2026-11-23 05:01:42', '2026-11-23 05:01:57', 15);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'web-client', 'ef535549b6a2b0780c3a16a008d20609660141dfa057025c689af6921c8fb7b4', '[\"*\"]', '2026-11-09 06:16:06', NULL, '2026-11-09 05:45:12', '2026-11-09 06:16:06'),
(2, 'App\\Models\\User', 1, 'web-client', '7157245d70bf619e6ca479bb448cb50509a0b487f08b63e414e341576f3909a1', '[\"*\"]', '2026-11-09 06:25:52', NULL, '2026-11-09 05:47:03', '2026-11-09 06:25:52'),
(45, 'App\\Models\\User', 1, 'api-client', '460af94ff20c3ffde13c9d25380626618e097ef734df1558420af5e0216ed325', '[\"*\"]', '2026-11-14 10:44:20', NULL, '2026-11-14 10:43:51', '2026-11-14 10:44:20'),
(68, 'App\\Models\\User', 1, 'test', '6a6d58949666f41d4689d29d900a0e30d2b3dce132205515688307b3aa050d8c', '[\"*\"]', '2026-11-14 21:01:35', NULL, '2026-11-14 21:01:34', '2026-11-14 21:01:35'),
(69, 'App\\Models\\User', 2, 'test', '4cf30a076f7b83a315749ed0381df1aa03d8ce0111e9337babe98ce402e1e256', '[\"*\"]', '2026-11-14 21:02:06', NULL, '2026-11-14 21:02:06', '2026-11-14 21:02:06'),
(70, 'App\\Models\\User', 1, 'admin_test', '784aebc0cb2d50de57e18a5d868fb39922486abd60a0d884f660491aa0125f9f', '[\"*\"]', '2026-11-14 21:02:14', NULL, '2026-11-14 21:02:14', '2026-11-14 21:02:14'),
(71, 'App\\Models\\User', 1, 'admin_test', '8f5ea7e6f97a0d6072812b91dab436784e4658164b890173eb43d06b40dda381', '[\"*\"]', '2026-11-14 21:02:20', NULL, '2026-11-14 21:02:20', '2026-11-14 21:02:20'),
(72, 'App\\Models\\User', 1, 'admin_test', '7a5a689debf43b734a63fb6b06415931c0c1c0812d1cf890c55f091bcc09d3e4', '[\"*\"]', '2026-11-14 21:02:33', NULL, '2026-11-14 21:02:33', '2026-11-14 21:02:33'),
(73, 'App\\Models\\User', 2, 'sales_test', '52a975fe75f75e43fcd03a1b55969d777dd7050d62112a8562197801473b03aa', '[\"*\"]', NULL, NULL, '2026-11-14 21:02:33', '2026-11-14 21:02:33'),
(74, 'App\\Models\\User', 1, 'test_1', '8929469f114be2da9c79e8e80b7dd48c116464076cbb832dab2b0de1a517026d', '[\"*\"]', '2026-11-14 21:05:36', NULL, '2026-11-14 21:05:36', '2026-11-14 21:05:36'),
(75, 'App\\Models\\User', 2, 'test_2', '01459f3cf42d164f7c9c3cd382429dc31029863430dfb7569533c32135afc243', '[\"*\"]', NULL, NULL, '2026-11-14 21:05:36', '2026-11-14 21:05:36'),
(76, 'App\\Models\\User', 3, 'test_3', '1fce3bccae43958b14cba779b8db3931f75708155c96aff817de60d8e651a63d', '[\"*\"]', NULL, NULL, '2026-11-14 21:05:37', '2026-11-14 21:05:37'),
(77, 'App\\Models\\User', 4, 'test_4', '308b0e06abe28afbceff717b4a5ad6d34b3822b7e22f9ee2602893d7bf43e7ed', '[\"*\"]', NULL, NULL, '2026-11-14 21:05:37', '2026-11-14 21:05:37'),
(78, 'App\\Models\\User', 4, 'test_leads', 'b6f9bf4bd6df06bcad511d33f5d78a4a9362395937d5790ad81f5b1d6cf66634', '[\"*\"]', '2026-11-14 21:05:42', NULL, '2026-11-14 21:05:42', '2026-11-14 21:05:42'),
(79, 'App\\Models\\User', 2, 'test_integration', 'beb99e94bec8dbc0506f911ef2cf74aaca04617d625f429983db74c48ddd09f5', '[\"*\"]', '2026-11-14 21:15:38', NULL, '2026-11-14 21:15:38', '2026-11-14 21:15:38'),
(80, 'App\\Models\\User', 4, 'test_andi', 'fb66ccbf84cfd6ec9effd74fe3da0517b8bf68872b9809cf24db453f6a2209f3', '[\"*\"]', '2026-11-14 21:28:23', NULL, '2026-11-14 21:28:23', '2026-11-14 21:28:23'),
(81, 'App\\Models\\User', 1, 'test_admin_acc', 'ea533e0a431c2eafd2196d4230f83eaf856b721e9a0e5125d188ace54aad58f0', '[\"*\"]', '2026-11-14 21:28:33', NULL, '2026-11-14 21:28:33', '2026-11-14 21:28:33'),
(87, 'App\\Models\\User', 1, 'api-client', '92f9acf0e28cbdd2d6068d8bdea3e30ee5112891183e4abe1d7ff0742d77f109', '[\"*\"]', '2026-11-14 21:47:37', NULL, '2026-11-14 21:47:34', '2026-11-14 21:47:37'),
(88, 'App\\Models\\User', 1, 'api-client', '9e780d5269384d4cc1cea3ec90b955dd5502ca5b845de184f8e393f6375e0c9f', '[\"*\"]', '2026-11-14 22:18:55', NULL, '2026-11-14 22:13:08', '2026-11-14 22:18:55'),
(89, 'App\\Models\\User', 1, 'disty-vue', '8b4dbe44be22d743e8e1327445c2ce68b6fa8bcbbed2b3d02b1112f084a14f13', '[\"*\"]', '2026-11-15 07:31:47', NULL, '2026-11-14 23:02:14', '2026-11-15 07:31:47'),
(90, 'App\\Models\\User', 1, 'api-client', '667b09e3668adcbd7715d4f31d066dd8873b1d620d726e4e3d1abc2567692e8c', '[\"*\"]', '2026-11-15 08:22:07', NULL, '2026-11-15 08:22:00', '2026-11-15 08:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_type` varchar(30) NOT NULL DEFAULT 'training',
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_hours` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `code`, `description`, `category`, `product_type`, `price`, `duration_hours`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Pelatihan dan Sertifikasi Digital Filing', 'PRD-DF', 'Pelatihan dan Sertifikasi Digital Filing dirancang untuk membekali peserta dengan keterampilan mengelola, menyimpan, dan menata dokumen secara digital secara efektif, aman, dan efisien sesuai kebutuhan instansi dan organisasi modern. Harga sudah termasuk: Pelatihan Digital Filing, Sertifikat Peserta, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer, Pajak.', 'Pelatihan & Sertifikasi', 'bundle', 3885000.00, 24, 1, '2026-10-22 07:18:55', '2026-11-14 01:43:46', NULL),
(2, 'Pelatihan Pelayanan Prima', 'PRD-PP', 'Pelatihan Pelayanan Prima dirancang untuk meningkatkan pengetahuan, keterampilan, dan sikap peserta dalam memberikan pelayanan yang efektif, ramah, profesional, dan berorientasi pada kepuasan pelanggan. Harga sudah termasuk: Pelatihan, Materi Pelatihan, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer.', 'Pelatihan', 'training', 1665000.00, 16, 1, '2026-10-22 07:18:55', '2026-11-14 01:43:46', NULL),
(3, 'Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP', 'BNSP-ES', 'Sertifikasi BNSP untuk Skema Pelayanan Prima (Excellent Service) berdasarkan SKKNI Bidang Administratif Profesional. Fasilitas: Harga sudah termasuk pajak, Harga belum termasuk makan, minum dan tempat, Asesmen oleh Asesor yang kompeten sesuai bidangnya, Materi Uji Kompetensi versi 2023, Sertifikat Kompetensi BNSP (bagi yang telah mengikuti asesmen dan direkomendasikan kompeten).', 'Sertifikasi BNSP', 'certification', 888000.00, 16, 1, '2026-10-22 07:18:55', '2026-11-14 01:43:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `registration_number` varchar(100) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `training_schedule_id` bigint(20) UNSIGNED DEFAULT NULL,
  `training_date` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'registered',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `registration_number`, `customer_id`, `user_id`, `product_id`, `training_schedule_id`, `training_date`, `status`, `amount`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'REG-20261114-0001', 1, NULL, 1, NULL, NULL, 'confirmed', 3885000.00, NULL, '2026-11-14 05:57:44', '2026-11-16 04:28:11'),
(2, 'REG-20261114-0002', 2, NULL, 2, NULL, NULL, 'confirmed', 1831500.00, NULL, '2026-11-14 06:03:59', '2026-11-14 12:19:15'),
(3, 'REG-20261114-0003', 3, NULL, 1, NULL, NULL, 'confirmed', 4273500.00, NULL, '2026-11-14 08:10:53', '2026-11-14 12:19:15'),
(4, 'REG-20261114-0004', 4, NULL, 1, 1, '2026-11-15', 'confirmed', 4273500.00, NULL, '2026-11-14 09:17:17', '2026-11-14 12:19:15'),
(5, 'REG-20261114-0005', 5, NULL, 2, NULL, NULL, 'confirmed', 1831500.00, NULL, '2026-11-14 09:55:52', '2026-11-14 09:56:22'),
(6, 'REG-20261114-0006', 6, NULL, 1, 1, '2026-11-15', 'confirmed', 4273500.00, NULL, '2026-11-14 12:20:57', '2026-11-14 12:26:33'),
(7, 'REG-20261115-0001', 7, 4, 2, NULL, NULL, 'confirmed', 1831500.00, NULL, '2026-11-14 20:33:46', '2026-11-14 20:44:38'),
(8, 'REG-20261115-0002', 8, 4, 1, 1, '2026-11-15', 'confirmed', 4273500.00, NULL, '2026-11-14 21:23:02', '2026-11-14 21:31:38'),
(9, 'REG-20261115-0003', 9, 3, 1, 1, '2026-11-15', 'confirmed', 4273500.00, NULL, '2026-11-15 08:51:06', '2026-11-15 08:55:35'),
(10, 'REG-20261116-0001', 7, 2, 1, 1, '2026-11-15', 'registered', 4273500.00, NULL, '2026-11-16 04:27:33', '2026-11-16 05:06:04'),
(11, 'REG-20261116-0002', 4, 2, 3, NULL, NULL, 'confirmed', 976800.00, NULL, '2026-11-16 04:30:24', '2026-11-16 05:13:56'),
(12, 'REG-20261122-0001', 7, 2, 1, NULL, NULL, 'registered', 4273500.00, NULL, '2026-11-22 08:13:49', '2026-11-22 08:13:49'),
(13, 'REG-20261122-0002', 1, 2, 1, NULL, NULL, 'confirmed', 4273500.00, NULL, '2026-11-22 08:14:18', '2026-11-22 11:58:46'),
(14, 'REG-20261122-0003', 4, 2, 2, NULL, NULL, 'registered', 1831500.00, NULL, '2026-11-22 08:26:24', '2026-11-22 08:26:24'),
(15, 'REG-20261123-0001', 10, 2, 1, 1, '2026-11-15', 'confirmed', 4273500.00, NULL, '2026-11-23 05:01:11', '2026-11-23 05:01:57');

-- --------------------------------------------------------

--
-- Table structure for table `rejection_reasons`
--

CREATE TABLE `rejection_reasons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rejection_reasons`
--

INSERT INTO `rejection_reasons` (`id`, `name`, `label`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'too_expensive', 'Harga Terlalu Mahal', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(2, 'wrong_timing', 'Waktu Pelatihan Tidak Cocok', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(3, 'no_budget', 'Belum Ada Anggaran Kantor', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(4, 'competitor', 'Memilih Vendor Lain', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(5, 'no_response', 'Tidak Merespon Chat/Telp', NULL, 1, '2026-10-22 07:18:55', '2026-10-22 07:18:55'),
(6, 'wrong_number', 'Nomor Salah', NULL, 1, '2026-11-14 00:31:59', '2026-11-14 00:31:59'),
(7, 'unreachable', 'Tidak Dapat Dihubungi', NULL, 1, '2026-11-14 00:31:59', '2026-11-14 00:31:59'),
(8, 'not_willing', 'Tidak Bersedia', NULL, 1, '2026-11-14 00:31:59', '2026-11-14 00:31:59'),
(9, 'not_interested', 'Tidak Tertarik', NULL, 1, '2026-11-14 00:31:59', '2026-11-14 00:31:59');

-- --------------------------------------------------------

--
-- Table structure for table `sales_reminders`
--

CREATE TABLE `sales_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `commission_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admin_id` bigint(20) UNSIGNED NOT NULL,
  `sales_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'follow_up',
  `message` text NOT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'urgent',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sales_reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_reminders`
--

INSERT INTO `sales_reminders` (`id`, `lead_id`, `commission_id`, `admin_id`, `sales_id`, `type`, `message`, `priority`, `is_read`, `read_at`, `is_completed`, `completed_at`, `sales_reply`, `created_at`, `updated_at`) VALUES
(2, 3, 1, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 166.500 untuk prospek Hendra Gunawan (Total transfer customer: Rp 1.831.500) telah berhasil ditransfer pada 14 Nov 2026, 20:41 WIB (Ref Mutasi: 81978000).', 'normal', 1, '2026-11-14 06:55:45', 1, NULL, NULL, '2026-11-14 06:42:04', '2026-11-14 07:10:02'),
(3, 2, 2, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Dwi Lestari Wulandari telah berhasil ditransfer pada 14 Nov 2026, 22:54 WIB.', 'normal', 1, '2026-11-14 08:55:01', 1, NULL, NULL, '2026-11-14 08:54:34', '2026-11-14 08:55:01'),
(4, 4, 3, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Maya Anggraini telah berhasil ditransfer pada 14 Nov 2026, 23:18 WIB (Ref Mutasi: 8889990000).', 'normal', 1, '2026-11-14 09:19:20', 1, NULL, NULL, '2026-11-14 09:19:09', '2026-11-14 09:19:20'),
(5, 5, 4, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 166.500 untuk prospek Bagus Setiawan telah berhasil ditransfer pada 15 Nov 2026, 00:12 WIB.', 'normal', 1, '2026-11-14 11:45:17', 1, NULL, NULL, '2026-11-14 10:12:12', '2026-11-14 11:45:17'),
(6, 8, 5, 1, 3, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Fajar Ramadhan telah berhasil ditransfer pada 15 Nov 2026, 02:21 WIB.', 'normal', 1, '2026-11-14 12:22:03', 1, NULL, NULL, '2026-11-14 12:21:42', '2026-11-14 12:22:03'),
(7, 15, NULL, 1, 4, 'payment_rejected', 'Bukti pembayaran peserta Dimas Arya Pamungkas dinyatakan TIDAK VALID oleh Admin. Alasan: \"bukti palsu\". Silakan hubungi peserta untuk konfirmasi ulang bukti transfer yang sah.', 'urgent', 1, '2026-11-14 20:44:17', 1, '2026-11-14 20:44:38', 'Selesai: Pembayaran terverifikasi valid', '2026-11-14 20:43:07', '2026-11-14 20:44:38'),
(8, 15, NULL, 1, 4, 'payment_rejected', 'Bukti pembayaran peserta Dimas Arya Pamungkas dinyatakan TIDAK VALID oleh Admin. Alasan: \"bukti palsu\". Silakan hubungi peserta untuk konfirmasi ulang bukti transfer yang sah.', 'urgent', 1, '2026-11-14 20:44:16', 1, '2026-11-14 20:44:38', 'Selesai: Pembayaran terverifikasi valid', '2026-11-14 20:43:53', '2026-11-14 20:44:38'),
(9, 15, 7, 1, 4, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 166.500 untuk prospek Dimas Arya Pamungkas telah berhasil ditransfer pada 15 Nov 2026, 10:45 WIB.', 'normal', 1, '2026-11-14 20:45:19', 1, NULL, NULL, '2026-11-14 20:45:02', '2026-11-14 20:45:19'),
(10, 18, 8, 1, 4, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Anisa Rahmawati telah berhasil ditransfer pada 15 Nov 2026, 11:32 WIB.', 'normal', 1, '2026-11-14 21:32:39', 1, NULL, NULL, '2026-11-14 21:32:21', '2026-11-14 21:32:39'),
(11, 9, 9, 1, 3, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Nurul Hidayati telah berhasil ditransfer pada 15 Nov 2026, 22:55 WIB.', 'normal', 1, '2026-11-22 10:35:50', 1, NULL, NULL, '2026-11-15 08:56:07', '2026-11-22 10:35:50'),
(12, 26, NULL, 1, 2, 'follow_up', 'Mohon segera tindak lanjuti prospek \"Dimas Arya Pamungkas\" (WA: 081552334401) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', 'urgent', 1, '2026-11-16 00:07:34', 1, '2026-11-16 04:29:41', 'Respon prospek: Tidak Bersedia. Dapat di-follow up ulang di masa depan.', '2026-11-16 00:07:01', '2026-11-16 04:29:41'),
(13, 1, 10, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Mochammad Rizky Pratama telah berhasil ditransfer pada 16 Nov 2026, 18:28 WIB.', 'normal', 1, '2026-11-16 04:28:39', 1, NULL, NULL, '2026-11-16 04:28:24', '2026-11-16 04:28:39'),
(15, 53, NULL, 1, 4, 'follow_up', 'Mohon segera tindak lanjuti prospek \"Ratna Juwita\" (WA: 081552334402) untuk penawaran program Program Pelatihan. Berikan detail materi dan jadwal pelatihan terdekat.', 'urgent', 0, NULL, 0, NULL, NULL, '2026-11-22 10:40:56', '2026-11-22 10:40:56'),
(16, 1, 15, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Mochammad Rizky Pratama telah berhasil ditransfer pada 23 Nov 2026, 01:59 WIB.', 'normal', 1, '2026-11-23 04:06:08', 1, NULL, NULL, '2026-11-22 11:59:55', '2026-11-23 04:06:08'),
(17, 60, 16, 1, 2, 'commission_paid', 'Selamat! Komisi 10% sebesar Rp 388.500 untuk prospek Nadia Kusuma Putri telah berhasil ditransfer pada 23 Nov 2026, 19:02 WIB.', 'normal', 1, '2026-11-23 05:02:32', 1, NULL, NULL, '2026-11-23 05:02:14', '2026-11-23 05:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Cr37XpYI8iOs0soC35edChitt2Z38F6VwcYLQicr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/158.0.0.0 Safari/537.36 Edg/158.0.0.0', 'eyJfdG9rZW4iOiJLUW1GM202eVhVUDduQnVYVHExY0xWZnJ1dXBUSjVBVlVUaXV5cGIxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sZWFkc1wvdGVtcGxhdGUtZXhjZWwiLCJyb3V0ZSI6ImxlYWRzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1795434998),
('NYn3QfBvd9XiN27tVC8LsfQtx8PSIi0Ej79O2Ijd', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.7977.167 Safari/537.36 Antigravity/2.34.0', 'eyJfdG9rZW4iOiJBSUZzdkpWUWFvMEFDd0xYSkh5SHF4WkFqc2U2WXVsakI0SEVVN0FuIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wYXltZW50c1wvMjIiLCJyb3V0ZSI6InBheW1lbnRzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1795435573);

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`id`, `name`, `email`, `phone`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Drs. Purwanto, M.Si.', 'purwanto@distyakademi.com', '081112220000', 'Trainer profesional resmi Disty Akademi', 1, '2026-10-22 07:18:55', '2026-11-14 02:40:34'),
(2, 'Drs. Mochammad Saleh, M.Si.', 'mochammad.saleh@distyakademi.com', '082224440000', 'Trainer profesional resmi Disty Akademi', 1, '2026-10-22 07:18:55', '2026-11-14 02:40:34'),
(3, 'Bayu Pancoroadi, ST.,MT', 'bayu.pancoroadi@distyakademi.com', '083336660000', 'Trainer profesional resmi Disty Akademi', 1, '2026-10-22 07:18:55', '2026-11-14 02:40:34'),
(4, 'Miftahul Ulum, ST., M.Si.', 'miftahul.ulum@distyakademi.com', '084448880000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(5, 'Mochamad Samsukadi, Lc., M.Th.I', 'mochamad.samsukadi@distyakademi.com', '085561100000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(6, 'Galuh Tisna Widiana, M.Pd.', 'galuh.tisna@distyakademi.com', '086673320000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(7, 'M. Yusuf Wibowo, ST.', 'yusuf.wibowo@distyakademi.com', '087785540000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(8, 'M. Ali Murtadlo, M.Kom.', 'ali.murtadlo@distyakademi.com', '088897760000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(9, 'Chandra Sukma Anugrah, M.Kom.', 'chandra.sukma@distyakademi.com', '081000998000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(10, 'Wiwit Denny Fitriana, M.Si.', 'wiwit.denny@distyakademi.com', '081112220000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(11, 'Dr. Nur Ulwiyah, M.Pd.I', 'nur.ulwiyah@distyakademi.com', '081223442000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(12, 'Dr. Miftakhul Ilmi S. Putra, M.Pd.', 'miftakhul.ilmi@distyakademi.com', '081334664000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(13, 'Dr. Dhikrul Hakim, M.Pd.I', 'dhikrul.hakim@distyakademi.com', '081445886000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(14, 'Dr. Moh. Makmun, M.HI', 'moh.makmun@distyakademi.com', '081557108000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34'),
(15, 'Mahmud Huda, M.S.I', 'mahmud.huda@distyakademi.com', '081668330000', 'Trainer profesional resmi Disty Akademi', 1, '2026-11-14 02:37:58', '2026-11-14 02:40:34');

-- --------------------------------------------------------

--
-- Table structure for table `training_schedules`
--

CREATE TABLE `training_schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `trainer_id` bigint(20) UNSIGNED NOT NULL,
  `training_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `training_schedules`
--

INSERT INTO `training_schedules` (`id`, `product_id`, `trainer_id`, `training_date`, `start_time`, `end_time`, `location`, `capacity`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 4, '2026-11-15', '09:00:00', '10:00:00', 'Disty Academy Hall / Zoom Meeting', 25, 'scheduled', NULL, '2026-11-14 09:16:16', '2026-11-14 09:16:16'),
(2, 2, 7, '2026-11-17', '09:00:00', '16:00:00', 'Disty Academy Hall / Zoom Meeting', 25, 'scheduled', NULL, '2026-11-15 09:53:41', '2026-11-15 09:53:41'),
(3, 3, 6, '2026-11-25', '09:00:00', '16:00:00', 'Zoom Meeting', 30, 'scheduled', NULL, '2026-11-22 11:47:30', '2026-11-22 11:47:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'sales',
  `phone` varchar(50) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_account_name` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `bank_name`, `bank_account_number`, `bank_account_name`, `is_active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin CRM', 'admin@distyakademi.com', NULL, '$2y$12$wkpcsL4KdE0tERPyN9mlMO9P7OQbNHmcy5/QCeDlG5y6kdVsN5EHi', 'admin', NULL, NULL, NULL, NULL, 1, NULL, '2026-10-22 07:18:55', '2026-10-22 07:18:55', NULL),
(2, 'Budi Santoso', 'budi@distyakademi.com', NULL, '$2y$12$2hNf.fKVCcztZQ11oOQPT.KfyO84d1YFpTINv/bZenEr28/4RIp66', 'sales', '081234567890', 'BNI', '883019281', 'Budi Santoso', 1, NULL, '2026-10-22 07:18:55', '2026-11-14 09:55:22', NULL),
(3, 'Sari Dewi', 'sari@distyakademi.com', NULL, '$2y$12$5hk2abY9WnrY12X7FeLatOx/MkuixhLgRy6LbQPH/JstuH4rY5Q.2', 'sales', '081234567891', 'Bank Mandiri', '141002938192', 'Sari Dewi', 1, NULL, '2026-10-22 07:18:55', '2026-10-22 07:18:55', NULL),
(4, 'Andi Wijaya', 'andi@distyakademi.com', NULL, '$2y$12$fuZHUQlL8aovQTPqsSoYeep7hF0tyvuiKo6btS7UjglAyhrs8eFti', 'sales', '081234567892', 'BNI', '0928371928', 'Andi Wijaya', 1, NULL, '2026-10-22 07:18:55', '2026-10-22 07:18:55', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activities_lead_id_index` (`lead_id`),
  ADD KEY `activities_user_id_index` (`user_id`),
  ADD KEY `activities_type_index` (`type`),
  ADD KEY `activities_activity_at_index` (`activity_at`),
  ADD KEY `activities_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `activities_owner_id_index` (`owner_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commissions_payment_id_foreign` (`payment_id`),
  ADD KEY `commissions_user_id_index` (`user_id`),
  ADD KEY `commissions_lead_id_index` (`lead_id`),
  ADD KEY `commissions_status_index` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  ADD KEY `customers_lead_id_foreign` (`lead_id`),
  ADD KEY `customers_name_index` (`name`),
  ADD KEY `customers_email_index` (`email`),
  ADD KEY `customers_phone_index` (`phone`),
  ADD KEY `customers_status_index` (`status`),
  ADD KEY `customers_institution_id_index` (`institution_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `follow_ups_lead_id_index` (`lead_id`),
  ADD KEY `follow_ups_assigned_to_index` (`assigned_to`),
  ADD KEY `follow_ups_follow_up_at_index` (`follow_up_at`),
  ADD KEY `follow_ups_status_index` (`status`);

--
-- Indexes for table `import_batches`
--
ALTER TABLE `import_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `import_batches_status_index` (`status`),
  ADD KEY `import_batches_user_id_index` (`user_id`);

--
-- Indexes for table `import_rows`
--
ALTER TABLE `import_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `import_rows_lead_id_foreign` (`lead_id`),
  ADD KEY `import_rows_import_batch_id_status_index` (`import_batch_id`,`status`);

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institutions_name_index` (`name`),
  ADD KEY `institutions_type_index` (`type`),
  ADD KEY `institutions_city_index` (`city`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leads_lead_code_unique` (`lead_code`),
  ADD KEY `leads_rejection_reason_id_foreign` (`rejection_reason_id`),
  ADD KEY `leads_status_index` (`status`),
  ADD KEY `leads_priority_index` (`priority`),
  ADD KEY `leads_assigned_to_index` (`assigned_to`),
  ADD KEY `leads_lead_source_id_index` (`lead_source_id`),
  ADD KEY `leads_product_id_index` (`product_id`),
  ADD KEY `leads_created_at_index` (`created_at`);

--
-- Indexes for table `lead_sources`
--
ALTER TABLE `lead_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_sources_name_unique` (`name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_product_id_foreign` (`product_id`),
  ADD KEY `payments_verified_by_foreign` (`verified_by`),
  ADD KEY `payments_lead_id_index` (`lead_id`),
  ADD KEY `payments_status_index` (`status`),
  ADD KEY `payments_payment_date_index` (`payment_date`),
  ADD KEY `payments_registration_id_foreign` (`registration_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_code_unique` (`code`),
  ADD KEY `products_category_index` (`category`),
  ADD KEY `products_is_active_index` (`is_active`),
  ADD KEY `products_product_type_index` (`product_type`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registrations_registration_number_unique` (`registration_number`),
  ADD KEY `registrations_customer_id_index` (`customer_id`),
  ADD KEY `registrations_product_id_index` (`product_id`),
  ADD KEY `registrations_training_schedule_id_index` (`training_schedule_id`),
  ADD KEY `registrations_status_index` (`status`),
  ADD KEY `registrations_user_id_foreign` (`user_id`);

--
-- Indexes for table `rejection_reasons`
--
ALTER TABLE `rejection_reasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rejection_reasons_name_unique` (`name`);

--
-- Indexes for table `sales_reminders`
--
ALTER TABLE `sales_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_reminders_admin_id_foreign` (`admin_id`),
  ADD KEY `sales_reminders_sales_id_is_completed_index` (`sales_id`,`is_completed`),
  ADD KEY `sales_reminders_sales_id_is_read_index` (`sales_id`,`is_read`),
  ADD KEY `sales_reminders_lead_id_index` (`lead_id`),
  ADD KEY `sales_reminders_commission_id_foreign` (`commission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainers_name_is_active_index` (`name`,`is_active`);

--
-- Indexes for table `training_schedules`
--
ALTER TABLE `training_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `training_schedules_product_id_training_date_index` (`product_id`,`training_date`),
  ADD KEY `training_schedules_trainer_id_training_date_index` (`trainer_id`,`training_date`),
  ADD KEY `training_schedules_status_index` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_is_active_index` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `follow_ups`
--
ALTER TABLE `follow_ups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `import_batches`
--
ALTER TABLE `import_batches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `import_rows`
--
ALTER TABLE `import_rows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `lead_sources`
--
ALTER TABLE `lead_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rejection_reasons`
--
ALTER TABLE `rejection_reasons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sales_reminders`
--
ALTER TABLE `sales_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `training_schedules`
--
ALTER TABLE `training_schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `commissions_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commissions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `follow_ups`
--
ALTER TABLE `follow_ups`
  ADD CONSTRAINT `follow_ups_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `follow_ups_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `import_batches`
--
ALTER TABLE `import_batches`
  ADD CONSTRAINT `import_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `import_rows`
--
ALTER TABLE `import_rows`
  ADD CONSTRAINT `import_rows_import_batch_id_foreign` FOREIGN KEY (`import_batch_id`) REFERENCES `import_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `import_rows_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_lead_source_id_foreign` FOREIGN KEY (`lead_source_id`) REFERENCES `lead_sources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_rejection_reason_id_foreign` FOREIGN KEY (`rejection_reason_id`) REFERENCES `rejection_reasons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_training_schedule_id_foreign` FOREIGN KEY (`training_schedule_id`) REFERENCES `training_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_reminders`
--
ALTER TABLE `sales_reminders`
  ADD CONSTRAINT `sales_reminders_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_reminders_commission_id_foreign` FOREIGN KEY (`commission_id`) REFERENCES `commissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_reminders_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_reminders_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_schedules`
--
ALTER TABLE `training_schedules`
  ADD CONSTRAINT `training_schedules_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_schedules_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
