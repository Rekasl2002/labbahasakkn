-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 01 Feb 2026 pada 09.54
-- Versi server: 8.0.30
-- Versi PHP: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `labbahasa`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$12$ky6N3ErafWlqDwaRSKDEWuJWaX1DgRfIEar8DeyugAJk3/sbbh5nW', '2026-02-01 02:00:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `audience` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all',
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `payload_json` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `events`
--

INSERT INTO `events` (`id`, `session_id`, `audience`, `type`, `payload_json`, `created_at`) VALUES
(1, 1, 'all', 'session_started', '{\"session_id\":1,\"name\":\"Test\",\"started_at\":\"2026-02-01 02:01:41\"}', '2026-02-01 02:01:41'),
(2, 1, 'all', 'message_sent', '{\"message_id\":1,\"sender_type\":\"admin\",\"sender_participant_id\":null,\"target_type\":\"public\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-01 02:02:43\"}', '2026-02-01 02:02:43'),
(3, 1, 'all', 'mic_all_changed', '{\"mic_on\":0}', '2026-02-01 02:04:15'),
(4, 1, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"Test\"}', '2026-02-01 02:04:22'),
(5, 1, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"Test\"}', '2026-02-01 02:04:24'),
(6, 1, 'all', 'participant_joined', '{\"participant_id\":1,\"student_name\":\"Test Siswa1\",\"class_name\":\"10 A\",\"device_label\":\"\",\"ip_address\":\"192.168.101.5\",\"mic_on\":0,\"speaker_on\":1}', '2026-02-01 02:11:14'),
(7, 1, 'all', 'speaker_changed', '{\"participant_id\":1,\"speaker_on\":0,\"forced_by_admin\":true}', '2026-02-01 02:11:48'),
(8, 1, 'all', 'mic_changed', '{\"participant_id\":1,\"mic_on\":1,\"forced_by_admin\":true}', '2026-02-01 02:11:49'),
(9, 1, 'all', 'mic_changed', '{\"participant_id\":1,\"mic_on\":0,\"forced_by_admin\":true}', '2026-02-01 02:11:50'),
(10, 1, 'all', 'mic_all_changed', '{\"mic_on\":1}', '2026-02-01 02:12:30'),
(11, 1, 'all', 'mic_all_changed', '{\"mic_on\":0}', '2026-02-01 02:12:31'),
(12, 1, 'all', 'mic_all_changed', '{\"mic_on\":1}', '2026-02-01 02:12:34'),
(13, 1, 'all', 'speaker_all_changed', '{\"speaker_on\":1}', '2026-02-01 02:12:34'),
(14, 1, 'all', 'session_ended', '{\"session_id\":\"1\",\"ended_at\":\"2026-02-01 02:16:24\"}', '2026-02-01 02:16:24'),
(15, 2, 'all', 'session_started', '{\"session_id\":2,\"name\":\"test2\",\"started_at\":\"2026-02-01 02:16:49\"}', '2026-02-01 02:16:49'),
(16, 2, 'all', 'participant_joined', '{\"participant_id\":2,\"student_name\":\"test 2\",\"class_name\":\"10B\",\"device_label\":\"Test pc\",\"ip_address\":\"192.168.101.5\",\"mic_on\":0,\"speaker_on\":1}', '2026-02-01 02:19:23'),
(17, 2, 'all', 'participant_joined', '{\"participant_id\":3,\"student_name\":\"test 3\",\"class_name\":\"10B\",\"device_label\":\"Test pc\",\"ip_address\":\"192.168.101.5\",\"mic_on\":0,\"speaker_on\":1}', '2026-02-01 02:20:07'),
(18, 2, 'all', 'mic_changed', '{\"participant_id\":3,\"mic_on\":1}', '2026-02-01 02:20:16'),
(19, 2, 'all', 'mic_changed', '{\"participant_id\":3,\"mic_on\":0}', '2026-02-01 02:20:18'),
(20, 2, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-01 02:20:25'),
(21, 2, 'admin', 'message_private_admin', '{\"message_id\":2,\"sender_type\":\"student\",\"sender_participant_id\":3,\"target_type\":\"private_admin\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-01 02:20:48\"}', '2026-02-01 02:20:48'),
(22, 2, 'participant:3', 'message_private_admin', '{\"message_id\":2,\"sender_type\":\"student\",\"sender_participant_id\":3,\"target_type\":\"private_admin\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-01 02:20:48\"}', '2026-02-01 02:20:48'),
(23, 2, 'admin', 'message_private_admin', '{\"message_id\":3,\"sender_type\":\"student\",\"sender_participant_id\":3,\"target_type\":\"private_admin\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-01 02:20:57\"}', '2026-02-01 02:20:57'),
(24, 2, 'participant:3', 'message_private_admin', '{\"message_id\":3,\"sender_type\":\"student\",\"sender_participant_id\":3,\"target_type\":\"private_admin\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-01 02:20:57\"}', '2026-02-01 02:20:57'),
(25, 2, 'all', 'session_ended', '{\"session_id\":\"2\",\"ended_at\":\"2026-02-01 02:21:21\"}', '2026-02-01 02:21:21'),
(26, 3, 'all', 'session_started', '{\"session_id\":3,\"name\":\"Sesi 2026-02-01 02:51\",\"started_at\":\"2026-02-01 02:51:07\"}', '2026-02-01 02:51:07'),
(27, 3, 'all', 'session_ended', '{\"session_id\":\"3\",\"ended_at\":\"2026-02-01 02:52:13\"}', '2026-02-01 02:52:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `materials`
--

CREATE TABLE `materials` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(160) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `text_content` mediumtext COLLATE utf8mb4_general_ci,
  `created_by_admin_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `material_files`
--

CREATE TABLE `material_files` (
  `id` int UNSIGNED NOT NULL,
  `material_id` int UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mime` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` int UNSIGNED DEFAULT NULL,
  `url_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `sender_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `sender_admin_id` int UNSIGNED DEFAULT NULL,
  `sender_participant_id` int UNSIGNED DEFAULT NULL,
  `target_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `target_participant_id` int UNSIGNED DEFAULT NULL,
  `body` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `session_id`, `sender_type`, `sender_admin_id`, `sender_participant_id`, `target_type`, `target_participant_id`, `body`, `created_at`) VALUES
(1, 1, 'admin', 1, NULL, 'public', NULL, 'test', '2026-02-01 02:02:43'),
(2, 2, 'student', NULL, 3, 'private_admin', NULL, 'test', '2026-02-01 02:20:48'),
(3, 2, 'student', NULL, 3, 'private_admin', NULL, 'test', '2026-02-01 02:20:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2026-01-25-000001', 'App\\Database\\Migrations\\CreateAdmins', 'default', 'App', 1769911207, 1),
(2, '2026-01-25-000002', 'App\\Database\\Migrations\\CreateSessions', 'default', 'App', 1769911207, 1),
(3, '2026-01-25-000003', 'App\\Database\\Migrations\\CreateParticipants', 'default', 'App', 1769911207, 1),
(4, '2026-01-25-000004', 'App\\Database\\Migrations\\CreateMessages', 'default', 'App', 1769911207, 1),
(5, '2026-01-25-000005', 'App\\Database\\Migrations\\CreateMaterials', 'default', 'App', 1769911207, 1),
(6, '2026-01-25-000006', 'App\\Database\\Migrations\\CreateMaterialFiles', 'default', 'App', 1769911207, 1),
(7, '2026-01-25-000007', 'App\\Database\\Migrations\\CreateSessionState', 'default', 'App', 1769911207, 1),
(8, '2026-01-25-000008', 'App\\Database\\Migrations\\CreateEvents', 'default', 'App', 1769911207, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `participants`
--

CREATE TABLE `participants` (
  `id` int UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `student_name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `class_name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `device_label` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `device_key` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mic_on` tinyint(1) NOT NULL DEFAULT '0',
  `speaker_on` tinyint(1) NOT NULL DEFAULT '1',
  `joined_at` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `left_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `participants`
--

INSERT INTO `participants` (`id`, `session_id`, `student_name`, `class_name`, `device_label`, `device_key`, `ip_address`, `mic_on`, `speaker_on`, `joined_at`, `last_seen_at`, `left_at`) VALUES
(1, 1, 'Test Siswa1', '10 A', NULL, NULL, '192.168.101.5', 1, 1, '2026-02-01 02:11:14', '2026-02-01 02:11:14', NULL),
(2, 2, 'test 2', '10B', 'Test pc', NULL, '192.168.101.5', 0, 1, '2026-02-01 02:19:23', '2026-02-01 02:19:35', NULL),
(3, 2, 'test 3', '10B', 'Test pc', NULL, '192.168.101.5', 0, 1, '2026-02-01 02:20:07', '2026-02-01 02:21:21', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_by_admin_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `name`, `is_active`, `started_at`, `ended_at`, `created_by_admin_id`, `created_at`) VALUES
(1, 'Test', 0, '2026-02-01 02:01:41', '2026-02-01 02:16:24', 1, '2026-02-01 02:01:41'),
(2, 'test2', 0, '2026-02-01 02:16:49', '2026-02-01 02:21:21', 1, '2026-02-01 02:16:49'),
(3, 'Sesi 2026-02-01 02:51', 0, '2026-02-01 02:51:07', '2026-02-01 02:52:13', 1, '2026-02-01 02:51:07');

-- --------------------------------------------------------

--
-- Struktur dari tabel `session_state`
--

CREATE TABLE `session_state` (
  `session_id` int UNSIGNED NOT NULL,
  `current_material_id` int UNSIGNED DEFAULT NULL,
  `broadcast_text` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `allow_student_mic` tinyint(1) NOT NULL DEFAULT '1',
  `allow_student_speaker` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `session_state`
--

INSERT INTO `session_state` (`session_id`, `current_material_id`, `broadcast_text`, `allow_student_mic`, `allow_student_speaker`, `updated_at`) VALUES
(1, NULL, 'Test', 1, 1, '2026-02-01 02:04:24'),
(2, NULL, 'test', 1, 1, '2026-02-01 02:20:25'),
(3, NULL, NULL, 1, 1, '2026-02-01 02:51:07');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id_id` (`session_id`,`id`),
  ADD KEY `session_id_audience_id` (`session_id`,`audience`,`id`);

--
-- Indeks untuk tabel `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type`,`id`);

--
-- Indeks untuk tabel `material_files`
--
ALTER TABLE `material_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id_id` (`material_id`,`id`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id_id` (`session_id`,`id`),
  ADD KEY `session_id_target_type` (`session_id`,`target_type`),
  ADD KEY `target_participant_id_id` (`target_participant_id`,`id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id_id` (`session_id`,`id`),
  ADD KEY `session_id_last_seen_at` (`session_id`,`last_seen_at`),
  ADD KEY `session_identity` (`session_id`,`student_name`,`class_name`,`device_key`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_active_id` (`is_active`,`id`);

--
-- Indeks untuk tabel `session_state`
--
ALTER TABLE `session_state`
  ADD PRIMARY KEY (`session_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `material_files`
--
ALTER TABLE `material_files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
