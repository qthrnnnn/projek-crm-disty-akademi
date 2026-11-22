-- CRM Disty Akademi. Hanya users, products, dan trainers berisi data.
-- Impor ke database baru atau kosong. Catatan migrations sengaja kosong.
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: crm_9
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activities`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `activity_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_lead_id_index` (`lead_id`),
  KEY `activities_user_id_index` (`user_id`),
  KEY `activities_type_index` (`type`),
  KEY `activities_activity_at_index` (`activity_at`),
  CONSTRAINT `activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `commissions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lead_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commissions_payment_id_foreign` (`payment_id`),
  KEY `commissions_user_id_index` (`user_id`),
  KEY `commissions_lead_id_index` (`lead_id`),
  KEY `commissions_status_index` (`status`),
  CONSTRAINT `commissions_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commissions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `institution_id` bigint(20) unsigned DEFAULT NULL,
  `lead_id` bigint(20) unsigned DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'active',
  `source` varchar(50) NOT NULL DEFAULT 'manual',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_customer_code_unique` (`customer_code`),
  KEY `customers_lead_id_foreign` (`lead_id`),
  KEY `customers_name_index` (`name`),
  KEY `customers_email_index` (`email`),
  KEY `customers_phone_index` (`phone`),
  KEY `customers_status_index` (`status`),
  KEY `customers_institution_id_index` (`institution_id`),
  CONSTRAINT `customers_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `follow_ups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `follow_ups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `follow_up_at` datetime NOT NULL,
  `method` varchar(30) NOT NULL DEFAULT 'whatsapp',
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `result_notes` text DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follow_ups_lead_id_index` (`lead_id`),
  KEY `follow_ups_assigned_to_index` (`assigned_to`),
  KEY `follow_ups_follow_up_at_index` (`follow_up_at`),
  KEY `follow_ups_status_index` (`status`),
  CONSTRAINT `follow_ups_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_ups_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `import_batches`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `total_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `success_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `failed_rows` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_batches_status_index` (`status`),
  KEY `import_batches_user_id_index` (`user_id`),
  CONSTRAINT `import_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `import_rows`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_rows` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `import_batch_id` bigint(20) unsigned NOT NULL,
  `row_number` int(10) unsigned NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`errors`)),
  `lead_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_rows_lead_id_foreign` (`lead_id`),
  KEY `import_rows_import_batch_id_status_index` (`import_batch_id`,`status`),
  CONSTRAINT `import_rows_import_batch_id_foreign` FOREIGN KEY (`import_batch_id`) REFERENCES `import_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `import_rows_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institutions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `institutions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `institutions_name_index` (`name`),
  KEY `institutions_type_index` (`type`),
  KEY `institutions_city_index` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lead_sources`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lead_sources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lead_sources_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leads`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `lead_source_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `deal_price` decimal(15,2) DEFAULT NULL COMMENT 'Harga kesepakatan negosiasi sales dengan pelanggan',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `rejection_reason_id` bigint(20) unsigned DEFAULT NULL,
  `rejection_notes` text DEFAULT NULL,
  `lead_score` int(10) unsigned NOT NULL DEFAULT 0,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `first_contacted_at` datetime DEFAULT NULL,
  `last_contacted_at` datetime DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `lost_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leads_lead_code_unique` (`lead_code`),
  KEY `leads_rejection_reason_id_foreign` (`rejection_reason_id`),
  KEY `leads_status_index` (`status`),
  KEY `leads_priority_index` (`priority`),
  KEY `leads_assigned_to_index` (`assigned_to`),
  KEY `leads_lead_source_id_index` (`lead_source_id`),
  KEY `leads_product_id_index` (`product_id`),
  KEY `leads_created_at_index` (`created_at`),
  CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_lead_source_id_foreign` FOREIGN KEY (`lead_source_id`) REFERENCES `lead_sources` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_rejection_reason_id_foreign` FOREIGN KEY (`rejection_reason_id`) REFERENCES `rejection_reasons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
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
  `registration_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_product_id_foreign` (`product_id`),
  KEY `payments_verified_by_foreign` (`verified_by`),
  KEY `payments_lead_id_index` (`lead_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_payment_date_index` (`payment_date`),
  KEY `payments_registration_id_foreign` (`registration_id`),
  CONSTRAINT `payments_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_registration_id_foreign` FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `products`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `product_type` varchar(30) NOT NULL DEFAULT 'training',
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_hours` int(10) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_code_unique` (`code`),
  KEY `products_category_index` (`category`),
  KEY `products_is_active_index` (`is_active`),
  KEY `products_product_type_index` (`product_type`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Pelatihan dan Sertifikasi Digital Filing','PRD-DF','Pelatihan dan Sertifikasi Digital Filing dirancang untuk membekali peserta dengan keterampilan mengelola, menyimpan, dan menata dokumen secara digital secara efektif, aman, dan efisien sesuai kebutuhan instansi dan organisasi modern. Harga sudah termasuk: Pelatihan Digital Filing, Sertifikat Peserta, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer, Pajak.','Pelatihan & Sertifikasi','bundle',3885000.00,24,1,'2026-10-22 07:18:55','2026-11-14 01:43:46',NULL),(2,'Pelatihan Pelayanan Prima','PRD-PP','Pelatihan Pelayanan Prima dirancang untuk meningkatkan pengetahuan, keterampilan, dan sikap peserta dalam memberikan pelayanan yang efektif, ramah, profesional, dan berorientasi pada kepuasan pelanggan. Harga sudah termasuk: Pelatihan, Materi Pelatihan, Konsumsi Peserta, Transportasi Trainer, Akomodasi Trainer.','Pelatihan','training',1665000.00,16,1,'2026-10-22 07:18:55','2026-11-14 01:43:46',NULL),(3,'Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP','BNSP-ES','Sertifikasi BNSP untuk Skema Pelayanan Prima (Excellent Service) berdasarkan SKKNI Bidang Administratif Profesional. Fasilitas: Harga sudah termasuk pajak, Harga belum termasuk makan, minum dan tempat, Asesmen oleh Asesor yang kompeten sesuai bidangnya, Materi Uji Kompetensi versi 2023, Sertifikat Kompetensi BNSP (bagi yang telah mengikuti asesmen dan direkomendasikan kompeten).','Sertifikasi BNSP','certification',888000.00,16,1,'2026-10-22 07:18:55','2026-11-14 01:43:46',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

--
-- Table structure for table `registrations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `registration_number` varchar(100) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `training_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `training_date` date DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'registered',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registrations_registration_number_unique` (`registration_number`),
  KEY `registrations_customer_id_index` (`customer_id`),
  KEY `registrations_product_id_index` (`product_id`),
  KEY `registrations_training_schedule_id_index` (`training_schedule_id`),
  KEY `registrations_status_index` (`status`),
  KEY `registrations_user_id_foreign` (`user_id`),
  CONSTRAINT `registrations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `registrations_training_schedule_id_foreign` FOREIGN KEY (`training_schedule_id`) REFERENCES `training_schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `registrations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rejection_reasons`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rejection_reasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rejection_reasons_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sales_reminders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint(20) unsigned NOT NULL,
  `commission_id` bigint(20) unsigned DEFAULT NULL,
  `admin_id` bigint(20) unsigned NOT NULL,
  `sales_id` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'follow_up',
  `message` text NOT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'urgent',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `sales_reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_reminders_admin_id_foreign` (`admin_id`),
  KEY `sales_reminders_sales_id_is_completed_index` (`sales_id`,`is_completed`),
  KEY `sales_reminders_sales_id_is_read_index` (`sales_id`,`is_read`),
  KEY `sales_reminders_lead_id_index` (`lead_id`),
  KEY `sales_reminders_commission_id_foreign` (`commission_id`),
  CONSTRAINT `sales_reminders_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_reminders_commission_id_foreign` FOREIGN KEY (`commission_id`) REFERENCES `commissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_reminders_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_reminders_sales_id_foreign` FOREIGN KEY (`sales_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trainers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trainers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainers_name_is_active_index` (`name`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainers`
--

/*!40000 ALTER TABLE `trainers` DISABLE KEYS */;
INSERT INTO `trainers` VALUES (1,'Drs. Purwanto, M.Si.','purwanto@distyakademi.com','081112220000','Trainer profesional resmi Disty Akademi',1,'2026-10-22 07:18:55','2026-11-14 02:40:34'),(2,'Drs. Mochammad Saleh, M.Si.','mochammad.saleh@distyakademi.com','082224440000','Trainer profesional resmi Disty Akademi',1,'2026-10-22 07:18:55','2026-11-14 02:40:34'),(3,'Bayu Pancoroadi, ST.,MT','bayu.pancoroadi@distyakademi.com','083336660000','Trainer profesional resmi Disty Akademi',1,'2026-10-22 07:18:55','2026-11-14 02:40:34'),(4,'Miftahul Ulum, ST., M.Si.','miftahul.ulum@distyakademi.com','084448880000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(5,'Mochamad Samsukadi, Lc., M.Th.I','mochamad.samsukadi@distyakademi.com','085561100000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(6,'Galuh Tisna Widiana, M.Pd.','galuh.tisna@distyakademi.com','086673320000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(7,'M. Yusuf Wibowo, ST.','yusuf.wibowo@distyakademi.com','087785540000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(8,'M. Ali Murtadlo, M.Kom.','ali.murtadlo@distyakademi.com','088897760000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(9,'Chandra Sukma Anugrah, M.Kom.','chandra.sukma@distyakademi.com','081000998000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(10,'Wiwit Denny Fitriana, M.Si.','wiwit.denny@distyakademi.com','081112220000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(11,'Dr. Nur Ulwiyah, M.Pd.I','nur.ulwiyah@distyakademi.com','081223442000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(12,'Dr. Miftakhul Ilmi S. Putra, M.Pd.','miftakhul.ilmi@distyakademi.com','081334664000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(13,'Dr. Dhikrul Hakim, M.Pd.I','dhikrul.hakim@distyakademi.com','081445886000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(14,'Dr. Moh. Makmun, M.HI','moh.makmun@distyakademi.com','081557108000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34'),(15,'Mahmud Huda, M.S.I','mahmud.huda@distyakademi.com','081668330000','Trainer profesional resmi Disty Akademi',1,'2026-11-14 02:37:58','2026-11-14 02:40:34');
/*!40000 ALTER TABLE `trainers` ENABLE KEYS */;

--
-- Table structure for table `training_schedules`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `training_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `trainer_id` bigint(20) unsigned NOT NULL,
  `training_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 20,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `training_schedules_product_id_training_date_index` (`product_id`,`training_date`),
  KEY `training_schedules_trainer_id_training_date_index` (`trainer_id`,`training_date`),
  KEY `training_schedules_status_index` (`status`),
  CONSTRAINT `training_schedules_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `training_schedules_trainer_id_foreign` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin CRM','admin@distyakademi.com',NULL,'$2y$12$wkpcsL4KdE0tERPyN9mlMO9P7OQbNHmcy5/QCeDlG5y6kdVsN5EHi','admin',NULL,NULL,NULL,NULL,1,NULL,'2026-10-22 07:18:55','2026-10-22 07:18:55',NULL),(2,'Budi Santoso','budi@distyakademi.com',NULL,'$2y$12$2hNf.fKVCcztZQ11oOQPT.KfyO84d1YFpTINv/bZenEr28/4RIp66','sales','081234567890','BNI','883019281','Budi Santoso',1,NULL,'2026-10-22 07:18:55','2026-11-14 09:55:22',NULL),(3,'Sari Dewi','sari@distyakademi.com',NULL,'$2y$12$5hk2abY9WnrY12X7FeLatOx/MkuixhLgRy6LbQPH/JstuH4rY5Q.2','sales','081234567891','Bank Mandiri','141002938192','Sari Dewi',1,NULL,'2026-10-22 07:18:55','2026-10-22 07:18:55',NULL),(4,'Andi Wijaya','andi@distyakademi.com',NULL,'$2y$12$fuZHUQlL8aovQTPqsSoYeep7hF0tyvuiKo6btS7UjglAyhrs8eFti','sales','081234567892','BNI','0928371928','Andi Wijaya',1,NULL,'2026-10-22 07:18:55','2026-10-22 07:18:55',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-22 22:29:45
