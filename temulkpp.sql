-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 01, 2026 at 10:53 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `temulkpp`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `name`, `email`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Agent 1', 'agent.1@temulkpp.com', '$2y$12$cdP8IM7yM1gNqIbdEHBr.eLDim.0jNgNOEnSEjdKTPuOeb0sdrRum', 1, NULL, '2026-04-30 06:37:40', '2026-04-30 06:37:40'),
(2, 'Agent 2', 'agent.2@temulkpp.com', '$2y$12$GpNbuI/6wPilVwMijDYFnudG6YZm5tjlhu4d0HY721eZKbrNw6SnG', 1, NULL, '2026-04-30 06:37:40', '2026-04-30 06:37:40'),
(3, 'Agent 3', 'agent.3@temulkpp.com', '$2y$12$MjfM6DNeiR4kF2Dczw/vPOdZt9o3WavcrknUU0sYrB5tJ6q3c6mzS', 1, NULL, '2026-04-30 06:37:41', '2026-04-30 06:37:41'),
(4, 'Agent 4', 'agent.4@temulkpp.com', '$2y$12$kRDKTq77S0xyqhB.smvuR.cs.Qa1LcBy3f3JMrTRBJFXWr33pCZ0S', 1, NULL, '2026-04-30 06:37:41', '2026-04-30 06:37:41'),
(5, 'Agent 5', 'agent.5@temulkpp.com', '$2y$12$4DOjwuB4isstX28ELpsOXOExGjmvhqHhWNusqT7GzIjB5b1g1PAjm', 1, NULL, '2026-04-30 06:37:41', '2026-04-30 06:37:41'),
(6, 'Agent 6', 'agent.6@temulkpp.com', '$2y$12$.iYdBBsZyqPhMcaijO0e.Oa9QFWQlJl/t2szFKF3TNmwd9zqB26xe', 1, NULL, '2026-04-30 06:37:41', '2026-04-30 06:37:41'),
(7, 'Agent 7', 'agent.7@temulkpp.com', '$2y$12$jca4R7CjOP86irUJnC5Y2uzyzDZ3MNYccFzYAqh8w7g7vd12MFOBW', 1, NULL, '2026-04-30 06:37:42', '2026-04-30 06:37:42'),
(8, 'Abdi Negara', 'abdinegara@temulkpp.com', '$2y$12$.XEH/aaAjCp1BfBcGtXvue9pqyn72oL3XsX..and2DIoIFyEqf5Sq', 0, 'GO9IA8lTQmy9xO5KVcaLj2IG0yDdUJfHs5GH2TW20oSFjMMdLKMWGAZa8drj', '2026-04-30 07:30:42', '2026-04-30 07:31:54');

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
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
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
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `route` varchar(255) NOT NULL,
  `icon` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `route`, `icon`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard Agent', 'agent.dashboard', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20\" /></svg>', 'Agent', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(2, 'Jadwal Layanan', 'agent.jadwal', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M7.5 4.75v2.5m9-2.5v2.5M4.75 9h14.5m-13.5 10.25h12a1.5 1.5 0 0 0 1.5-1.5V8A1.5 1.5 0 0 0 17.75 6.5h-12A1.5 1.5 0 0 0 4.25 8v9.75a1.5 1.5 0 0 0 1.5 1.5Z\" /></svg>', 'Agent', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(3, 'Detail Layanan', 'agent.detail-layanan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M5.75 4.75h12.5A1.5 1.5 0 0 1 19.75 6.25v11.5a1.5 1.5 0 0 1-1.5 1.5H5.75a1.5 1.5 0 0 1-1.5-1.5V6.25a1.5 1.5 0 0 1 1.5-1.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M8 9h8M8 12h8M8 15h5\" /></svg>', 'Agent', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(4, 'Dashboard Pimpinan', 'pimpinan.dashboard', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20\" /></svg>', 'Pimpinan', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(5, 'Rekap Layanan Selesai', 'pimpinan.layanan-selesai', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M4.5 12.75 9 17.25l10.5-10.5\" /></svg>', 'Pimpinan', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(6, 'Monitoring Layanan Berjalan', 'pimpinan.layanan-berjalan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M6 6l12 12M18 6 6 18\" /></svg>', 'Pimpinan', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(7, 'Performa Agent', 'pimpinan.performa-agent', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M4 19v-2a4 4 0 0 1 4-4h1.5\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M20 19v-2a4 4 0 0 0-4-4h-1.5\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M8.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z\" /></svg>', 'Pimpinan', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(8, 'Laporan Cetak', 'pimpinan.laporan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M6.75 5.75h10.5a1.5 1.5 0 0 1 1.5 1.5v9.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-9.5a1.5 1.5 0 0 1 1.5-1.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M8.75 9.25h6.5M8.75 12h6.5M8.75 14.75h4\" /></svg>', 'Pimpinan', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(9, 'Dashboard Superadmin', 'superadmin.dashboard', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(10, 'Perizinan Menu', 'superadmin.permissions', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(11, 'Master Data Agent', 'superadmin.master-agent', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(12, 'Master Data Pimpinan', 'superadmin.master-pimpinan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632.02-.219.037-.441.037-.666 0-.01 0-.02.001-.031a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198a9.094 9.094 0 0 1-3.741-.479M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(13, 'Rekap Layanan Selesai (SA)', 'superadmin.layanan-selesai', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M4.5 12.75 9 17.25l10.5-10.5\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(14, 'Monitoring Layanan Berjalan (SA)', 'superadmin.layanan-berjalan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M6 6l12 12M18 6 6 18\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(15, 'Performa Agent (SA)', 'superadmin.performa-agent', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M4 19v-2a4 4 0 0 1 4-4h1.5\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M20 19v-2a4 4 0 0 0-4-4h-1.5\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M8.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(16, 'Laporan Cetak (SA)', 'superadmin.laporan', '<svg class=\"h-5 w-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M6.75 5.75h10.5a1.5 1.5 0 0 1 1.5 1.5v9.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-9.5a1.5 1.5 0 0 1 1.5-1.5Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.8\" d=\"M8.75 9.25h6.5M8.75 12h6.5M8.75 14.75h4\" /></svg>', 'Superadmin', '2026-04-30 07:37:58', '2026-04-30 07:37:58');

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
(4, '2026_04_07_035126_create_reservations_table', 1),
(5, '2026_04_07_120000_add_front_desk_fields_to_reservations_table', 1),
(6, '2026_04_12_000001_update_reservation_profile_fields', 1),
(7, '2026_04_13_103616_create_agents_table', 1),
(8, '2026_04_13_103616_create_pimpinans_table', 1),
(9, '2026_04_13_103617_add_meeting_fields_to_reservations_table', 1),
(10, '2026_04_17_000002_expand_jenis_layanan_length_on_reservations', 1),
(11, '2026_04_17_000003_expand_jenis_kebutuhan_length_on_reservations', 1),
(12, '2026_04_17_000004_drop_jenis_kebutuhan_from_reservations', 1),
(13, '2026_04_17_000005_drop_deskripsi_keluhan_from_reservations', 1),
(14, '2026_04_30_134155_create_superadmins_table', 2),
(15, '2026_04_30_142639_add_is_active_to_agents_table', 3),
(16, '2026_04_30_143303_add_is_active_to_pimpinans_table', 4),
(17, '2026_04_30_143635_create_menus_table', 5),
(18, '2026_04_30_143642_create_role_menu_table', 5);

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
-- Table structure for table `pimpinans`
--

CREATE TABLE `pimpinans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pimpinans`
--

INSERT INTO `pimpinans` (`id`, `name`, `email`, `password`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Pimpinan 1', 'pimpinan.1@temulkpp.com', '$2y$12$hCqR2oe0LETQB5AAc/CeOecc4ehXK6XiB2vuKOocKPkbgV6ze7FJO', 1, '04NWWZRWeeG2R120Xmp94fUWS8H10gLSp3AFS9gqkNVBu4idPRO34VB8D8C2', '2026-04-30 06:37:42', '2026-04-30 06:37:42'),
(2, 'Pimpinan 2', 'pimpinan.2@temulkpp.com', '$2y$12$Gvut7mOFZr6jCGm3Hx.z5.exWzJx61Ucvv5sqCivbEt8hAWiqi.n6', 1, NULL, '2026-04-30 06:37:42', '2026-04-30 06:37:42'),
(3, 'Pimpinan 3', 'pimpinan.3@temulkpp.com', '$2y$12$BvI9aKHiYpDeKFHmw.8viO8FqVDnZSgGiy1QDbnjO3ou4ASuFTh4W', 1, NULL, '2026-04-30 06:37:42', '2026-04-30 06:37:42'),
(4, 'Andi', 'admin@temulkpp.com', '$2y$12$.18hd3piUBCjvG.hYNo0hey/A.N1w3.ncYH7qoev6qQQUVCx90wG2', 0, NULL, '2026-04-30 07:34:57', '2026-04-30 07:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nama_lengkap` varchar(50) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `asal_pt` varchar(100) NOT NULL,
  `jenis_layanan` varchar(100) DEFAULT NULL,
  `detail_keperluan` text NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `tanggal_jam` datetime NOT NULL,
  `kode_reservasi` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `checked_in_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `waktu_mulai_tatap_muka` timestamp NULL DEFAULT NULL,
  `waktu_selesai_tatap_muka` timestamp NULL DEFAULT NULL,
  `catatan_tatap_muka` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `nama_lengkap`, `jabatan`, `asal_pt`, `jenis_layanan`, `detail_keperluan`, `lampiran`, `tanggal_jam`, `kode_reservasi`, `status`, `checked_in_at`, `created_at`, `updated_at`, `agent_id`, `waktu_mulai_tatap_muka`, `waktu_selesai_tatap_muka`, `catatan_tatap_muka`) VALUES
(1, 'Rafi Rizqallah Andila', 'Wakil Ketua', 'PT Millenio Amerta Data', 'Konsultasi Regulasi Pengadaan', 'test', NULL, '2026-05-04 08:00:00', 'RES-20260430-ZQTW', 'pending', NULL, '2026-04-30 09:20:31', '2026-04-30 09:20:31', 5, NULL, NULL, NULL),
(2, 'Rafi Rizqallah Andila', 'Wakil Ketua', 'PT Millenio Amerta Data', 'Konsultasi Regulasi Pengadaan', 'test', NULL, '2026-05-05 15:40:00', 'RES-20260430-71KS', 'pending', NULL, '2026-04-30 09:42:55', '2026-04-30 09:42:55', 1, NULL, NULL, NULL),
(3, 'Tamu 1', 'Staff', 'Pemkot Jakarta', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-28 12:26:48', 'RSV-P90FH9', 'checked_in_front_desk', '2026-04-28 05:22:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(4, 'Tamu 2', 'Staff', 'UGM', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-05-01 11:59:48', 'RSV-6VZI3M', 'in_progress', '2026-05-01 05:03:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-05-01 05:10:48', NULL, NULL),
(5, 'Tamu 3', 'Staff', 'UGM', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-30 16:17:48', 'RSV-MZQGXP', 'completed', '2026-04-30 09:18:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-30 09:30:48', '2026-04-30 10:18:48', NULL),
(6, 'Tamu 4', 'Staff', 'ITB', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-24 12:10:48', 'RSV-IVSLIV', 'completed', '2026-04-24 05:08:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-24 05:16:48', '2026-04-24 05:34:48', NULL),
(7, 'Tamu 5', 'Staff', 'Pemkot Jakarta', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-24 16:55:48', 'RSV-ZIVE1R', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(8, 'Tamu 6', 'Staff', 'ITB', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-27 12:23:48', 'RSV-AOI1CP', 'checked_in_front_desk', '2026-04-27 05:27:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(9, 'Tamu 7', 'Staff', 'PT Maju Bersama', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-28 08:03:48', 'RSV-FEKDAL', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(10, 'Tamu 8', 'Staff', 'Universitas Indonesia', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-26 08:18:48', 'RSV-XCBQSO', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(11, 'Tamu 9', 'Staff', 'Universitas Indonesia', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-30 16:42:48', 'RSV-TS87HR', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(12, 'Tamu 10', 'Staff', 'Universitas Indonesia', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-29 12:43:48', 'RSV-KAAXIH', 'checked_in_front_desk', '2026-04-29 05:40:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(13, 'Tamu 11', 'Staff', 'Pemkot Jakarta', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-23 14:38:48', 'RSV-CFSDTF', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(14, 'Tamu 12', 'Staff', 'UGM', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-25 08:40:48', 'RSV-3FIEMT', 'in_progress', '2026-04-25 01:38:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-25 01:53:48', NULL, NULL),
(15, 'Tamu 13', 'Staff', 'Universitas Indonesia', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-29 16:14:48', 'RSV-WS4TSO', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(16, 'Tamu 14', 'Staff', 'UGM', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-29 12:45:48', 'RSV-TM7JQM', 'in_progress', '2026-04-29 05:40:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-29 05:54:48', NULL, NULL),
(17, 'Tamu 15', 'Staff', 'ITB', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-21 13:00:48', 'RSV-YVPHUG', 'completed', '2026-04-21 06:05:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-21 06:05:48', '2026-04-21 06:46:48', NULL),
(18, 'Tamu 16', 'Staff', 'Pemkot Jakarta', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-22 15:11:48', 'RSV-GQZ0XK', 'completed', '2026-04-22 08:14:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-22 08:26:48', '2026-04-22 08:50:48', NULL),
(19, 'Tamu 17', 'Staff', 'PT Maju Bersama', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-24 14:00:48', 'RSV-WJ0AIG', 'completed', '2026-04-24 07:05:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-24 07:05:48', '2026-04-24 08:03:48', NULL),
(20, 'Tamu 18', 'Staff', 'UGM', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-25 08:56:48', 'RSV-NVMNH3', 'checked_in_front_desk', '2026-04-25 01:52:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(21, 'Tamu 19', 'Staff', 'Universitas Indonesia', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-24 08:43:48', 'RSV-DQ2MAR', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(22, 'Tamu 20', 'Staff', 'Pemkot Jakarta', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-26 12:08:48', 'RSV-4JW536', 'checked_in_front_desk', '2026-04-26 05:08:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(23, 'Tamu 21', 'Staff', 'PT Maju Bersama', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-05-01 15:11:48', 'RSV-EODP8M', 'completed', '2026-05-01 08:11:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-05-01 08:23:48', '2026-05-01 09:18:48', NULL),
(24, 'Tamu 22', 'Staff', 'ITB', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-21 16:22:48', 'RSV-EGJ7NY', 'in_progress', '2026-04-21 09:25:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-21 09:27:48', NULL, NULL),
(25, 'Tamu 23', 'Staff', 'ITB', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-21 14:07:48', 'RSV-NEGC1D', 'in_progress', '2026-04-21 07:09:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-21 07:18:48', NULL, NULL),
(26, 'Tamu 24', 'Staff', 'ITB', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-23 09:36:48', 'RSV-UI2EGA', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(27, 'Tamu 25', 'Staff', 'UGM', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-28 14:49:48', 'RSV-US8ARH', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(28, 'Tamu 26', 'Staff', 'PT Maju Bersama', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-29 15:59:48', 'RSV-UWFMP8', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(29, 'Tamu 27', 'Staff', 'PT Maju Bersama', 'Non SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-27 11:06:48', 'RSV-NUPRZQ', 'completed', '2026-04-27 04:02:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-27 04:11:48', '2026-04-27 05:07:48', NULL),
(30, 'Tamu 28', 'Staff', 'PT Maju Bersama', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-25 11:56:48', 'RSV-MWLV0O', 'pending', NULL, '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL),
(31, 'Tamu 29', 'Staff', 'UGM', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-22 14:40:48', 'RSV-WWMRDH', 'in_progress', '2026-04-22 07:44:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, '2026-04-22 07:55:48', NULL, NULL),
(32, 'Tamu 30', 'Staff', 'Universitas Indonesia', 'SPSE', 'Konsultasi teknis pengadaan', NULL, '2026-04-29 16:12:48', 'RSV-VVAA9C', 'checked_in_front_desk', '2026-04-29 09:15:48', '2026-05-01 03:09:48', '2026-05-01 03:09:48', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_menu`
--

CREATE TABLE `role_menu` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role` varchar(255) NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_menu`
--

INSERT INTO `role_menu` (`id`, `role`, `menu_id`, `created_at`, `updated_at`) VALUES
(1, 'agent', 1, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(2, 'agent', 2, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(3, 'agent', 3, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(4, 'pimpinan', 4, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(5, 'pimpinan', 5, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(6, 'pimpinan', 6, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(7, 'pimpinan', 7, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(8, 'pimpinan', 8, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(9, 'superadmin', 9, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(10, 'superadmin', 10, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(11, 'superadmin', 11, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(12, 'superadmin', 12, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(13, 'superadmin', 13, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(14, 'superadmin', 14, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(15, 'superadmin', 15, '2026-04-30 07:37:58', '2026-04-30 07:37:58'),
(16, 'superadmin', 16, '2026-04-30 07:37:58', '2026-04-30 07:37:58');

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
('OcmCvSBrAmS1ONBO0QASQO4xSj13mhvjuxa6TTu5', 1, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'eyJfdG9rZW4iOiJrSWFuTjVHNVhjd29Hb3hpSW52Wm1IQ3JYQXhQcjFXQW1NMThDT3loIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3N1cGVyc2V0XC9ndWVzdC10b2tlbj9kYXNoYm9hcmRJZD1kYmQ4ODY3My1iNzA1LTQwY2MtOGZhMC1iOGI4YTI2ZTlhZmQiLCJyb3V0ZSI6InN1cGVyc2V0Lmd1ZXN0LXRva2VuIn0sImxvZ2luX2FnZW50XzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9', 1777625443);

-- --------------------------------------------------------

--
-- Table structure for table `superadmins`
--

CREATE TABLE `superadmins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `superadmins`
--

INSERT INTO `superadmins` (`id`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Superadmin', 'superadmin@temulkpp.com', '$2y$12$WL9s9/10QilWOsY4v4aNN.4z.NsQLSY9JriUb5N9qbOdqk.QGOAG6', '2xvKJdiLcdWI62Z1RUd72J4QmkR8fWgbWJIovT5c1uKxDDbFaehGLDhEMAX9', '2026-04-30 06:57:04', '2026-04-30 06:57:04');

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
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `agents_email_unique` (`email`);

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `pimpinans`
--
ALTER TABLE `pimpinans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pimpinans_email_unique` (`email`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reservations_kode_reservasi_unique` (`kode_reservasi`),
  ADD KEY `reservations_agent_id_foreign` (`agent_id`);

--
-- Indexes for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_menu_menu_id_foreign` (`menu_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `superadmins`
--
ALTER TABLE `superadmins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `superadmins_email_unique` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pimpinans`
--
ALTER TABLE `pimpinans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `role_menu`
--
ALTER TABLE `role_menu`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `superadmins`
--
ALTER TABLE `superadmins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_menu`
--
ALTER TABLE `role_menu`
  ADD CONSTRAINT `role_menu_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
