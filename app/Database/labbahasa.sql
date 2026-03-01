-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 01 Mar 2026 pada 07.50
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
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$12$ky6N3ErafWlqDwaRSKDEWuJWaX1DgRfIEar8DeyugAJk3/sbbh5nW', '2026-03-01 06:49:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `events`
--

CREATE TABLE `events` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `audience` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'all',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payload_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `materials`
--

CREATE TABLE `materials` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `text_content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
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
  `sort_order` int NOT NULL DEFAULT '0',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mime` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `size` int UNSIGNED DEFAULT NULL,
  `url_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `preview_url_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cover_url_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `sender_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sender_admin_id` int UNSIGNED DEFAULT NULL,
  `sender_participant_id` int UNSIGNED DEFAULT NULL,
  `target_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `target_participant_id` int UNSIGNED DEFAULT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
(8, '2026-01-25-000008', 'App\\Database\\Migrations\\CreateEvents', 'default', 'App', 1769911207, 1),
(9, '2026-02-03-000008', 'App\\Database\\Migrations\\AddVoiceLocksToSessionState', 'default', 'App', 1770443310, 2),
(10, '2026-02-03-000009', 'App\\Database\\Migrations\\AddParticipantDeviceKey', 'default', 'App', 1770443310, 2),
(11, '2026-02-05-000008', 'App\\Database\\Migrations\\AddMaterialItemSelection', 'default', 'App', 1770443310, 2),
(12, '2026-02-05-000009', 'App\\Database\\Migrations\\AddMaterialFileOrderAndPreview', 'default', 'App', 1770443310, 2),
(13, '2026-02-07-000010', 'App\\Database\\Migrations\\AddMaterialFileCover', 'default', 'App', 1770448521, 3),
(14, '2026-02-19-000011', 'App\\Database\\Migrations\\AddSessionTimeLimit', 'default', 'App', 1771495419, 4),
(15, '2026-02-20-000012', 'App\\Database\\Migrations\\AddParticipantPresenceState', 'default', 'App', 1771591395, 5);

-- --------------------------------------------------------

--
-- Struktur dari tabel `participants`
--

CREATE TABLE `participants` (
  `id` int UNSIGNED NOT NULL,
  `session_id` int UNSIGNED NOT NULL,
  `student_name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class_name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `device_label` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `device_key` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mic_on` tinyint(1) NOT NULL DEFAULT '0',
  `speaker_on` tinyint(1) NOT NULL DEFAULT '1',
  `joined_at` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `left_at` datetime DEFAULT NULL,
  `presence_state` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'offline',
  `presence_page` varchar(24) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `presence_reason` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `presence_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `started_at` datetime DEFAULT NULL,
  `ended_at` datetime DEFAULT NULL,
  `created_by_admin_id` int UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `duration_limit_minutes` int UNSIGNED DEFAULT NULL,
  `deadline_at` datetime DEFAULT NULL,
  `extension_minutes` int UNSIGNED DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `session_state`
--

CREATE TABLE `session_state` (
  `session_id` int UNSIGNED NOT NULL,
  `current_material_id` int UNSIGNED DEFAULT NULL,
  `current_material_file_id` int UNSIGNED DEFAULT NULL,
  `current_material_text_index` int DEFAULT NULL,
  `broadcast_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `broadcast_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` datetime DEFAULT NULL,
  `allow_student_mic` tinyint(1) DEFAULT '1',
  `allow_student_speaker` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `session_identity` (`session_id`,`student_name`,`class_name`,`device_key`),
  ADD KEY `session_presence_state` (`session_id`,`presence_state`);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
