-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 19 Feb 2026 pada 19.40
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
(1, 'admin', '$2y$12$ky6N3ErafWlqDwaRSKDEWuJWaX1DgRfIEar8DeyugAJk3/sbbh5nW', '2026-02-01 02:00:07');

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
(27, 3, 'all', 'session_ended', '{\"session_id\":\"3\",\"ended_at\":\"2026-02-01 02:52:13\"}', '2026-02-01 02:52:13'),
(28, 4, 'all', 'session_started', '{\"session_id\":4,\"name\":\"Sesi 2026-02-07 11:04\",\"started_at\":\"2026-02-07 11:04:07\"}', '2026-02-07 11:04:07'),
(29, 4, 'all', 'session_ended', '{\"session_id\":\"4\",\"ended_at\":\"2026-02-07 11:40:35\"}', '2026-02-07 11:40:35'),
(30, 5, 'all', 'session_started', '{\"session_id\":5,\"name\":\"Sesi 2026-02-07 12:36\",\"started_at\":\"2026-02-07 12:36:05\"}', '2026-02-07 12:36:05'),
(31, 5, 'all', 'material_changed', '{\"material_id\":1}', '2026-02-07 12:53:04'),
(32, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 12:53:07'),
(33, 5, 'all', 'participant_joined', '{\"participant_id\":4,\"student_name\":\"test\",\"class_name\":\"test\",\"device_label\":\"Komputer Lab 15\",\"ip_address\":\"192.168.101.15\",\"mic_on\":0,\"speaker_on\":1}', '2026-02-07 12:53:53'),
(34, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2961019693 1 udp 2113937151 b9bc6f0f-5c9e-4902-be0e-075217a0455b.local 52478 typ host generation 0 ufrag LmP3 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"LmP3\"}},\"sent_at\":\"2026-02-07 12:53:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:54'),
(35, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 3002540079029150977 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:LmP3\\r\\na=ice-pwd:8gYqJtNwOcHKUxgY3+NNgf4z\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 F0:7E:FE:C2:B7:3E:D9:8F:A8:94:F5:D3:56:A5:70:A7:7D:A0:E6:F9:A0:A5:E1:18:81:F3:FA:41:82:B6:34:4F\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 12:53:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:54'),
(36, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3116820503 1 udp 1677729535 160.19.227.254 22190 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag LmP3 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"LmP3\"}},\"sent_at\":\"2026-02-07 12:53:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:54'),
(37, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 1677466319262839511 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS 9b48e3e7-c3cf-48c3-a486-db2d9ee9be7b\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:UquB\\r\\na=ice-pwd:EyIiJl4RhbrT3x2DMWv4zUDH\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 FF:4E:00:92:60:5B:00:CD:6B:D2:DE:43:F1:FC:57:6D:3A:CB:DE:58:0A:B1:36:D0:A0:4F:D0:96:62:8D:4F:5E\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:9b48e3e7-c3cf-48c3-a486-db2d9ee9be7b 5676e65d-d379-4408-800b-da5bab7bb2ee\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:2604148754 cname:BQP+u/40r9giOeAB\\r\\n\"},\"sent_at\":\"2026-02-07 12:53:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:54'),
(38, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3364473744 1 udp 2122260223 192.168.101.15 64101 typ host generation 0 ufrag UquB network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"UquB\"}},\"sent_at\":\"2026-02-07 12:53:55\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:55'),
(39, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1581362505 1 udp 1686052607 160.19.227.254 13703 typ srflx raddr 192.168.101.15 rport 64101 generation 0 ufrag UquB network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"UquB\"}},\"sent_at\":\"2026-02-07 12:53:55\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:55'),
(40, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:908271364 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag UquB network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"UquB\"}},\"sent_at\":\"2026-02-07 12:53:55\",\"ip\":\"192.168.101.15\"}', '2026-02-07 12:53:55'),
(41, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 12:54:11'),
(42, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:11'),
(43, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:12'),
(44, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"play\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":0,\"playback_rate\":1}', '2026-02-07 12:54:23'),
(45, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"pause\",\"current_time\":1.436,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:25'),
(46, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"seek\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:30'),
(47, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 12:54:32'),
(48, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:32'),
(49, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:33'),
(50, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"play\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":0,\"playback_rate\":1}', '2026-02-07 12:54:34'),
(51, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"pause\",\"current_time\":4.003,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:38'),
(52, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 12:54:43'),
(53, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:43'),
(54, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:54:44'),
(55, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 12:54:48'),
(56, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 12:55:16'),
(57, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:55:16'),
(58, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:55:18'),
(59, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 12:55:20'),
(60, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 12:57:15'),
(61, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 12:57:17'),
(62, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:57:17'),
(63, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:57:17'),
(64, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 12:57:24'),
(65, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:57:25'),
(66, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 12:57:26'),
(67, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 13:01:43'),
(68, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":[],\"sent_at\":\"2026-02-07 13:36:25\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:25'),
(69, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 2121755721768868671 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:jUPV\\r\\na=ice-pwd:nuu8IJUT3K285LYqmpcgVps9\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 E2:7F:33:2F:DF:19:4F:79:51:0B:EC:2B:FA:26:5C:CE:F1:D3:6A:36:57:94:10:D3:74:C3:46:8B:28:C9:F9:0E\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 13:36:25\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:25'),
(70, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1686304358 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag jUPV network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"jUPV\"}},\"sent_at\":\"2026-02-07 13:36:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:26'),
(71, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:441259262 1 udp 2122260223 192.168.101.15 64149 typ host generation 0 ufrag jUPV network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"jUPV\"}},\"sent_at\":\"2026-02-07 13:36:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:26'),
(72, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"69cce083-c92e-4e1b-83ec-e248ff2a1d91\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3682812852 1 udp 1686052607 160.19.227.254 27935 typ srflx raddr 192.168.101.15 rport 64149 generation 0 ufrag jUPV network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"jUPV\"}},\"sent_at\":\"2026-02-07 13:36:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:26'),
(73, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 611998043194586269 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:01fH\\r\\na=ice-pwd:8SKrRS3OHmURsdxH3eLjyVGj\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 7A:45:4D:58:DE:E8:CB:A4:41:D4:C1:4B:D3:41:7C:E8:AD:D1:30:A1:2E:E6:05:8D:89:99:65:31:73:33:F1:90\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 13:36:30\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:30'),
(74, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:29270059 1 udp 2113937151 74fef0b9-1160-4462-a399-1740d4a1440f.local 61321 typ host generation 0 ufrag 01fH network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"01fH\"}},\"sent_at\":\"2026-02-07 13:36:30\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:30'),
(75, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:134601489 1 udp 1677729535 160.19.227.254 26112 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag 01fH network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"01fH\"}},\"sent_at\":\"2026-02-07 13:36:31\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:31'),
(76, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 6085144835635989754 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS ed9cd4da-7023-4c13-a89a-405044df179c\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:sWXU\\r\\na=ice-pwd:5rMj6gCiIwmpoa7IxYW7QDVK\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 AA:2A:9D:35:F6:98:82:37:5E:59:86:CC:98:42:BA:0E:DD:9B:11:90:C3:97:BE:DA:0B:8C:FF:A3:CB:19:47:60\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:ed9cd4da-7023-4c13-a89a-405044df179c befbc522-4231-4c6a-bc84-96cfa72324ee\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:1392021116 cname:mk//e6wjjRIwdsWE\\r\\n\"},\"sent_at\":\"2026-02-07 13:36:31\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:31'),
(77, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3027432321 1 udp 2122260223 192.168.101.15 55690 typ host generation 0 ufrag sWXU network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"sWXU\"}},\"sent_at\":\"2026-02-07 13:36:31\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:31'),
(78, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1975297227 1 udp 1686052607 160.19.227.254 17884 typ srflx raddr 192.168.101.15 rport 55690 generation 0 ufrag sWXU network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"sWXU\"}},\"sent_at\":\"2026-02-07 13:36:31\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:31'),
(79, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3401388313 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag sWXU network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"sWXU\"}},\"sent_at\":\"2026-02-07 13:36:31\",\"ip\":\"192.168.101.15\"}', '2026-02-07 13:36:31'),
(80, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 13:36:49'),
(81, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 13:37:06'),
(82, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 13:37:27'),
(83, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 13:37:27'),
(84, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 13:37:28'),
(85, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 13:38:00'),
(86, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 13:38:01'),
(87, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 13:38:01'),
(88, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 13:38:09'),
(89, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 13:38:57'),
(90, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":[],\"sent_at\":\"2026-02-07 14:15:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:15:27'),
(91, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 2789749973235067778 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:vFMP\\r\\na=ice-pwd:F3bIcGTuHCn+IJAYwbpyhhuu\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 D1:04:EE:DA:8A:C3:C7:17:7B:86:81:07:1B:53:5F:29:83:D2:13:47:31:6B:BE:4C:A4:6C:D4:70:A2:E0:4A:99\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:15:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:15:27'),
(92, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1954768996 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag vFMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vFMP\"}},\"sent_at\":\"2026-02-07 14:15:28\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:15:28'),
(93, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2317991152 1 udp 2122260223 192.168.101.15 54999 typ host generation 0 ufrag vFMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vFMP\"}},\"sent_at\":\"2026-02-07 14:15:28\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:15:28'),
(94, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:484563497 1 udp 1686052607 160.19.227.254 28355 typ srflx raddr 192.168.101.15 rport 54999 generation 0 ufrag vFMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vFMP\"}},\"sent_at\":\"2026-02-07 14:15:28\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:15:28'),
(95, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 14:15:44'),
(96, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:15:44'),
(97, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:15:45'),
(98, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 14:15:49'),
(99, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:15:50'),
(100, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:15:50'),
(101, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:16:06'),
(102, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 3710556881265457895 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:P9q+\\r\\na=ice-pwd:PSg02O0AoM9Mxew/JoXAFdhu\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 07:B7:19:4F:A5:63:EE:00:B6:F9:D6:AB:97:D7:A3:CD:3F:7D:50:B1:C2:92:F5:2E:89:C1:46:4A:2B:DE:17:C2\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:17:17\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:17:17'),
(103, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:982251300 1 udp 2122260223 192.168.101.15 51143 typ host generation 0 ufrag P9q+ network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"P9q+\"}},\"sent_at\":\"2026-02-07 14:17:18\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:17:18'),
(104, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2890109437 1 udp 1686052607 160.19.227.254 1609 typ srflx raddr 192.168.101.15 rport 51143 generation 0 ufrag P9q+ network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"P9q+\"}},\"sent_at\":\"2026-02-07 14:17:18\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:17:18'),
(105, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"3fa3ccc7-a626-43f2-a5f6-08d4799ebe7b\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3290506160 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag P9q+ network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"P9q+\"}},\"sent_at\":\"2026-02-07 14:17:18\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:17:18'),
(106, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":28,\"text_index\":null}', '2026-02-07 14:17:19'),
(107, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 8226486074806278775 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:VQqA\\r\\na=ice-pwd:6ysxErJomE/0t+QWuUn4pSPO\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 2E:2E:66:E9:BC:53:F9:5F:77:86:58:36:B6:0D:04:5E:63:A2:FB:2A:3F:C0:F3:66:AB:02:F9:B7:18:95:E0:D1\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(108, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3149030315 1 udp 2113937151 8accbaaa-b464-499c-97cc-aa540d09c000.local 58592 typ host generation 0 ufrag VQqA network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"VQqA\"}},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(109, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2986940561 1 udp 1677729535 160.19.227.254 30705 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag VQqA network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"VQqA\"}},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(110, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 7801385621765121688 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS aa50a6db-166b-4d65-ac39-f8ba1cb1d61d\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:nKlb\\r\\na=ice-pwd:2jx9YX4lf7r79aE0glOoXSB1\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 68:2A:76:8A:F2:7D:2B:81:F1:2A:08:6A:49:CE:E3:6D:A2:F3:7B:05:F2:71:C8:B0:11:A1:6B:F5:5F:68:69:C5\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:aa50a6db-166b-4d65-ac39-f8ba1cb1d61d b71bb612-d554-419e-9a92-8c68670db61a\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:1298670460 cname:qi1PUBvv1b7gZpIY\\r\\n\"},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(111, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2717477028 1 udp 2122260223 192.168.101.15 64538 typ host generation 0 ufrag nKlb network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"nKlb\"}},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(112, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:925961853 1 udp 1686052607 160.19.227.254 14718 typ srflx raddr 192.168.101.15 rport 64538 generation 0 ufrag nKlb network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"nKlb\"}},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(113, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1599323184 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag nKlb network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"nKlb\"}},\"sent_at\":\"2026-02-07 14:29:45\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:45'),
(114, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":[],\"sent_at\":\"2026-02-07 14:29:50\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:50'),
(115, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:4155194125 1 udp 2122260223 192.168.101.15 62472 typ host generation 0 ufrag fpfd network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"fpfd\"}},\"sent_at\":\"2026-02-07 14:29:50\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:50'),
(116, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 5407246909179744622 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:fpfd\\r\\na=ice-pwd:sK/sGUu9AR9lEcyAq4VQ4l9p\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 B5:B2:6D:23:D5:62:34:7A:EA:9E:51:7D:07:85:EB:7C:78:F4:6B:B0:CB:D1:C7:02:4C:7A:3C:45:0E:62:BA:F9\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:29:50\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:50'),
(117, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:912616519 1 udp 1686052607 160.19.227.254 19951 typ srflx raddr 192.168.101.15 rport 62472 generation 0 ufrag fpfd network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"fpfd\"}},\"sent_at\":\"2026-02-07 14:29:50\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:50'),
(118, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"d792c3e3-6b00-422e-8d49-77a2c5cff097\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2305087893 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag fpfd network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"fpfd\"}},\"sent_at\":\"2026-02-07 14:29:50\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:29:50'),
(119, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:29:57'),
(120, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 14:30:01'),
(121, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:01'),
(122, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:02'),
(123, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 14:30:03'),
(124, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 14:30:05'),
(125, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:05'),
(126, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:07'),
(127, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 14:30:08'),
(128, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:08'),
(129, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:30:08'),
(130, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 14:30:13'),
(131, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:31:15'),
(132, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:31:20'),
(133, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:31:21'),
(134, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:31:22'),
(135, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:31:23'),
(136, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:31:28'),
(137, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:31:29'),
(138, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:31:29'),
(139, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 14:31:31'),
(140, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:31:39'),
(141, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 6428260245486059535 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:NUw8\\r\\na=ice-pwd:csnx9TtnD45kMwSJG1qYLKXx\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 1E:A0:C3:34:E7:B6:00:68:62:AF:69:62:DC:C5:B4:EE:81:32:83:F5:BC:E2:35:F9:BE:EB:B6:26:A8:E7:A4:1A\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:49:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:22'),
(142, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3437968185 1 udp 2113937151 d7e9a6c8-7763-4be4-9755-b18fcc85c97c.local 51529 typ host generation 0 ufrag NUw8 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"NUw8\"}},\"sent_at\":\"2026-02-07 14:49:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:22');
INSERT INTO `events` (`id`, `session_id`, `audience`, `type`, `payload_json`, `created_at`) VALUES
(143, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3310370819 1 udp 1677729535 160.19.227.254 21515 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag NUw8 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"NUw8\"}},\"sent_at\":\"2026-02-07 14:49:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:22'),
(144, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 4431522399163494415 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS 5c0afdd6-0a55-44a4-bf29-6deca7043459\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:8KHw\\r\\na=ice-pwd:VIfLnhGom506AoEuHXQx0nSB\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 3F:0A:7A:20:29:50:BF:2D:BE:4D:3C:87:12:7B:2B:1F:36:C7:D1:CF:03:06:29:22:50:35:9D:BD:38:A8:9B:66\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:5c0afdd6-0a55-44a4-bf29-6deca7043459 21ae6e41-22b0-43f1-bfd1-d068b2e08722\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:2521665152 cname:tRnwBG8RriFSuMCW\\r\\n\"},\"sent_at\":\"2026-02-07 14:49:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:23'),
(145, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2997445497 1 udp 2122260223 192.168.101.15 59346 typ host generation 0 ufrag 8KHw network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"8KHw\"}},\"sent_at\":\"2026-02-07 14:49:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:23'),
(146, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:610338208 1 udp 1686052607 160.19.227.254 25528 typ srflx raddr 192.168.101.15 rport 59346 generation 0 ufrag 8KHw network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"8KHw\"}},\"sent_at\":\"2026-02-07 14:49:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:23'),
(147, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1275312109 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag 8KHw network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"8KHw\"}},\"sent_at\":\"2026-02-07 14:49:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:23'),
(148, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":[],\"sent_at\":\"2026-02-07 14:49:25\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:25'),
(149, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 4876756762531560758 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:kq8V\\r\\na=ice-pwd:30RLEeaJZ9muatk8Xj+OtX7T\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 EF:BB:0B:87:D7:F9:32:64:61:17:81:59:39:FD:88:0B:DA:6B:C9:90:21:8D:E6:76:6B:C0:5B:50:67:0E:A1:03\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:49:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:27'),
(150, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2889547134 1 udp 2122260223 192.168.101.15 62942 typ host generation 0 ufrag kq8V network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"kq8V\"}},\"sent_at\":\"2026-02-07 14:49:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:27'),
(151, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1844750900 1 udp 1686052607 160.19.227.254 20802 typ srflx raddr 192.168.101.15 rport 62942 generation 0 ufrag kq8V network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"kq8V\"}},\"sent_at\":\"2026-02-07 14:49:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:27'),
(152, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"ba1add10-4bd1-4b51-a532-586b6d6e22f5\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3539279846 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag kq8V network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"kq8V\"}},\"sent_at\":\"2026-02-07 14:49:27\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:49:27'),
(153, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:49:44'),
(154, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 14:49:47'),
(155, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 14:49:52'),
(156, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:49:54'),
(157, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 14:49:58'),
(158, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:49:59'),
(159, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:50:07'),
(160, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:50:10'),
(161, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 14:50:33'),
(162, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:50:34'),
(163, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:50:35'),
(164, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:50:37'),
(165, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:50:42'),
(166, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:50:45'),
(167, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":28,\"text_index\":null}', '2026-02-07 14:50:48'),
(168, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:50:50'),
(169, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:50:53'),
(170, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:50:55'),
(171, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 14:50:57'),
(172, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:50:58'),
(173, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 14:50:58'),
(174, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:50:59'),
(175, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:01'),
(176, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:03'),
(177, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:04'),
(178, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:06'),
(179, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:07'),
(180, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:08'),
(181, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:08'),
(182, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:09'),
(183, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:11'),
(184, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:12'),
(185, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:13'),
(186, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:15'),
(187, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:16'),
(188, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:17'),
(189, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:18'),
(190, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:18'),
(191, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:19'),
(192, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:20'),
(193, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:20'),
(194, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:21'),
(195, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:21'),
(196, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:23'),
(197, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:24'),
(198, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:25'),
(199, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:26'),
(200, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":25,\"text_index\":null}', '2026-02-07 14:51:30'),
(201, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:51:33'),
(202, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:51:35'),
(203, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:51:36'),
(204, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1654311492 1 udp 2113937151 601ad79f-08ce-4f2a-a3fb-63676fd8c448.local 52089 typ host generation 0 ufrag mBwR network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"mBwR\"}},\"sent_at\":\"2026-02-07 14:55:51\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:51'),
(205, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 7732433031002300475 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:mBwR\\r\\na=ice-pwd:6BPkQUy6Tw/JdN7hlKQh1Plk\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 F4:1A:79:14:BB:6A:70:59:C3:41:C9:F0:29:26:C3:45:5E:4D:06:A4:4C:25:9A:25:15:52:32:8C:1C:29:72:08\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:55:51\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:51'),
(206, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1797370238 1 udp 1677729535 160.19.227.254 28003 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag mBwR network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"mBwR\"}},\"sent_at\":\"2026-02-07 14:55:51\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:51'),
(207, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 2913870372719169648 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS f365ea11-c45b-47c2-902d-7a0f46e65c81\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:6eiX\\r\\na=ice-pwd:Nr0jRrfRar8EyN/KS5mNseNM\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 1B:65:4C:B4:F6:D9:54:2A:4B:C3:E8:C4:5A:EC:3D:84:43:C0:41:88:1C:CD:0E:7C:E4:D2:40:4F:E4:82:3B:50\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:f365ea11-c45b-47c2-902d-7a0f46e65c81 c558bb13-a9bf-46bc-81c5-6ee9e93ab477\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:1182103 cname:Cc4gDdc4QY46BgTP\\r\\n\"},\"sent_at\":\"2026-02-07 14:55:52\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:52'),
(208, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3827851862 1 udp 2122260223 192.168.101.15 63434 typ host generation 0 ufrag 6eiX network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"6eiX\"}},\"sent_at\":\"2026-02-07 14:55:52\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:52'),
(209, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1927283855 1 udp 1686052607 160.19.227.254 27747 typ srflx raddr 192.168.101.15 rport 63434 generation 0 ufrag 6eiX network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"6eiX\"}},\"sent_at\":\"2026-02-07 14:55:52\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:52'),
(210, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:444774082 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag 6eiX network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"6eiX\"}},\"sent_at\":\"2026-02-07 14:55:52\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:52'),
(211, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":[],\"sent_at\":\"2026-02-07 14:55:55\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:55'),
(212, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 2771858693962501327 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:9RS9\\r\\na=ice-pwd:06YehwuWpfFnBnDeXZHrfklX\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 EB:04:B1:C2:37:FF:F9:E2:F3:C5:03:00:F0:74:30:2A:45:01:E7:1D:20:A0:9A:5D:B5:7D:B4:E5:2E:A5:3C:2B\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 14:55:55\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:55'),
(213, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:130633119 1 udp 2122260223 192.168.101.15 59509 typ host generation 0 ufrag 9RS9 network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"9RS9\"}},\"sent_at\":\"2026-02-07 14:55:56\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:56'),
(214, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3322354389 1 udp 1686052607 160.19.227.254 24385 typ srflx raddr 192.168.101.15 rport 59509 generation 0 ufrag 9RS9 network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"9RS9\"}},\"sent_at\":\"2026-02-07 14:55:56\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:56'),
(215, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2030481159 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag 9RS9 network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"9RS9\"}},\"sent_at\":\"2026-02-07 14:55:56\",\"ip\":\"192.168.101.15\"}', '2026-02-07 14:55:56'),
(216, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:56:05'),
(217, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:56:09'),
(218, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:56:09'),
(219, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:56:12'),
(220, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 14:56:15'),
(221, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 14:56:17'),
(222, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":0}', '2026-02-07 14:56:19'),
(223, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 14:56:22'),
(224, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 795010822191820515 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:BMMP\\r\\na=ice-pwd:yarU7Jf8f3/yK6trh6V0ot8y\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 CA:9D:6A:89:67:40:33:97:66:70:CC:4A:51:10:63:E7:B4:36:B3:DD:95:7A:B1:47:82:2C:83:05:BC:D1:EC:A1\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:13:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:13:22'),
(225, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:929882231 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag BMMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"BMMP\"}},\"sent_at\":\"2026-02-07 15:13:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:13:22'),
(226, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1235419887 1 udp 2122260223 192.168.101.15 62799 typ host generation 0 ufrag BMMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"BMMP\"}},\"sent_at\":\"2026-02-07 15:13:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:13:22'),
(227, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"77e52d27-b5b9-4f83-92d7-3a7d69c786c0\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2288866725 1 udp 1686052607 160.19.227.254 27073 typ srflx raddr 192.168.101.15 rport 62799 generation 0 ufrag BMMP network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"BMMP\"}},\"sent_at\":\"2026-02-07 15:13:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:13:22'),
(228, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 1762242538995799567 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:p9Y5\\r\\na=ice-pwd:Oo7sojDhLYXxfXqLvrF7+kyO\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 37:57:DC:B3:F6:71:CC:FE:84:90:5F:C9:E7:5C:B2:4D:93:D8:13:C4:39:71:A5:46:EF:29:8A:0D:4B:D4:3E:BC\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:19\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:19'),
(229, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3041749384 1 udp 2113937151 44a68bd5-cdbb-4b8d-a3b3-6f7e233139bb.local 61795 typ host generation 0 ufrag p9Y5 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"p9Y5\"}},\"sent_at\":\"2026-02-07 15:14:19\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:19'),
(230, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3095033988 1 udp 1677729535 160.19.227.254 8896 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag p9Y5 network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"p9Y5\"}},\"sent_at\":\"2026-02-07 15:14:19\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:19'),
(231, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 2502082107691019059 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS e0475b4e-9f28-4f9c-a16b-c40a44494ba6\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:yU3I\\r\\na=ice-pwd:9XwAZ9lj5GXapnnh4BLqF8Sp\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 60:02:4E:9E:67:CE:ED:36:40:33:5B:26:56:04:FA:02:82:AF:21:BE:8D:25:BF:CD:06:DA:9B:BD:D6:92:B6:74\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:e0475b4e-9f28-4f9c-a16b-c40a44494ba6 9ae22748-48f9-4217-81d4-b0f9038b6b91\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:2342135983 cname:e+LgJi2DKP9QXWs8\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:19\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:19'),
(232, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1447911617 1 udp 2122260223 192.168.101.15 61700 typ host generation 0 ufrag yU3I network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"yU3I\"}},\"sent_at\":\"2026-02-07 15:14:20\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:20'),
(233, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2541959051 1 udp 1686052607 160.19.227.254 4655 typ srflx raddr 192.168.101.15 rport 61700 generation 0 ufrag yU3I network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"yU3I\"}},\"sent_at\":\"2026-02-07 15:14:20\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:20'),
(234, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":{\"candidate\":{\"candidate\":\"candidate:679641689 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag yU3I network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"yU3I\"}},\"sent_at\":\"2026-02-07 15:14:20\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:20'),
(235, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"hangup\",\"call_id\":\"b3a137f7-71ec-42c9-8c3a-272a66050a75\",\"data\":[],\"sent_at\":\"2026-02-07 15:14:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:22'),
(236, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 6740506763754478028 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:ThNj\\r\\na=ice-pwd:ZRUxn6AQKO2Y/Kv9QMEscnzW\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 C3:6C:9F:30:03:43:FD:71:8C:57:C6:FC:C7:63:A5:76:B2:2A:C7:11:7E:3E:09:57:A5:81:52:72:71:90:B2:45\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:22'),
(237, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2566928609 1 udp 2113937151 1660bb57-9997-49cb-be76-0e4731c76459.local 60019 typ host generation 0 ufrag ThNj network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"ThNj\"}},\"sent_at\":\"2026-02-07 15:14:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:22'),
(238, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2428191707 1 udp 1677729535 160.19.227.254 21304 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag ThNj network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"ThNj\"}},\"sent_at\":\"2026-02-07 15:14:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:22'),
(239, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2574269338 1 udp 2122260223 192.168.101.15 56313 typ host generation 0 ufrag Llww network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"Llww\"}},\"sent_at\":\"2026-02-07 15:14:22\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:22'),
(240, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 7697687980423234966 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS e0475b4e-9f28-4f9c-a16b-c40a44494ba6\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:Llww\\r\\na=ice-pwd:Y9v6Yupvj+a1Ykp8mbZT7FFj\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 B8:7E:98:7C:E2:9C:77:A1:B2:42:58:14:63:C0:45:77:69:3C:95:78:09:F1:24:8E:31:78:14:34:5E:44:06:8D\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=sendonly\\r\\na=msid:e0475b4e-9f28-4f9c-a16b-c40a44494ba6 9ae22748-48f9-4217-81d4-b0f9038b6b91\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\na=ssrc:3899226916 cname:UhQ/B2Pc2oIdvM/M\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:23'),
(241, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"candidate\":{\"candidate\":\"candidate:263740739 1 udp 1686052607 160.19.227.254 25127 typ srflx raddr 192.168.101.15 rport 56313 generation 0 ufrag Llww network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"Llww\"}},\"sent_at\":\"2026-02-07 15:14:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:23'),
(242, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1742401294 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag Llww network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"Llww\"}},\"sent_at\":\"2026-02-07 15:14:23\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:23'),
(243, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"15d99595-6341-47eb-b62c-3eb2e20678c7\",\"data\":[],\"sent_at\":\"2026-02-07 15:14:24\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:24'),
(244, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2499961548 1 udp 2113937151 548d94b1-8b73-496d-883a-705ef3df4b62.local 65185 typ host generation 0 ufrag 7DWy network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"7DWy\"}},\"sent_at\":\"2026-02-07 15:14:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:26'),
(245, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"offer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 6684750043803665894 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:7DWy\\r\\na=ice-pwd:R1K2wm1P41krdnRgQZDZOJlH\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 40:28:11:98:8E:D4:FA:8A:8D:A2:05:E7:37:73:2D:70:A4:5B:B1:5C:F4:F3:80:4D:19:45:A6:E2:4F:79:4D:F0\\r\\na=setup:actpass\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=recvonly\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:26'),
(246, 5, 'admin', 'rtc_signal', '{\"from_type\":\"student\",\"from_participant_id\":4,\"to_type\":\"admin\",\"to_participant_id\":null,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2553639872 1 udp 1677729535 160.19.227.254 4973 typ srflx raddr 0.0.0.0 rport 0 generation 0 ufrag 7DWy network-cost 999\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"7DWy\"}},\"sent_at\":\"2026-02-07 15:14:26\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:26'),
(247, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 3912257901084942367 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:eeyW\\r\\na=ice-pwd:cefqTmgOS/ufDCI6+MwRhQ0v\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 3E:80:CB:E6:E4:52:8E:25:1F:19:B4:3F:D8:B6:3E:AB:2A:02:63:54:0F:22:A0:A9:A6:B5:57:78:81:BF:A9:9C\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:14:29\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:29'),
(248, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3422153077 1 udp 2122260223 192.168.101.15 61879 typ host generation 0 ufrag eeyW network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"eeyW\"}},\"sent_at\":\"2026-02-07 15:14:29\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:29'),
(249, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:171425343 1 udp 1686052607 160.19.227.254 24347 typ srflx raddr 192.168.101.15 rport 61879 generation 0 ufrag eeyW network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"eeyW\"}},\"sent_at\":\"2026-02-07 15:14:29\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:29'),
(250, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3040218093 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag eeyW network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"eeyW\"}},\"sent_at\":\"2026-02-07 15:14:29\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:14:29'),
(251, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":2}', '2026-02-07 15:15:08'),
(252, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"text\",\"file_id\":0,\"text_index\":1}', '2026-02-07 15:15:10'),
(253, 5, 'all', 'broadcast_text_changed', '{\"broadcast_text\":\"test\"}', '2026-02-07 15:15:13'),
(254, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":26,\"text_index\":null}', '2026-02-07 15:15:16'),
(255, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 15:15:16'),
(256, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":26,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 15:15:17'),
(257, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":27,\"text_index\":null}', '2026-02-07 15:15:21'),
(258, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 15:15:21'),
(259, 5, 'all', 'material_media_control', '{\"material_id\":1,\"file_id\":27,\"action\":\"sync\",\"current_time\":0,\"volume\":1,\"muted\":0,\"paused\":1,\"playback_rate\":1}', '2026-02-07 15:15:22'),
(260, 5, 'all', 'material_changed', '{\"material_id\":1,\"item_type\":\"file\",\"file_id\":28,\"text_index\":null}', '2026-02-07 15:15:35'),
(261, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":[],\"sent_at\":\"2026-02-07 15:15:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:15:54');
INSERT INTO `events` (`id`, `session_id`, `audience`, `type`, `payload_json`, `created_at`) VALUES
(262, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 6433537227331269742 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:qK9E\\r\\na=ice-pwd:x2PYyPrgetKez5R82eZCjtzs\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 D6:3F:9E:FC:51:B3:15:56:34:3D:D2:85:E9:24:AE:FD:11:CB:7A:41:16:96:87:E7:0D:23:09:D4:D7:55:2E:9A\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:15:58\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:15:58'),
(263, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2986648859 1 udp 2122260223 192.168.101.15 59354 typ host generation 0 ufrag qK9E network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"qK9E\"}},\"sent_at\":\"2026-02-07 15:15:58\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:15:58'),
(264, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:617399234 1 udp 1686052607 160.19.227.254 24492 typ srflx raddr 192.168.101.15 rport 59354 generation 0 ufrag qK9E network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"qK9E\"}},\"sent_at\":\"2026-02-07 15:15:58\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:15:58'),
(265, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1286500751 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag qK9E network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"qK9E\"}},\"sent_at\":\"2026-02-07 15:15:58\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:15:58'),
(266, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"hangup\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":[],\"sent_at\":\"2026-02-07 15:16:09\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:16:09'),
(267, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 4721548745084912659 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:vrAG\\r\\na=ice-pwd:IDA6J4wYemxNuEeaI1CTMcyL\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 C8:2D:3E:63:40:3F:9D:AD:DE:FE:4F:0E:5E:74:18:C7:D7:15:52:B8:6D:D5:CA:AD:EC:2D:C4:DB:58:DF:80:9D\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-07 15:31:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:31:54'),
(268, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:3756620193 1 udp 2122260223 192.168.101.15 58530 typ host generation 0 ufrag vrAG network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vrAG\"}},\"sent_at\":\"2026-02-07 15:31:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:31:54'),
(269, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:505884395 1 udp 1686052607 160.19.227.254 6508 typ srflx raddr 192.168.101.15 rport 58530 generation 0 ufrag vrAG network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vrAG\"}},\"sent_at\":\"2026-02-07 15:31:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:31:54'),
(270, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2703661881 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag vrAG network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"vrAG\"}},\"sent_at\":\"2026-02-07 15:31:54\",\"ip\":\"192.168.101.15\"}', '2026-02-07 15:31:54'),
(271, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 925674854799677399 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:hZZv\\r\\na=ice-pwd:X379swZQpPnxHS90pGEdCRCk\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 2F:62:E6:EB:F8:2B:06:FE:3C:68:7C:59:92:84:1B:CB:7C:91:66:A8:FC:4B:46:68:CE:28:B6:9D:2E:1E:7B:AE\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-19 16:20:22\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:22'),
(272, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:878780583 1 udp 2122260223 192.168.101.15 61599 typ host generation 0 ufrag hZZv network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"hZZv\"}},\"sent_at\":\"2026-02-19 16:20:22\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:22'),
(273, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:4121917421 1 udp 1686052607 160.19.227.5 11086 typ srflx raddr 192.168.101.15 rport 61599 generation 0 ufrag hZZv network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"hZZv\"}},\"sent_at\":\"2026-02-19 16:20:22\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:22'),
(274, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1252966975 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag hZZv network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"hZZv\"}},\"sent_at\":\"2026-02-19 16:20:22\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:22'),
(275, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:1466332624 1 udp 2122260223 192.168.101.15 60150 typ host generation 0 ufrag ewfm network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"ewfm\"}},\"sent_at\":\"2026-02-19 16:20:27\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:27'),
(276, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"answer\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"type\":\"answer\",\"sdp\":\"v=0\\r\\no=- 3359060206091121978 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=group:BUNDLE 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\nm=audio 9 UDP/TLS/RTP/SAVPF 111 63 9 0 8 13 110 126\\r\\nc=IN IP4 0.0.0.0\\r\\na=rtcp:9 IN IP4 0.0.0.0\\r\\na=ice-ufrag:ewfm\\r\\na=ice-pwd:Gf9VfvSolU788t8BS8aVJ0mU\\r\\na=ice-options:trickle\\r\\na=fingerprint:sha-256 5E:A6:62:90:72:3A:FA:17:5B:65:85:24:24:46:8B:83:8B:F5:CD:1D:B1:A5:E3:E6:8B:5A:D7:A6:86:07:65:C0\\r\\na=setup:active\\r\\na=mid:0\\r\\na=extmap:1 urn:ietf:params:rtp-hdrext:ssrc-audio-level\\r\\na=extmap:2 http://www.webrtc.org/experiments/rtp-hdrext/abs-send-time\\r\\na=extmap:3 http://www.ietf.org/id/draft-holmer-rmcat-transport-wide-cc-extensions-01\\r\\na=extmap:4 urn:ietf:params:rtp-hdrext:sdes:mid\\r\\na=inactive\\r\\na=rtcp-mux\\r\\na=rtcp-rsize\\r\\na=rtpmap:111 opus/48000/2\\r\\na=rtcp-fb:111 transport-cc\\r\\na=fmtp:111 minptime=10;useinbandfec=1\\r\\na=rtpmap:63 red/48000/2\\r\\na=fmtp:63 111/111\\r\\na=rtpmap:9 G722/8000\\r\\na=rtpmap:0 PCMU/8000\\r\\na=rtpmap:8 PCMA/8000\\r\\na=rtpmap:13 CN/8000\\r\\na=rtpmap:110 telephone-event/48000\\r\\na=rtpmap:126 telephone-event/8000\\r\\n\"},\"sent_at\":\"2026-02-19 16:20:27\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:27'),
(277, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:2527601306 1 udp 1686052607 160.19.227.5 60145 typ srflx raddr 192.168.101.15 rport 60150 generation 0 ufrag ewfm network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"ewfm\"}},\"sent_at\":\"2026-02-19 16:20:27\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:27'),
(278, 5, 'participant:4', 'rtc_signal', '{\"from_type\":\"admin\",\"from_participant_id\":null,\"to_type\":\"participant\",\"to_participant_id\":4,\"signal_type\":\"candidate\",\"call_id\":\"8279e74b-5e58-4135-b727-02e41615f90e\",\"data\":{\"candidate\":{\"candidate\":\"candidate:698980168 1 tcp 1518280447 192.168.101.15 9 typ host tcptype active generation 0 ufrag ewfm network-id 1\",\"sdpMid\":\"0\",\"sdpMLineIndex\":0,\"usernameFragment\":\"ewfm\"}},\"sent_at\":\"2026-02-19 16:20:27\",\"ip\":\"192.168.101.15\"}', '2026-02-19 16:20:27'),
(279, 5, 'all', 'session_ended', '{\"session_id\":\"5\",\"ended_at\":\"2026-02-19 16:20:29\"}', '2026-02-19 16:20:29'),
(280, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 16:58\",\"started_at\":\"2026-02-19 16:58:44\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:28:44\"}', '2026-02-19 16:58:44'),
(281, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:02\",\"started_at\":\"2026-02-19 17:02:46\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:32:46\"}', '2026-02-19 17:02:46'),
(282, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:02\",\"started_at\":\"2026-02-19 17:02:55\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:32:55\"}', '2026-02-19 17:02:55'),
(283, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:02\",\"started_at\":\"2026-02-19 17:02:56\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:32:56\"}', '2026-02-19 17:02:56'),
(284, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:02\",\"started_at\":\"2026-02-19 17:02:57\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:32:57\"}', '2026-02-19 17:02:57'),
(285, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:01\",\"duration_limit_minutes\":30,\"deadline_at\":\"2026-02-19 17:33:01\"}', '2026-02-19 17:03:01'),
(286, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:02\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:02\"}', '2026-02-19 17:03:02'),
(287, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:03\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:03\"}', '2026-02-19 17:03:03'),
(288, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:04\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:04\"}', '2026-02-19 17:03:04'),
(289, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:05\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:05\"}', '2026-02-19 17:03:05'),
(290, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:06\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:06\"}', '2026-02-19 17:03:06'),
(291, 0, 'all', 'session_started', '{\"session_id\":false,\"name\":\"test\",\"started_at\":\"2026-02-19 17:03:08\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:08\"}', '2026-02-19 17:03:08'),
(292, 6, 'all', 'session_started', '{\"session_id\":6,\"name\":\"Sesi 2026-02-19 17:03\",\"started_at\":\"2026-02-19 17:03:44\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:33:44\"}', '2026-02-19 17:03:44'),
(293, 6, 'all', 'session_ended', '{\"session_id\":6,\"ended_at\":\"2026-02-19 17:04:28\",\"reason\":\"manual\"}', '2026-02-19 17:04:28'),
(294, 7, 'all', 'session_started', '{\"session_id\":7,\"name\":\"Sesi 2026-02-19 17:04\",\"started_at\":\"2026-02-19 17:04:34\",\"duration_limit_minutes\":90,\"deadline_at\":\"2026-02-19 18:34:34\"}', '2026-02-19 17:04:34'),
(295, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 19:04:34\",\"extension_minutes\":30,\"added_minutes\":30}', '2026-02-19 17:05:51'),
(296, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 19:34:34\",\"extension_minutes\":60,\"added_minutes\":30}', '2026-02-19 17:08:49'),
(297, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 20:04:34\",\"extension_minutes\":90,\"added_minutes\":30}', '2026-02-19 17:09:08'),
(298, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 20:34:34\",\"extension_minutes\":120,\"added_minutes\":30}', '2026-02-19 17:09:47'),
(299, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 21:04:34\",\"extension_minutes\":150,\"added_minutes\":30}', '2026-02-19 17:09:51'),
(300, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 21:34:34\",\"extension_minutes\":180,\"added_minutes\":30}', '2026-02-19 17:10:19'),
(301, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 22:04:34\",\"extension_minutes\":210,\"added_minutes\":30}', '2026-02-19 17:10:59'),
(302, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 22:34:34\",\"extension_minutes\":240,\"added_minutes\":30}', '2026-02-19 17:11:09'),
(303, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 23:04:34\",\"extension_minutes\":270,\"added_minutes\":30}', '2026-02-19 17:11:52'),
(304, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-19 23:34:34\",\"extension_minutes\":300,\"added_minutes\":30}', '2026-02-19 17:11:54'),
(305, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-20 00:04:34\",\"extension_minutes\":330,\"added_minutes\":30}', '2026-02-19 17:11:55'),
(306, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-20 00:34:34\",\"extension_minutes\":360,\"added_minutes\":30}', '2026-02-19 17:11:56'),
(307, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-20 01:04:34\",\"extension_minutes\":390,\"added_minutes\":30}', '2026-02-19 17:12:06'),
(308, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-20 01:34:34\",\"extension_minutes\":420,\"added_minutes\":30}', '2026-02-19 17:12:09'),
(309, 7, 'all', 'message_sent', '{\"message_id\":4,\"sender_type\":\"admin\",\"sender_participant_id\":null,\"target_type\":\"public\",\"target_participant_id\":null,\"body\":\"test\",\"created_at\":\"2026-02-19 17:12:21\"}', '2026-02-19 17:12:21'),
(310, 7, 'all', 'session_extended', '{\"session_id\":7,\"deadline_at\":\"2026-02-20 02:04:34\",\"extension_minutes\":450,\"added_minutes\":30}', '2026-02-19 17:12:23'),
(311, 7, 'all', 'session_ended', '{\"session_id\":7,\"ended_at\":\"2026-02-19 17:12:58\",\"reason\":\"manual\"}', '2026-02-19 17:12:58');

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

--
-- Dumping data untuk tabel `materials`
--

INSERT INTO `materials` (`id`, `title`, `type`, `text_content`, `created_by_admin_id`, `created_at`, `updated_at`) VALUES
(1, 'test', 'folder', 'Test 1\r\nTest 2\r\nTest 3', 1, '2026-02-07 12:41:14', '2026-02-07 14:17:09');

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

--
-- Dumping data untuk tabel `material_files`
--

INSERT INTO `material_files` (`id`, `material_id`, `sort_order`, `filename`, `mime`, `size`, `url_path`, `preview_url_path`, `cover_url_path`, `created_at`) VALUES
(25, 1, 1, 'Koordinator Bk Module — Sib‑k (code Igniter 4).docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 24133, '/uploads/materials/1770443484_7bb6c3e9dab7e0a293ae.docx', '/uploads/materials/1770443484_7bb6c3e9dab7e0a293ae.pdf', NULL, '2026-02-07 12:51:26'),
(26, 1, 2, 'Important Zoom Settings Update.mp4', 'video/mp4', 24851138, '/uploads/materials/1770443529_dfd9810631d40d014d2f.mp4', NULL, NULL, '2026-02-07 12:52:09'),
(27, 1, 3, 'Harpy Hare(MP3_320K).mp3', 'audio/mpeg', 7852960, '/uploads/materials/1770443578_285483487239a55af42d.mp3', NULL, NULL, '2026-02-07 12:52:58'),
(28, 1, 4, 'tugas.png', 'image/png', 1075522, '/uploads/materials/1770448629_a764c66c57834fdb94d0.png', NULL, NULL, '2026-02-07 14:17:09');

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

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id`, `session_id`, `sender_type`, `sender_admin_id`, `sender_participant_id`, `target_type`, `target_participant_id`, `body`, `created_at`) VALUES
(1, 1, 'admin', 1, NULL, 'public', NULL, 'test', '2026-02-01 02:02:43'),
(2, 2, 'student', NULL, 3, 'private_admin', NULL, 'test', '2026-02-01 02:20:48'),
(3, 2, 'student', NULL, 3, 'private_admin', NULL, 'test', '2026-02-01 02:20:57'),
(4, 7, 'admin', 1, NULL, 'public', NULL, 'test', '2026-02-19 17:12:21');

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
(14, '2026-02-19-000011', 'App\\Database\\Migrations\\AddSessionTimeLimit', 'default', 'App', 1771495419, 4);

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
  `left_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `participants`
--

INSERT INTO `participants` (`id`, `session_id`, `student_name`, `class_name`, `device_label`, `device_key`, `ip_address`, `mic_on`, `speaker_on`, `joined_at`, `last_seen_at`, `left_at`) VALUES
(1, 1, 'Test Siswa1', '10 A', NULL, NULL, '192.168.101.5', 1, 1, '2026-02-01 02:11:14', '2026-02-01 02:11:14', NULL),
(2, 2, 'test 2', '10B', 'Test pc', NULL, '192.168.101.5', 0, 1, '2026-02-01 02:19:23', '2026-02-01 02:19:35', NULL),
(3, 2, 'test 3', '10B', 'Test pc', NULL, '192.168.101.5', 0, 1, '2026-02-01 02:20:07', '2026-02-01 02:21:21', NULL),
(4, 5, 'test', 'test', 'Komputer Lab 15', '203f4fbeacbc312707eece378ed5f0fe', '192.168.101.15', 0, 1, '2026-02-07 12:53:53', '2026-02-07 15:15:59', NULL);

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

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `name`, `is_active`, `started_at`, `ended_at`, `created_by_admin_id`, `created_at`, `duration_limit_minutes`, `deadline_at`, `extension_minutes`) VALUES
(1, 'Test', 0, '2026-02-01 02:01:41', '2026-02-01 02:16:24', 1, '2026-02-01 02:01:41', NULL, NULL, 0),
(2, 'test2', 0, '2026-02-01 02:16:49', '2026-02-01 02:21:21', 1, '2026-02-01 02:16:49', NULL, NULL, 0),
(3, 'Sesi 2026-02-01 02:51', 0, '2026-02-01 02:51:07', '2026-02-01 02:52:13', 1, '2026-02-01 02:51:07', NULL, NULL, 0),
(4, 'Sesi 2026-02-07 11:04', 0, '2026-02-07 11:04:07', '2026-02-07 11:40:35', 1, '2026-02-07 11:04:07', NULL, NULL, 0),
(5, 'Sesi 2026-02-07 12:36', 0, '2026-02-07 12:36:05', '2026-02-19 16:20:29', 1, '2026-02-07 12:36:05', NULL, NULL, 0),
(6, 'Sesi 2026-02-19 17:03', 0, '2026-02-19 17:03:44', '2026-02-19 17:04:28', 1, '2026-02-19 17:03:44', 90, '2026-02-19 18:33:44', 0),
(7, 'Sesi 2026-02-19 17:04', 0, '2026-02-19 17:04:34', '2026-02-19 17:12:58', 1, '2026-02-19 17:04:34', 90, '2026-02-20 02:04:34', 450);

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
  `updated_at` datetime DEFAULT NULL,
  `allow_student_mic` tinyint(1) DEFAULT '1',
  `allow_student_speaker` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `session_state`
--

INSERT INTO `session_state` (`session_id`, `current_material_id`, `current_material_file_id`, `current_material_text_index`, `broadcast_text`, `updated_at`, `allow_student_mic`, `allow_student_speaker`) VALUES
(0, NULL, NULL, NULL, NULL, '2026-02-19 16:58:44', 1, 1),
(1, NULL, NULL, NULL, 'Test', '2026-02-01 02:04:24', 1, 1),
(2, NULL, NULL, NULL, 'test', '2026-02-01 02:20:25', 1, 1),
(3, NULL, NULL, NULL, NULL, '2026-02-01 02:51:07', 1, 1),
(4, NULL, NULL, NULL, NULL, '2026-02-07 11:04:07', 1, 1),
(5, 1, 28, NULL, 'test', '2026-02-07 15:15:35', 1, 1),
(6, NULL, NULL, NULL, NULL, '2026-02-19 17:03:44', 1, 1),
(7, NULL, NULL, NULL, NULL, '2026-02-19 17:04:34', 1, 1);

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
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=312;

--
-- AUTO_INCREMENT untuk tabel `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `material_files`
--
ALTER TABLE `material_files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
