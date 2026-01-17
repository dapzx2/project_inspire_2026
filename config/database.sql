-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 17, 2026 at 06:00 AM
-- Server version: 8.0.30
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inspire_project`
--
CREATE DATABASE IF NOT EXISTS `db_inspire_project` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `db_inspire_project`;

-- --------------------------------------------------------
-- Drop existing tables (in reverse order due to foreign keys)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `perencanaan_studi`;
DROP TABLE IF EXISTS `transkrip`;
DROP TABLE IF EXISTS `krs`;
DROP TABLE IF EXISTS `pengumuman`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `mata_kuliah`;

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah` (must be created first for FK references)
--

CREATE TABLE `krs` (
  `id` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int NOT NULL DEFAULT '3',
  `jenis` varchar(20) DEFAULT 'Wajib',
  `kelas` varchar(5) DEFAULT 'A',
  `dosen1` varchar(150) DEFAULT NULL,
  `dosen2` varchar(150) DEFAULT NULL,
  `hari` varchar(20) NOT NULL,
  `jam_mulai` varchar(10) NOT NULL,
  `jam_selesai` varchar(10) NOT NULL,
  `nilai_huruf` varchar(2) DEFAULT NULL,
  `bobot` decimal(3,2) DEFAULT '0.00',
  `nilai_dosen1` decimal(5,2) DEFAULT NULL,
  `nilai_dosen2` decimal(5,2) DEFAULT NULL,
  `semester_krs` varchar(20) DEFAULT '20251',
  `status_krs` varchar(20) DEFAULT 'Disetujui',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `krs`
--

INSERT INTO `krs` (`id`, `nim`, `kode_mk`, `nama_mk`, `sks`, `jenis`, `kelas`, `dosen1`, `dosen2`, `hari`, `jam_mulai`, `jam_selesai`, `nilai_huruf`, `bobot`, `nilai_dosen1`, `nilai_dosen2`, `semester_krs`, `status_krs`, `created_at`) VALUES
(1, '220211060323', 'TIK2071', 'PRAKTIKUM TEKNOLOGI BASIS DATA', 1, 'Wajib', 'A', 'DIRKO GUSTAAFIANO SETYADHARMAPUTRA RUINDUNGAN ST, M.Eng', NULL, 'Jumat', '13:00', '15:30', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(2, '220211060323', 'TIK3011', 'PEMBELAJARAN MESIN', 3, 'Wajib', 'C', 'OKTAVIAN ABRAHAM LANTANG ST, MTI, Ph.D', NULL, 'Senin', '08:00', '10:30', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(3, '220211060323', 'TIK3021', 'PENGEMBANGAN GAME', 3, 'Wajib', 'C', 'Ir. SUMENGE TANGKAWAROUW GODION KAUNANG MT, Ph.D', NULL, 'Selasa', '08:00', '10:30', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(4, '220211060323', 'TIK3031', 'REALITAS TERTAMBAH DAN REALITAS MAYA', 3, 'Wajib', 'C', 'BRAVE ANGKASA SUGIARSO ST', 'WAHYUNI FITHRATUL ZALMI S.Kom., M.Kom', 'Kamis', '08:00', '10:30', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(5, '220211060323', 'TIK4030', 'SEMINAR DAN PRAKTEK PROFESIONAL', 3, 'Wajib', 'B', 'ALWIN MELKIE SAMBUL ST, M.Eng, Ph.D.', 'VICTOR TARIGAN M.Kom', 'Selasa', '13:00', '15:30', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(6, '220211060323', 'TIK4041', 'GRAFIKA KOMPUTER', 2, 'Wajib', 'B', 'RIZAL SENGKEY ST, MT', NULL, 'Rabu', '14:40', '16:20', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(7, '220211060323', 'TIK4051', 'ETIKA PROFESI', 2, 'Wajib', 'B', 'DRINGHUZEN JEKKE MAMAHIT ST, MT', NULL, 'Selasa', '15:30', '17:10', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(8, '220211060323', 'TIK4061', 'KRIPTOGRAFI', 2, 'Wajib', 'B', 'RENDY SYAHPUTRA S.Kom., M.Kom', NULL, 'Rabu', '13:00', '14:40', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(9, '220211060323', 'TIK1071', 'PROBABILITAS DAN STATISTIKA', 2, 'Wajib', 'A', 'KENNETH YOSUA R PALILINGAN ST, MT', NULL, 'Jumat', '10:30', '12:10', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(10, '220211060323', 'TIK1051', 'MATEMATIKA DISKRIT', 3, 'Wajib', 'A', 'Dr.Eng. SARY DIANE EKAWATI PATURUSI ST, M.Eng', 'PUJO HARI SAPUTRO S.Kom., M.T', 'Jumat', '08:00', '10:30', 'B', 3.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(11, '220211060323', 'TIK3022', 'RISET INFORMATIKA', 3, 'Wajib', 'A', 'JIMMY REAGEN ROBOT', NULL, 'Senin', '08:00', '10:30', 'C+', 2.50, 60.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(12, '220211060323', 'TIK3032', 'KEWIRAUSAHAAN', 2, 'Wajib', 'F', 'ARTHUR MOURITS RUMAGIT', NULL, 'Senin', '10:30', '12:10', 'A', 4.00, 84.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(13, '220211060323', 'TIK3042', 'TOPIK KHUSUS TEKNIK INFORMATIKA', 2, 'Wajib', 'E', 'KENNETH YOSUA R PALILINGAN', NULL, 'Selasa', '08:00', '09:40', 'B+', 3.50, 78.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(14, '220211060323', 'TIK3052', 'KECAKAPAN ANTAR PERSONAL', 2, 'Wajib', 'D', 'ADE YUSUPA', NULL, 'Selasa', '10:30', '12:10', 'A', 4.00, 88.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(15, '220211060323', 'TIK3062', 'KEAMANAN SIBER', 3, 'Wajib', 'B', 'HEILBERT ARMANDO MAPALY', NULL, 'Rabu', '08:00', '10:30', 'A', 4.00, 90.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(16, '220211060323', 'TIK3072', 'PRAKTIKUM KEAMANAN SIBER', 1, 'Wajib', 'B', 'HEILBERT ARMANDO MAPALY', NULL, 'Rabu', '13:00', '15:30', 'B+', 3.50, 76.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(17, '220211060323', 'TIK3132', 'DATA MINING', 2, 'Wajib', 'A', 'OKTAVIAN ABRAHAM LANTANG', NULL, 'Kamis', '08:00', '09:40', 'A', 4.00, 90.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(18, '220211060323', 'TIK3182', 'PENGEMBANGAN APLIKASI DESKTOP', 2, 'Wajib', 'A', 'DIRKO GUSTAAFIANO SETYADHARMAPUTRA RUINDUNGAN', NULL, 'Kamis', '10:30', '12:10', 'A', 4.00, 82.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(19, '220211060323', 'TIK3192', 'PENGEMBANGAN APLIKASI WEB BERBASIS FRAMEWORK', 2, 'Wajib', 'A', 'XAVERIUS B.N. NAJOAN', NULL, 'Jumat', '08:00', '09:40', 'C', 2.00, 60.10, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(20, '220211060323', 'TIK1041', 'KEWARGANEGARAAN', 2, 'Wajib', 'D', 'ADE YUSUPA', NULL, 'Senin', '08:00', '09:40', 'B+', 3.50, 78.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(21, '220211060323', 'TIK2041', 'STRUKTUR DATA', 3, 'Wajib', 'A', 'ALWIN MELKIE SAMBUL', 'VICTOR TARIGAN', 'Senin', '10:30', '13:00', 'A', 4.00, 100.00, 72.00, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(22, '220211060323', 'TIK3011', 'PEMBELAJARAN MESIN', 3, 'Wajib', 'B', 'SHERWIN REINALDO U ALDO SOMPIE', NULL, 'Selasa', '08:00', '10:30', 'D', 1.00, 53.20, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(23, '220211060323', 'TIK3021', 'PENGEMBANGAN GAME', 3, 'Wajib', 'E', 'ADE YUSUPA', NULL, 'Selasa', '10:30', '13:00', 'E', 0.00, 3.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(24, '220211060323', 'TIK3031', 'REALITAS TERTAMBAH DAN REALITAS MAYA', 3, 'Wajib', 'B', 'JIMMY REAGEN ROBOT', NULL, 'Rabu', '08:00', '10:30', 'E', 0.00, 0.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(25, '220211060323', 'TIK3041', 'KOMPUTASI AWAN', 2, 'Wajib', 'D', 'ARTHUR MOURITS RUMAGIT', 'PUJO HARI SAPUTRO', 'Rabu', '10:30', '12:10', 'B', 3.00, 73.00, 73.00, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(26, '220211060323', 'TIK3051', 'SISTEM MULTIMEDIA', 3, 'Wajib', 'B', 'JIMMY REAGEN ROBOT', NULL, 'Kamis', '08:00', '10:30', 'B+', 3.50, 79.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(27, '220211060323', 'TIK3061', 'PRAKTIKUM SISTEM MULTIMEDIA', 1, 'Wajib', 'B', 'JIMMY REAGEN ROBOT', NULL, 'Kamis', '13:00', '15:30', 'B+', 3.50, 79.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(28, '220211060323', 'TIK2012', 'INTERAKSI MANUSIA DAN KOMPUTER', 4, 'Wajib', 'G', 'VIRGINIA TULENAN', NULL, 'Senin', '08:00', '11:20', 'A', 4.00, 80.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(29, '220211060323', 'TIK2022', 'KECERDASAN BUATAN', 2, 'Wajib', 'G', 'MUHAMAD DWISNANTO PUTRO', 'RENDY SYAHPUTRA', 'Senin', '13:00', '14:40', 'A', 4.00, 81.00, 81.00, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(30, '220211060323', 'TIK2032', 'PEMROGRAMAN WEB', 3, 'Wajib', 'G', 'SHERWIN REINALDO U ALDO SOMPIE', 'VICTOR TARIGAN', 'Selasa', '08:00', '10:30', 'D', 1.00, 54.59, 54.69, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(31, '220211060323', 'TIK2042', 'PEMODELAN DAN SIMULASI KOMPUTER', 3, 'Wajib', 'G', 'OKTAVIAN ABRAHAM LANTANG', 'WAHYUNI FITHRATUL ZALMI', 'Selasa', '10:30', '13:00', 'D', 1.00, 56.55, 56.50, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(32, '220211060323', 'TIK2052', 'PENGOLAHAN CITRA DIGITAL', 3, 'Wajib', 'G', 'ARTHUR MOURITS RUMAGIT', 'PUJO HARI SAPUTRO', 'Rabu', '08:00', '10:30', 'B+', 3.50, 76.00, 76.00, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(33, '220211060323', 'TIK2062', 'JARINGAN DAN KOMUNIKASI DATA', 3, 'Wajib', 'G', 'SALVIUS PAULUS LENGKONG', NULL, 'Kamis', '08:00', '10:30', 'A', 4.00, 80.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(34, '220211060323', 'TIK2072', 'PRAKTIKUM JARINGAN DAN KOMUNIKASI DATA', 1, 'Wajib', 'G', 'SALVIUS PAULUS LENGKONG', NULL, 'Kamis', '13:00', '15:30', 'E', 0.00, 40.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(35, '220211060323', 'TIK1071', 'PROBABILITAS DAN STATISTIKA', 2, 'Wajib', 'E', 'KENNETH YOSUA R PALILINGAN', NULL, 'Senin', '08:00', '09:40', 'C', 2.00, 50.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(36, '220211060323', 'TIK2011', 'SISTEM INFORMASI', 3, 'Wajib', 'H', 'FRANSISCA JOANET PONTOH', NULL, 'Senin', '10:30', '13:00', 'B', 3.00, 70.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(37, '220211060323', 'TIK2021', 'ARSITEKTUR DAN ORGANISASI KOMPUTER', 3, 'Wajib', 'H', 'HARNI SEVEN ADINATA', NULL, 'Selasa', '08:00', '10:30', 'A', 4.00, 85.20, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(38, '220211060323', 'TIK2031', 'SISTEM OPERASI', 3, 'Wajib', 'H', 'BERNAD JUMADI DEHOTMAN SITOMPUL', NULL, 'Selasa', '10:30', '13:00', 'B', 3.00, 68.23, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(39, '220211060323', 'TIK2051', 'REKAYASA PERANGKAT LUNAK', 3, 'Wajib', 'H', 'REINHARD KOMANSILAN', NULL, 'Rabu', '08:00', '10:30', 'A', 4.00, 91.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(40, '220211060323', 'TIK2061', 'TEKNOLOGI BASIS DATA', 3, 'Wajib', 'A', 'DIRKO GUSTAAFIANO SETYADHARMAPUTRA RUINDUNGAN', NULL, 'Kamis', '08:00', '10:30', 'A', 4.00, 82.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(41, '220211060323', 'TIK1012', 'BAHASA INGGRIS', 2, 'Wajib', 'C', 'REINHARD KOMANSILAN', NULL, 'Senin', '08:00', '09:40', 'C', 2.00, 62.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(42, '220211060323', 'TIK1022', 'PENGETAHUAN KEPASIFIKAN', 2, 'Wajib', 'C', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Senin', '10:30', '12:10', 'A', 4.00, 82.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(43, '220211060323', 'TIK1032', 'PENGANTAR TEKNIK INFORMATIKA', 2, 'Wajib', 'C', 'HANS FREDRIK WOWOR', NULL, 'Selasa', '08:00', '09:40', 'C', 2.00, 62.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(44, '220211060323', 'TIK1042', 'ALJABAR LINEAR', 2, 'Wajib', 'C', 'ARIE SALMON MATIUS LUMENTA', NULL, 'Selasa', '10:30', '12:10', 'E', 0.00, 19.58, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(45, '220211060323', 'TIK1052', 'METODE NUMERIK', 3, 'Wajib', 'C', 'ARIE SALMON MATIUS LUMENTA', NULL, 'Rabu', '08:00', '10:30', 'E', 0.00, 45.71, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(46, '220211060323', 'TIK1072', 'KOMPUTER DAN MASYARAKAT', 2, 'Wajib', 'C', 'NANCY JEANE TUTUROONG', NULL, 'Rabu', '10:30', '12:10', 'B+', 3.50, 77.50, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(47, '220211060323', 'TIK1082', 'BASIS DATA', 3, 'Wajib', 'C', 'HANS FREDRIK WOWOR', NULL, 'Kamis', '08:00', '10:30', 'B', 3.00, 75.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(48, '220211060323', 'TIK1092', 'PRAKTIKUM BASIS DATA', 1, 'Wajib', 'C', 'HANS FREDRIK WOWOR', NULL, 'Kamis', '13:00', '15:30', 'E', 0.00, 4.60, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(49, '220211060323', 'TIK1101', 'PENDIDIKAN AGAMA KRISTEN PROTESTAN', 2, 'Wajib', 'A', 'Ferta Dina Pontoh', 'ALWIN MELKIE SAMBUL', 'Senin', '08:00', '09:40', 'B', 3.00, 70.00, 70.00, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(50, '220211060323', 'TIK1021', 'PANCASILA', 2, 'Wajib', 'A', 'PINROLINVIC DUADELFRI KURNIALIMKI MANEMBU', 'MARKUS KARAMOY UMBOH', 'Senin', '10:30', '12:10', 'B+', 3.50, 83.00, 72.00, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(51, '220211060323', 'TIK1031', 'BAHASA INDONESIA', 2, 'Wajib', 'A', 'DRINGHUZEN JEKKE MAMAHIT', NULL, 'Selasa', '08:00', '09:40', 'A', 4.00, 95.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(52, '220211060323', 'TIK1041', 'KEWARGANEGARAAN', 2, 'Wajib', 'A', 'VIRGINIA TULENAN', 'BRAVE ANGKASA SUGIARSO', 'Selasa', '10:30', '12:10', 'D', 1.00, 50.00, 50.00, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(53, '220211060323', 'TIK1051', 'MATEMATIKA DISKRIT', 3, 'Wajib', 'A', 'PINROLINVIC DUADELFRI KURNIALIMKI MANEMBU', NULL, 'Rabu', '08:00', '10:30', 'E', 0.00, 40.40, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(54, '220211060323', 'TIK1061', 'KALKULUS', 3, 'Wajib', 'A', 'DANIEL FEBRIAN SENGKEY', NULL, 'Rabu', '10:30', '13:00', 'C+', 2.50, 68.69, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(55, '220211060323', 'TIK1071', 'PROBABILITAS DAN STATISTIKA', 2, 'Wajib', 'A', 'DANIEL FEBRIAN SENGKEY', NULL, 'Kamis', '08:00', '09:40', 'E', 0.00, 0.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(56, '220211060323', 'TIK1081', 'ALGORITMA DAN PEMROGRAMAN KOMPUTER', 4, 'Wajib', 'A', 'RIZAL SENGKEY', NULL, 'Kamis', '10:30', '14:00', 'C+', 2.50, 66.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(57, '220211060323', 'TIK1091', 'PRAK. ALGORITMA DAN PEMROGRAMAN KOMPUTER', 1, 'Wajib', 'A', 'SHERWIN REINALDO U ALDO SOMPIE', NULL, 'Jumat', '08:00', '10:30', 'C+', 2.50, 69.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(58, '220211060242', 'TIK1101', 'PENDIDIKAN AGAMA KRISTEN PROTESTAN', 2, 'Wajib', 'A', 'Ferta Dina Pontoh', 'ALWIN MELKIE SAMBUL', 'Senin', '08:00', '09:40', 'A', 4.00, 85.00, 85.00, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(59, '220211060242', 'TIK1021', 'PANCASILA', 2, 'Wajib', 'D', 'FRANSISCA JOANET PONTOH', NULL, 'Senin', '10:30', '12:10', 'A', 4.00, 94.29, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(60, '220211060242', 'TIK1031', 'BAHASA INDONESIA', 2, 'Wajib', 'D', 'SARTJE SILIMANG', NULL, 'Selasa', '08:00', '09:40', 'A', 4.00, 90.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(61, '220211060242', 'TIK1041', 'KEWARGANEGARAAN', 2, 'Wajib', 'D', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Selasa', '10:30', '12:10', 'B+', 3.50, 78.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(62, '220211060242', 'TIK1051', 'MATEMATIKA DISKRIT', 3, 'Wajib', 'D', 'DANIEL FEBRIAN SENGKEY', NULL, 'Rabu', '08:00', '10:30', 'C', 2.00, 62.17, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(63, '220211060242', 'TIK1061', 'KALKULUS', 3, 'Wajib', 'D', 'ABDUL HARIS JUNUS ONTOWIRJO', NULL, 'Rabu', '10:30', '13:00', 'B+', 3.50, 79.86, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(64, '220211060242', 'TIK1071', 'PROBABILITAS DAN STATISTIKA', 2, 'Wajib', 'D', 'HEILBERT ARMANDO MAPALY', NULL, 'Kamis', '08:00', '09:40', 'C', 2.00, 63.39, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(65, '220211060242', 'TIK1081', 'ALGORITMA DAN PEMROGRAMAN KOMPUTER', 4, 'Wajib', 'D', 'HANS FREDRIK WOWOR', NULL, 'Kamis', '10:30', '14:00', 'C+', 2.50, 66.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(66, '220211060242', 'TIK1091', 'PRAK. ALGORITMA DAN PEMROGRAMAN KOMPUTER', 1, 'Wajib', 'B', 'REINHARD KOMANSILAN', NULL, 'Jumat', '08:00', '10:30', 'A', 4.00, 93.00, NULL, '20221', 'Disetujui', '2026-01-15 17:49:45'),
(67, '220211060242', 'TIK1012', 'BAHASA INGGRIS', 2, 'Wajib', 'G', 'SARY DIANE EKAWATI PATURUSI', 'WAHYUNI FITHRATUL ZALMI', 'Senin', '08:00', '09:40', 'B', 3.00, 75.00, 70.00, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(68, '220211060242', 'TIK1022', 'PENGETAHUAN KEPASIFIKAN', 2, 'Wajib', 'G', 'SARTJE SILIMANG', NULL, 'Senin', '10:30', '12:10', 'A', 4.00, 80.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(69, '220211060242', 'TIK1032', 'PENGANTAR TEKNIK INFORMATIKA', 2, 'Wajib', 'G', 'YAULIE DEO Y RINDENGAN', NULL, 'Selasa', '08:00', '09:40', 'A', 4.00, 83.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(70, '220211060242', 'TIK1042', 'ALJABAR LINEAR', 2, 'Wajib', 'G', 'JANE I LITOUW', NULL, 'Selasa', '10:30', '12:10', 'B+', 3.50, 78.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(71, '220211060242', 'TIK1052', 'METODE NUMERIK', 3, 'Wajib', 'G', 'ABDUL HARIS JUNUS ONTOWIRJO', NULL, 'Rabu', '08:00', '10:30', 'E', 0.00, 40.57, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(72, '220211060242', 'TIK1062', 'TEORI BAHASA DAN AUTOMATA', 3, 'Wajib', 'G', 'REINHARD KOMANSILAN', NULL, 'Rabu', '10:30', '13:00', 'A', 4.00, 90.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(73, '220211060242', 'TIK1072', 'KOMPUTER DAN MASYARAKAT', 2, 'Wajib', 'G', 'BERNAD JUMADI DEHOTMAN SITOMPUL', NULL, 'Kamis', '08:00', '09:40', 'B', 3.00, 75.18, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(74, '220211060242', 'TIK1082', 'BASIS DATA', 3, 'Wajib', 'G', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Kamis', '10:30', '13:00', 'B', 3.00, 73.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(75, '220211060242', 'TIK1092', 'PRAKTIKUM BASIS DATA', 1, 'Wajib', 'G', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Jumat', '08:00', '10:30', 'A', 4.00, 80.00, NULL, '20222', 'Disetujui', '2026-01-15 17:49:45'),
(76, '220211060242', 'TIK2011', 'SISTEM INFORMASI', 3, 'Wajib', 'F', 'SALVIUS PAULUS LENGKONG', NULL, 'Senin', '08:00', '10:30', 'B', 3.00, 74.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(77, '220211060242', 'TIK2021', 'ARSITEKTUR DAN ORGANISASI KOMPUTER', 3, 'Wajib', 'F', 'YURI VANLI AKAY', NULL, 'Senin', '10:30', '13:00', 'A', 4.00, 80.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(78, '220211060242', 'TIK2031', 'SISTEM OPERASI', 3, 'Wajib', 'F', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Selasa', '08:00', '10:30', 'B', 3.00, 70.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(79, '220211060242', 'TIK2041', 'STRUKTUR DATA', 3, 'Wajib', 'F', 'OKTAVIAN ABRAHAM LANTANG', NULL, 'Selasa', '10:30', '13:00', 'A', 4.00, 80.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(80, '220211060242', 'TIK2051', 'REKAYASA PERANGKAT LUNAK', 3, 'Wajib', 'F', 'XAVERIUS B.N. NAJOAN', 'PUJO HARI SAPUTRO', 'Rabu', '08:00', '10:30', 'A', 4.00, 80.00, 80.00, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(81, '220211060242', 'TIK2061', 'TEKNOLOGI BASIS DATA', 3, 'Wajib', 'G', 'OKTAVIAN ABRAHAM LANTANG', 'VICTOR TARIGAN', 'Rabu', '10:30', '13:00', 'C', 2.00, 60.35, 60.30, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(82, '220211060242', 'TIK2071', 'PRAKTIKUM TEKNOLOGI BASIS DATA', 1, 'Wajib', 'C', 'HENRY VALENTINO FLORENSIUS KAINDE', NULL, 'Kamis', '08:00', '10:30', 'A', 4.00, 89.60, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(83, '220211060242', 'TIK3041', 'KOMPUTASI AWAN', 2, 'Pilihan', 'E', 'REINHARD KOMANSILAN', NULL, 'Kamis', '10:30', '12:10', 'A', 4.00, 80.00, NULL, '20231', 'Disetujui', '2026-01-15 17:49:45'),
(84, '220211060242', 'TIK2012', 'INTERAKSI MANUSIA DAN KOMPUTER', 4, 'Wajib', 'G', 'VIRGINIA TULENAN', NULL, 'Senin', '08:00', '11:30', 'A', 4.00, 80.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(85, '220211060242', 'TIK2022', 'KECERDASAN BUATAN', 2, 'Wajib', 'G', 'MUHAMAD DWISNANTO PUTRO', 'RENDY SYAHPUTRA', 'Senin', '13:00', '14:40', 'A', 4.00, 81.00, 81.00, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(86, '220211060242', 'TIK2032', 'PEMROGRAMAN WEB', 3, 'Wajib', 'G', 'SHERWIN REINALDO U ALDO SOMPIE', 'VICTOR TARIGAN', 'Selasa', '08:00', '10:30', 'B', 3.00, 75.51, 75.51, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(87, '220211060242', 'TIK2042', 'PEMODELAN DAN SIMULASI KOMPUTER', 3, 'Wajib', 'G', 'OKTAVIAN ABRAHAM LANTANG', 'WAHYUNI FITHRATUL ZALMI', 'Selasa', '10:30', '13:00', 'A', 4.00, 94.05, 94.00, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(88, '220211060242', 'TIK2052', 'PENGOLAHAN CITRA DIGITAL', 3, 'Wajib', 'G', 'ARTHUR MOURITS RUMAGIT', 'PUJO HARI SAPUTRO', 'Rabu', '08:00', '10:30', 'A', 4.00, 87.00, 87.00, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(89, '220211060242', 'TIK2062', 'JARINGAN DAN KOMUNIKASI DATA', 3, 'Wajib', 'G', 'SALVIUS PAULUS LENGKONG', NULL, 'Rabu', '10:30', '13:00', 'B+', 3.50, 77.10, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(90, '220211060242', 'TIK2072', 'PRAKTIKUM JARINGAN DAN KOMUNIKASI DATA', 1, 'Wajib', 'G', 'SALVIUS PAULUS LENGKONG', NULL, 'Rabu', '13:00', '15:30', 'B', 3.00, 75.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(91, '220211060242', 'TIK3032', 'KEWIRAUSAHAAN', 2, 'Wajib', 'F', 'ARTHUR MOURITS RUMAGIT', NULL, 'Kamis', '08:00', '09:40', 'A', 4.00, 85.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(92, '220211060242', 'TIK3052', 'KECAKAPAN ANTAR PERSONAL', 2, 'Wajib', 'G', 'SALVIUS PAULUS LENGKONG', NULL, 'Kamis', '10:30', '12:10', 'A', 4.00, 88.00, NULL, '20232', 'Disetujui', '2026-01-15 17:49:45'),
(93, '220211060242', 'TIK3011', 'PEMBELAJARAN MESIN', 3, 'Wajib', 'C', 'FRANSISCA JOANET PONTOH', NULL, 'Senin', '08:00', '10:30', 'B+', 3.50, 79.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(94, '220211060242', 'TIK3021', 'PENGEMBANGAN GAME', 3, 'Wajib', 'C', 'SUMENGE TANGKAWAROUW GODION KAUNANG', NULL, 'Senin', '10:30', '13:00', 'A', 4.00, 90.40, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(95, '220211060242', 'TIK3031', 'REALITAS TERTAMBAH DAN REALITAS MAYA', 3, 'Wajib', 'B', 'JIMMY REAGEN ROBOT', NULL, 'Selasa', '08:00', '10:30', 'B+', 3.50, 76.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(96, '220211060242', 'TIK3051', 'SISTEM MULTIMEDIA', 3, 'Wajib', 'H', 'YURI VANLI AKAY', NULL, 'Selasa', '10:30', '13:00', 'D', 1.00, 53.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(97, '220211060242', 'TIK3061', 'PRAKTIKUM SISTEM MULTIMEDIA', 1, 'Wajib', 'H', 'YURI VANLI AKAY', NULL, 'Selasa', '13:00', '15:30', 'E', 0.00, 46.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(98, '220211060242', 'TIK4041', 'GRAFIKA KOMPUTER', 2, 'Wajib', 'C', 'NANCY JEANE TUTUROONG', NULL, 'Rabu', '08:00', '09:40', 'A', 4.00, 85.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(99, '220211060242', 'TIK4061', 'KRIPTOGRAFI', 2, 'Wajib', 'E', 'HEILBERT ARMANDO MAPALY', NULL, 'Rabu', '10:30', '12:10', 'A', 4.00, 82.60, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(100, '220211060242', 'TIK3151', 'TEKNIK PERUTEAN JARINGAN', 2, 'Pilihan', 'A', 'MEICSY ELDAD ISRAEL NAJOAN', NULL, 'Kamis', '08:00', '09:40', 'A', 4.00, 81.50, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(101, '220211060242', 'TIK3161', 'TEKNIK ADMINISTRASI SERVER', 2, 'Pilihan', 'A', 'DANIEL FEBRIAN SENGKEY', NULL, 'Kamis', '10:30', '12:10', 'D', 1.00, 50.00, NULL, '20241', 'Disetujui', '2026-01-15 17:49:45'),
(102, '220211060242', 'TIK3012', 'BIOINFORMATIKA', 3, 'Wajib', 'A', 'DANIEL FEBRIAN SENGKEY', NULL, 'Senin', '08:00', '10:30', 'B+', 3.50, 76.92, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(103, '220211060242', 'TIK3022', 'RISET INFORMATIKA', 3, 'Wajib', 'A', 'JIMMY REAGEN ROBOT', NULL, 'Senin', '10:30', '13:00', 'C', 2.00, 60.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(104, '220211060242', 'TIK3072', 'PRAKTIKUM KEAMANAN SIBER', 1, 'Wajib', 'B', 'HEILBERT ARMANDO MAPALY', NULL, 'Selasa', '08:00', '10:30', 'A', 4.00, 86.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(105, '220211060242', 'TIK3162', 'PEMROGRAMAN JARINGAN', 2, 'Wajib', 'A', 'XAVERIUS B.N. NAJOAN', NULL, 'Selasa', '10:30', '12:10', 'B', 3.00, 70.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(106, '220211060242', 'TIK4030', 'SEMINAR DAN PRAKTEK PROFESIONAL', 3, 'Wajib', 'B', 'SARY DIANE EKAWATI PATURUSI', 'REINHARD KOMANSILAN', 'Rabu', '08:00', '10:30', 'B', 3.00, 70.00, 70.00, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(107, '220211060242', 'TIK1052', 'METODE NUMERIK', 3, 'Wajib', 'E', 'KENNETH YOSUA R PALILINGAN', NULL, 'Rabu', '13:00', '15:30', 'C', 2.00, 60.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(108, '220211060242', 'TIK3042', 'TOPIK KHUSUS TEKNIK INFORMATIKA', 2, 'Wajib', 'C', 'SARY DIANE EKAWATI PATURUSI', NULL, 'Kamis', '08:00', '09:40', 'A', 4.00, 85.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(109, '220211060242', 'TIK3062', 'KEAMANAN SIBER', 3, 'Wajib', 'B', 'HEILBERT ARMANDO MAPALY', NULL, 'Kamis', '10:30', '13:00', 'A', 4.00, 92.00, NULL, '20242', 'Disetujui', '2026-01-15 17:49:45'),
(110, '220211060242', 'TIK3051', 'SISTEM MULTIMEDIA', 3, 'Wajib', 'A', 'BRAVE ANGKASA SUGIARSO', 'ADE YUSUPA', 'Senin', '08:00', '10:30', 'D', 1.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(111, '220211060242', 'TIK3061', 'PRAKTIKUM SISTEM MULTIMEDIA', 1, 'Wajib', 'A', 'BRAVE ANGKASA SUGIARSO', 'ADE YUSUPA', 'Senin', '13:00', '15:30', 'C', 2.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(112, '220211060242', 'TIK4010', 'KKT', 4, 'Wajib', 'A', 'VIRGINIA TULENAN', NULL, 'Selasa', '08:00', '12:00', 'A', 4.00, 0.00, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(113, '220211060242', 'TIK4020', 'MAGANG', 3, 'Wajib', 'A', 'VIRGINIA TULENAN', NULL, 'Rabu', '08:00', '11:00', 'N', 0.00, 0.00, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45'),
(114, '220211060242', 'TIK4051', 'ETIKA PROFESI', 2, 'Wajib', 'A', 'YAULIE DEO Y RINDENGAN', NULL, 'Kamis', '08:00', '09:40', 'A', 4.00, NULL, NULL, '20251', 'Disetujui', '2026-01-15 17:49:45');

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id` int NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int NOT NULL DEFAULT '3',
  `semester` int DEFAULT '1',
  `jenis` enum('wajib','pilihan') DEFAULT 'wajib',
  `kategori` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id`, `kode_mk`, `nama_mk`, `sks`, `semester`, `jenis`, `kategori`, `created_at`) VALUES
(1, 'TIK1011', 'Pendidikan Agama', 2, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(2, 'TIK1021', 'Pancasila', 2, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(3, 'TIK1031', 'Bahasa Indonesia', 2, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(4, 'TIK1041', 'Kewarganegaraan', 2, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(5, 'TIK1051', 'Matematika Diskrit', 3, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(6, 'TIK1061', 'Kalkulus', 3, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(7, 'TIK1071', 'Probabilitas dan Statistika', 2, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(8, 'TIK1081', 'Algoritma dan Pemrograman Komputer', 4, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(9, 'TIK1091', 'Prakt. Algoritma & Pemrograman Komputer', 1, 1, 'wajib', NULL, '2026-01-15 17:49:44'),
(10, 'TIK1012', 'Bahasa Inggris', 2, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(11, 'TIK1022', 'Pengetahuan Kepasifikan', 2, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(12, 'TIK1032', 'Pengantar Teknik Informatika', 2, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(13, 'TIK1042', 'Aljabar Linier', 2, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(14, 'TIK1052', 'Metode Numerik', 3, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(15, 'TIK1062', 'Teori Bahasa dan Automata', 3, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(16, 'TIK1072', 'Komputer dan Masyarakat', 2, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(17, 'TIK1082', 'Basis Data', 3, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(18, 'TIK1092', 'Praktikum Basis Data', 1, 2, 'wajib', NULL, '2026-01-15 17:49:44'),
(19, 'TIK2011', 'Sistem Informasi', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(20, 'TIK2021', 'Arsitektur dan Organisasi Komputer', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(21, 'TIK2031', 'Sistem Operasi', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(22, 'TIK2041', 'Struktur Data', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(23, 'TIK2051', 'Rekayasa Perangkat Lunak', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(24, 'TIK2061', 'Teknologi Basis Data', 3, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(25, 'TIK2071', 'Praktikum Teknologi Basis Data', 1, 3, 'wajib', NULL, '2026-01-15 17:49:44'),
(26, 'TIK2012', 'Interaksi Manusia dan Komputer', 4, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(27, 'TIK2022', 'Kecerdasan Buatan', 2, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(28, 'TIK2032', 'Pemrograman Web', 3, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(29, 'TIK2042', 'Pemodelan dan Simulasi Komputer', 3, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(30, 'TIK2052', 'Pengolahan Citra Digital', 3, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(31, 'TIK2062', 'Jaringan dan Komunikasi Data', 3, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(32, 'TIK2072', 'Praktikum Jaringan dan Komunikasi Data', 1, 4, 'wajib', NULL, '2026-01-15 17:49:44'),
(33, 'TIK3011', 'Pembelajaran Mesin', 3, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(34, 'TIK3021', 'Pengembangan Game', 3, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(35, 'TIK3031', 'Realitas Tertambah dan Realitas Maya', 3, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(36, 'TIK3041', 'Komputasi Awan', 2, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(37, 'TIK3051', 'Sistem Multimedia', 3, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(38, 'TIK3061', 'Praktikum Sistem Multimedia', 1, 5, 'wajib', NULL, '2026-01-15 17:49:44'),
(39, 'TIK3012', 'Bioinformatika', 3, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(40, 'TIK3022', 'Riset Informatika', 3, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(41, 'TIK3032', 'Kewirausahaan', 2, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(42, 'TIK3042', 'Topik Khusus Teknik Informatika', 2, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(43, 'TIK3052', 'Kecakapan Antar Personal', 2, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(44, 'TIK3062', 'Keamanan Siber', 3, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(45, 'TIK3072', 'Praktikum Keamanan Siber', 1, 6, 'wajib', NULL, '2026-01-15 17:49:44'),
(46, 'TIK4010', 'KKT', 4, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(47, 'TIK4020', 'Magang', 3, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(48, 'TIK4030', 'Seminar dan Praktek Profesional', 3, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(49, 'TIK4041', 'Grafika Komputer', 2, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(50, 'TIK4051', 'Etika Profesi', 2, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(51, 'TIK4061', 'Kriptografi', 2, 7, 'wajib', NULL, '2026-01-15 17:49:44'),
(52, 'TIK4040', 'Skripsi', 6, 8, 'wajib', NULL, '2026-01-15 17:49:44'),
(53, 'TIK3071', 'Sistem Pendukung Pengambilan Keputusan', 2, 5, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(54, 'TIK3081', 'Sistem Pakar', 2, 5, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(55, 'TIK3082', 'Visi Komputer', 2, 6, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(56, 'TIK3092', 'Jaringan Saraf Tiruan', 2, 6, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(57, 'TIK4042', 'Algoritma Genetik', 2, 7, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(58, 'TIK4052', 'Robotika Cerdas', 2, 7, 'pilihan', 'Artificial Intelligence', '2026-01-15 17:49:44'),
(59, 'TIK3091', 'Desain Eksperimen', 2, 5, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(60, 'TIK3101', 'Informatika Biomedis', 2, 5, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(61, 'TIK3102', 'Teknologi Pembelajaran Daring', 2, 6, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(62, 'TIK3112', 'Sistem Informasi Kesehatan', 2, 6, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(63, 'TIK4062', 'E-Sport', 2, 7, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(64, 'TIK4071', 'User Experience', 2, 7, 'pilihan', 'Human Informatics', '2026-01-15 17:49:44'),
(65, 'TIK3111', 'Manajemen Sistem Basis Data', 2, 5, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(66, 'TIK3121', 'Representasi Pengetahuan', 2, 5, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(67, 'TIK3122', 'Big Data', 2, 6, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(68, 'TIK3132', 'Data Mining', 2, 6, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(69, 'TIK4081', 'Information Retrieval', 2, 7, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(70, 'TIK4091', 'Semantic Web', 2, 7, 'pilihan', 'Big Data', '2026-01-15 17:49:44'),
(71, 'TIK3131', 'Teori Informasi', 2, 5, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(72, 'TIK3141', 'Manajemen Sistem Informasi Korporat', 2, 5, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(73, 'TIK3142', 'E-Bisnis', 2, 6, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(74, 'TIK3152', 'Sistem Informasi Geografis', 2, 6, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(75, 'TIK4101', 'E-Government', 2, 7, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(76, 'TIK4111', 'Audit Sistem Informasi', 2, 7, 'pilihan', 'Information System', '2026-01-15 17:49:44'),
(77, 'TIK3151', 'Teknik Perutean Jaringan', 2, 5, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(78, 'TIK3161', 'Teknik Administrasi Server', 2, 5, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(79, 'TIK3162', 'Pemrograman Jaringan', 2, 6, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(80, 'TIK3172', 'Komunikasi Data Nirkabel', 2, 6, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(81, 'TIK4121', 'Sistem Komunikasi Optik', 2, 7, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(82, 'TIK4131', 'Teknik Simulasi Jaringan', 2, 7, 'pilihan', 'Distributed System', '2026-01-15 17:49:44'),
(83, 'TIK3171', 'Pengembangan Aplikasi Mobile', 2, 5, 'pilihan', 'Software Development', '2026-01-15 17:49:44'),
(84, 'TIK3181', 'Aplikasi Berorientasi Service', 2, 5, 'pilihan', 'Software Development', '2026-01-15 17:49:44'),
(85, 'TIK3182', 'Pengembangan Aplikasi Desktop', 2, 6, 'pilihan', 'Software Development', '2026-01-15 17:49:44'),
(86, 'TIK3192', 'Pengembangan Aplikasi Web Berbasis Framework', 2, 6, 'pilihan', 'Software Development', '2026-01-15 17:49:44'),
(87, 'TIK4141', 'Manajemen Proyek Perangkat Lunak', 2, 7, 'pilihan', 'Software Development', '2026-01-15 17:49:44'),
(88, 'TIK4151', 'Kualitas Perangkat Lunak', 2, 7, 'pilihan', 'Software Development', '2026-01-15 17:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text,
  `kategori` varchar(50) DEFAULT NULL,
  `oleh` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'DOSEN MATAKULIAH',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `nim`, `judul`, `isi`, `kategori`, `oleh`, `role`, `created_at`) VALUES
(1, '220211060323', '[PENGEMBANGAN GAME ] Link Demo UAS', 'Demo UAS meet.google.com/zfy-suiy-bnn', 'Matakuliah', 'Ir. SUMENGE TANGKAWAROUW GODION KAUNANG MT, Ph.D', 'DOSEN MATAKULIAH', '2025-12-16 04:01:26'),
(2, '220211060323', '[REALITAS TERTAMBAH DAN REALITAS VIRTUAL]', '39SBJQ85', 'Matakuliah', 'WAHYUNI FITHRATUL ZALMI S.Kom., M.Kom', 'DOSEN MATAKULIAH', '2025-12-15 17:59:10'),
(3, '220211060323', '[KRIPTOGRAFI] Absensi UAS', '05HCHM85', 'Matakuliah', 'RENDY SYAHPUTRA S.Kom., M.Kom', 'DOSEN MATAKULIAH', '2025-12-11 19:03:27'),
(4, '220211060323', '[KRIPTOGRAFI] UAS', 'Berikut saya lampirkan UAS Kriptografi', 'Matakuliah', 'RENDY SYAHPUTRA S.Kom., M.Kom', 'DOSEN MATAKULIAH', '2025-12-10 01:08:04'),
(5, '220211060323', '[PENGEMBANGAN GAME ] Demo 1 UAS', 'Demo 1 UAS, 9 Desember 2025, 15.00 wita.', 'Matakuliah', 'Ir. SUMENGE TANGKAWAROUW GODION KAUNANG MT, Ph.D', 'DOSEN MATAKULIAH', '2025-12-08 22:53:22'),
(6, '220211060242', '[PRAKTIKUM SISTEM MULTIMEDIA] Link Grup WA', 'untuk MK Praks. Sistem Multimedia kelas A, silahkan gabung & undang teman2 lainnya di grup ini', 'Matakuliah', 'BRAVE ANGKASA SUGIARSO ST', 'DOSEN MATAKULIAH', '2025-09-10 20:45:33'),
(7, '220211060242', '[ETIKA PROFESI] Grup WA', 'Silakan masuk Grup WA', 'Matakuliah', 'YAULIE DEO Y RINDENGAN ST, M.Sc, MM', 'DOSEN MATAKULIAH', '2025-09-01 22:41:54'),
(8, '220211060242', '[SISTEM MULTIMEDIA] Gabung WAG', 'https://chat.whatsapp.com/KJ8AxEclvvg0JvcdCpykg5', 'Matakuliah', 'ADE YUSUPA S.Pd, M.Kom', 'DOSEN MATAKULIAH', '2025-08-24 17:47:40'),
(9, '220211060242', 'Selamat datang Mahasiswa Baru 2025', 'Selamat bergabung di keluarga besar Program Studi S1 Teknik Informatika, UNSRAT. Semoga perjalanan akademik kalian menyenangkan dan penuh prestasi!', 'Prodi', 'VIRGINIA TULENAN S.Kom, MTI', 'KORPRODI', '2025-08-05 16:40:59'),
(10, '220211060242', 'Kompetisi Bergengsi GEMASTIK 2024', 'CALLING ALL WARRIORS!!! Ajang PALING bergengsi sebagai Mahasiswa Teknik Informatika. GEMASTIK 2024 sudah dibuka! Ayo daftarkan timmu sekarang!', 'Prodi', 'VIRGINIA TULENAN S.Kom, MTI', 'KORPRODI', '2024-04-18 01:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `perencanaan_studi`
--

CREATE TABLE `perencanaan_studi` (
  `id` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `perencanaan_studi`
--

INSERT INTO `perencanaan_studi` (`id`, `nim`, `kode_mk`, `created_at`) VALUES
(27, '220211060323', 'TIK2072', '2026-01-16 09:14:44'),
(31, '220211060323', 'TIK3122', '2026-01-16 09:15:25'),
(32, '220211060323', 'TIK3142', '2026-01-16 09:15:28'),
(36, '220211060323', 'TIK1062', '2026-01-16 10:31:11'),
(37, '220211060323', 'TIK2042', '2026-01-16 10:31:13'),
(38, '220211060323', 'TIK2032', '2026-01-16 10:31:13'),
(39, '220211060323', 'TIK1092', '2026-01-16 10:31:14'),
(41, '220211060323', 'TIK1042', '2026-01-16 10:31:16'),
(42, '220211060323', 'TIK1052', '2026-01-16 10:33:36'),
(43, '220211060323', 'TIK3012', '2026-01-16 10:35:36');

-- --------------------------------------------------------

--
-- Table structure for table `transkrip`
--

CREATE TABLE `transkrip` (
  `id` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int NOT NULL DEFAULT '3',
  `nilai_huruf` varchar(2) NOT NULL,
  `bobot` decimal(3,2) NOT NULL,
  `semester` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transkrip`
--

INSERT INTO `transkrip` (`id`, `nim`, `kode_mk`, `nama_mk`, `sks`, `nilai_huruf`, `bobot`, `semester`, `created_at`) VALUES
(1, '220211060323', 'TIK1101', 'Pendidikan Agama Kristen Protestan', 2, 'B', 3.00, 1, '2026-01-15 17:49:44'),
(2, '220211060323', 'TIK1021', 'Pancasila', 2, 'B+', 3.50, 1, '2026-01-15 17:49:44'),
(3, '220211060323', 'TIK1031', 'Bahasa Indonesia', 2, 'A', 4.00, 1, '2026-01-15 17:49:44'),
(4, '220211060323', 'TIK1041', 'Kewarganegaraan', 2, 'B+', 3.50, 1, '2026-01-15 17:49:44'),
(5, '220211060323', 'TIK1051', 'Matematika Diskrit', 3, 'E', 0.00, 1, '2026-01-15 17:49:44'),
(6, '220211060323', 'TIK1061', 'Kalkulus', 3, 'C+', 2.50, 1, '2026-01-15 17:49:44'),
(7, '220211060323', 'TIK1071', 'Probabilitas Dan Statistika', 2, 'D', 1.00, 1, '2026-01-15 17:49:44'),
(8, '220211060323', 'TIK1081', 'Algoritma Dan Pemrograman Komputer', 4, 'C+', 2.50, 1, '2026-01-15 17:49:44'),
(9, '220211060323', 'TIK1091', 'Prak. Algoritma Dan Pemrograman Komputer', 1, 'C+', 2.50, 1, '2026-01-15 17:49:44'),
(10, '220211060323', 'TIK1012', 'Bahasa Inggris', 2, 'C', 2.00, 2, '2026-01-15 17:49:44'),
(11, '220211060323', 'TIK1022', 'Pengetahuan Kepasifikan', 2, 'A', 4.00, 2, '2026-01-15 17:49:44'),
(12, '220211060323', 'TIK1032', 'Pengantar Teknik Informatika', 2, 'C', 2.00, 2, '2026-01-15 17:49:44'),
(13, '220211060323', 'TIK1042', 'Aljabar Linear', 2, 'E', 0.00, 2, '2026-01-15 17:49:44'),
(14, '220211060323', 'TIK1052', 'Metode Numerik', 3, 'E', 0.00, 2, '2026-01-15 17:49:44'),
(15, '220211060323', 'TIK1072', 'Komputer Dan Masyarakat', 2, 'B+', 3.50, 2, '2026-01-15 17:49:44'),
(16, '220211060323', 'TIK1082', 'Basis Data', 3, 'B', 3.00, 2, '2026-01-15 17:49:44'),
(17, '220211060323', 'TIK1092', 'Praktikum Basis Data', 1, 'E', 0.00, 2, '2026-01-15 17:49:44'),
(18, '220211060323', 'TIK2011', 'Sistem Informasi', 3, 'B', 3.00, 3, '2026-01-15 17:49:44'),
(19, '220211060323', 'TIK2021', 'Arsitektur Dan Organisasi Komputer', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(20, '220211060323', 'TIK2031', 'Sistem Operasi', 3, 'C+', 2.50, 3, '2026-01-15 17:49:44'),
(21, '220211060323', 'TIK2041', 'Struktur Data', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(22, '220211060323', 'TIK2051', 'Rekayasa Perangkat Lunak', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(23, '220211060323', 'TIK2061', 'Teknologi Basis Data', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(24, '220211060323', 'TIK2012', 'Interaksi Manusia Dan Komputer', 4, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(25, '220211060323', 'TIK2022', 'Kecerdasan Buatan', 2, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(26, '220211060323', 'TIK2032', 'Pemrograman Web', 3, 'D', 1.00, 4, '2026-01-15 17:49:44'),
(27, '220211060323', 'TIK2042', 'Pemodelan Dan Simulasi Komputer', 3, 'D', 1.00, 4, '2026-01-15 17:49:44'),
(28, '220211060323', 'TIK2052', 'Pengolahan Citra Digital', 3, 'B+', 3.50, 4, '2026-01-15 17:49:44'),
(29, '220211060323', 'TIK2062', 'Jaringan Dan Komunikasi Data', 3, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(30, '220211060323', 'TIK2072', 'Praktikum Jaringan Dan Komunikasi Data', 1, 'E', 0.00, 4, '2026-01-15 17:49:44'),
(31, '220211060323', 'TIK3011', 'Pembelajaran Mesin', 3, 'D', 1.00, 5, '2026-01-15 17:49:44'),
(32, '220211060323', 'TIK3021', 'Pengembangan Game', 3, 'E', 0.00, 5, '2026-01-15 17:49:44'),
(33, '220211060323', 'TIK3031', 'Realitas Tertambah Dan Realitas Maya', 3, 'E', 0.00, 5, '2026-01-15 17:49:44'),
(34, '220211060323', 'TIK3041', 'Komputasi Awan', 2, 'B', 3.00, 5, '2026-01-15 17:49:44'),
(35, '220211060323', 'TIK3051', 'Sistem Multimedia', 3, 'B+', 3.50, 5, '2026-01-15 17:49:44'),
(36, '220211060323', 'TIK3061', 'Praktikum Sistem Multimedia', 1, 'B+', 3.50, 5, '2026-01-15 17:49:44'),
(37, '220211060323', 'TIK3022', 'Riset Informatika', 3, 'C', 2.00, 6, '2026-01-15 17:49:44'),
(38, '220211060323', 'TIK3032', 'Kewirausahaan', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(39, '220211060323', 'TIK3042', 'Topik Khusus Teknik Informatika', 2, 'B+', 3.50, 6, '2026-01-15 17:49:44'),
(40, '220211060323', 'TIK3052', 'Kecakapan Antar Personal', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(41, '220211060323', 'TIK3062', 'Keamanan Siber', 3, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(42, '220211060323', 'TIK3072', 'Praktikum Keamanan Siber', 1, 'B+', 3.50, 6, '2026-01-15 17:49:44'),
(43, '220211060323', 'TIK3132', 'Data Mining', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(44, '220211060323', 'TIK3182', 'Pengembangan Aplikasi Desktop', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(45, '220211060323', 'TIK3192', 'Pengembangan Aplikasi Web Berbasis Framework', 2, 'C', 2.00, 6, '2026-01-15 17:49:44'),
(46, '220211060242', 'TIK1021', 'Pancasila', 2, 'A', 4.00, 1, '2026-01-15 17:49:44'),
(47, '220211060242', 'TIK1031', 'Bahasa Indonesia', 2, 'A', 4.00, 1, '2026-01-15 17:49:44'),
(48, '220211060242', 'TIK1041', 'Kewarganegaraan', 2, 'B+', 3.50, 1, '2026-01-15 17:49:44'),
(49, '220211060242', 'TIK1051', 'Matematika Diskrit', 3, 'C', 2.00, 1, '2026-01-15 17:49:44'),
(50, '220211060242', 'TIK1061', 'Kalkulus', 3, 'B+', 3.50, 1, '2026-01-15 17:49:44'),
(51, '220211060242', 'TIK1071', 'Probabilitas Dan Statistika', 2, 'C', 2.00, 1, '2026-01-15 17:49:44'),
(52, '220211060242', 'TIK1081', 'Algoritma Dan Pemrograman Komputer', 4, 'C+', 2.50, 1, '2026-01-15 17:49:44'),
(53, '220211060242', 'TIK1091', 'Prak. Algoritma Dan Pemrograman Komputer', 1, 'A', 4.00, 1, '2026-01-15 17:49:44'),
(54, '220211060242', 'TIK1101', 'Pendidikan Agama Kristen Protestan', 2, 'A', 4.00, 1, '2026-01-15 17:49:44'),
(55, '220211060242', 'TIK1012', 'Bahasa Inggris', 2, 'B', 3.00, 2, '2026-01-15 17:49:44'),
(56, '220211060242', 'TIK1022', 'Pengetahuan Kepasifikan', 2, 'A', 4.00, 2, '2026-01-15 17:49:44'),
(57, '220211060242', 'TIK1032', 'Pengantar Teknik Informatika', 2, 'A', 4.00, 2, '2026-01-15 17:49:44'),
(58, '220211060242', 'TIK1042', 'Aljabar Linear', 2, 'B+', 3.50, 2, '2026-01-15 17:49:44'),
(59, '220211060242', 'TIK1052', 'Metode Numerik', 3, 'C', 2.00, 2, '2026-01-15 17:49:44'),
(60, '220211060242', 'TIK1062', 'Teori Bahasa Dan Automata', 3, 'A', 4.00, 2, '2026-01-15 17:49:44'),
(61, '220211060242', 'TIK1072', 'Komputer Dan Masyarakat', 2, 'B', 3.00, 2, '2026-01-15 17:49:44'),
(62, '220211060242', 'TIK1082', 'Basis Data', 3, 'B', 3.00, 2, '2026-01-15 17:49:44'),
(63, '220211060242', 'TIK1092', 'Praktikum Basis Data', 1, 'A', 4.00, 2, '2026-01-15 17:49:44'),
(64, '220211060242', 'TIK2011', 'Sistem Informasi', 3, 'B', 3.00, 3, '2026-01-15 17:49:44'),
(65, '220211060242', 'TIK2021', 'Arsitektur Dan Organisasi Komputer', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(66, '220211060242', 'TIK2031', 'Sistem Operasi', 3, 'B', 3.00, 3, '2026-01-15 17:49:44'),
(67, '220211060242', 'TIK2041', 'Struktur Data', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(68, '220211060242', 'TIK2051', 'Rekayasa Perangkat Lunak', 3, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(69, '220211060242', 'TIK2061', 'Teknologi Basis Data', 3, 'C', 2.00, 3, '2026-01-15 17:49:44'),
(70, '220211060242', 'TIK2071', 'Praktikum Teknologi Basis Data', 1, 'A', 4.00, 3, '2026-01-15 17:49:44'),
(71, '220211060242', 'TIK2012', 'Interaksi Manusia Dan Komputer', 4, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(72, '220211060242', 'TIK2022', 'Kecerdasan Buatan', 2, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(73, '220211060242', 'TIK2032', 'Pemrograman Web', 3, 'B', 3.00, 4, '2026-01-15 17:49:44'),
(74, '220211060242', 'TIK2042', 'Pemodelan Dan Simulasi Komputer', 3, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(75, '220211060242', 'TIK2052', 'Pengolahan Citra Digital', 3, 'A', 4.00, 4, '2026-01-15 17:49:44'),
(76, '220211060242', 'TIK2062', 'Jaringan Dan Komunikasi Data', 3, 'B+', 3.50, 4, '2026-01-15 17:49:44'),
(77, '220211060242', 'TIK2072', 'Praktikum Jaringan Dan Komunikasi Data', 1, 'B', 3.00, 4, '2026-01-15 17:49:44'),
(78, '220211060242', 'TIK3011', 'Pembelajaran Mesin', 3, 'B+', 3.50, 5, '2026-01-15 17:49:44'),
(79, '220211060242', 'TIK3021', 'Pengembangan Game', 3, 'A', 4.00, 5, '2026-01-15 17:49:44'),
(80, '220211060242', 'TIK3031', 'Realitas Tertambah Dan Realitas Maya', 3, 'B+', 3.50, 5, '2026-01-15 17:49:44'),
(81, '220211060242', 'TIK3041', 'Komputasi Awan', 2, 'A', 4.00, 5, '2026-01-15 17:49:44'),
(82, '220211060242', 'TIK3051', 'Sistem Multimedia', 3, 'D', 1.00, 5, '2026-01-15 17:49:44'),
(83, '220211060242', 'TIK3061', 'Praktikum Sistem Multimedia', 1, 'E', 0.00, 5, '2026-01-15 17:49:44'),
(84, '220211060242', 'TIK3151', 'Teknik Perutean Jaringan', 2, 'A', 4.00, 5, '2026-01-15 17:49:44'),
(85, '220211060242', 'TIK3161', 'Teknik Administrasi Server', 2, 'D', 1.00, 5, '2026-01-15 17:49:44'),
(86, '220211060242', 'TIK3012', 'Bioinformatika', 3, 'B+', 3.50, 6, '2026-01-15 17:49:44'),
(87, '220211060242', 'TIK3022', 'Riset Informatika', 3, 'C', 2.00, 6, '2026-01-15 17:49:44'),
(88, '220211060242', 'TIK3032', 'Kewirausahaan', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(89, '220211060242', 'TIK3042', 'Topik Khusus Teknik Informatika', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(90, '220211060242', 'TIK3052', 'Kecakapan Antar Personal', 2, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(91, '220211060242', 'TIK3062', 'Keamanan Siber', 3, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(92, '220211060242', 'TIK3072', 'Praktikum Keamanan Siber', 1, 'A', 4.00, 6, '2026-01-15 17:49:44'),
(93, '220211060242', 'TIK3162', 'Pemrograman Jaringan', 2, 'B', 3.00, 6, '2026-01-15 17:49:44'),
(94, '220211060242', 'TIK4030', 'Seminar Dan Praktek Profesional', 3, 'B', 3.00, 7, '2026-01-15 17:49:44'),
(95, '220211060242', 'TIK4041', 'Grafika Komputer', 2, 'A', 4.00, 7, '2026-01-15 17:49:44'),
(96, '220211060242', 'TIK4061', 'Kriptografi', 2, 'A', 4.00, 7, '2026-01-15 17:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT '2022-08-01',
  `foto_profil` varchar(255) DEFAULT 'assets/images/user_default.png',
  `semester` int DEFAULT '1',
  `tahun_akademik` varchar(20) DEFAULT '2025/2026',
  `periode` varchar(10) DEFAULT 'Gasal',
  `status` varchar(50) DEFAULT 'Aktif',
  `status_pddikti` varchar(50) DEFAULT 'Aktif',
  `angkatan` varchar(4) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `jenjang` varchar(10) DEFAULT NULL,
  `pembimbing_akademik` varchar(100) DEFAULT NULL,
  `nip_pembimbing` varchar(30) DEFAULT NULL,
  `masa_studi` int DEFAULT '1',
  `sisa_masa_studi` int DEFAULT '14',
  `ipk` decimal(3,2) DEFAULT '0.00',
  `sks_lulus` int DEFAULT '0',
  `sks_diambil` int DEFAULT '0',
  `has_academic_warning` tinyint(1) DEFAULT '0',
  `warning_message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nim`, `password`, `nama`, `email`, `tempat_lahir`, `tanggal_lahir`, `tanggal_masuk`, `foto_profil`, `semester`, `tahun_akademik`, `periode`, `status`, `status_pddikti`, `angkatan`, `fakultas`, `prodi`, `jenjang`, `pembimbing_akademik`, `nip_pembimbing`, `masa_studi`, `sisa_masa_studi`, `ipk`, `sks_lulus`, `sks_diambil`, `has_academic_warning`, `warning_message`, `created_at`, `updated_at`) VALUES
(1, '220211060323', '$2y$12$VZclEMIjVHn2PmQAbJ8oO.vlh.zv3rFq9bNoed2BQRtOSg.gcodvq', 'DAVA OKTAVITO JOSUA L. ULUS', NULL, NULL, NULL, '2022-08-01', 'assets/images/user_default.png', 7, '2025/2026', 'Gasal', 'Aktif', 'Aktif', '2022', 'Teknik', 'Teknik Informatika', 'S1', 'MEICSY ELDAD ISRAEL NAJOAN ST, MT', '196705271995121001', 1, 14, 2.63, 82, 24, 0, NULL, '2026-01-15 17:49:44', '2026-01-16 08:29:29'),
(2, '220211060242', '$2y$12$igUeo63omACWYKDF1vVRtefX2rFv4IXbKSLHFgZVYfVZIG/MqjA.C', 'ROMAL PUTRA LENGKONG', NULL, 'Manado', '2004-09-11', '2022-08-01', 'assets/images/user_default.png', 7, '2025/2026', 'Gasal', 'Aktif', 'Aktif', '2022', 'Teknik', 'Teknik Informatika', 'S1', 'KENNETH YOSUA R PALILINGAN ST, MT', NULL, 1, 14, 3.31, 117, 0, 0, NULL, '2026-01-15 17:49:44', '2026-01-15 17:49:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `krs`
--
ALTER TABLE `krs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mk` (`kode_mk`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `perencanaan_studi`
--
ALTER TABLE `perencanaan_studi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_perencanaan` (`nim`,`kode_mk`);

--
-- Indexes for table `transkrip`
--
ALTER TABLE `transkrip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `krs`
--
ALTER TABLE `krs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `perencanaan_studi`
--
ALTER TABLE `perencanaan_studi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transkrip`
--
ALTER TABLE `transkrip`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Foreign Key Constraints
--

-- Note: Foreign keys are commented out to avoid import errors if tables are not in correct order
-- Uncomment if you need referential integrity checks

-- ALTER TABLE `krs`
--   ADD CONSTRAINT `fk_krs_nim` FOREIGN KEY (`nim`) REFERENCES `users` (`nim`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `transkrip`
--   ADD CONSTRAINT `fk_transkrip_nim` FOREIGN KEY (`nim`) REFERENCES `users` (`nim`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `perencanaan_studi`
--   ADD CONSTRAINT `fk_perencanaan_nim` FOREIGN KEY (`nim`) REFERENCES `users` (`nim`) ON DELETE CASCADE ON UPDATE CASCADE,
--   ADD CONSTRAINT `fk_perencanaan_kode_mk` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE `pengumuman`
--   ADD CONSTRAINT `fk_pengumuman_nim` FOREIGN KEY (`nim`) REFERENCES `users` (`nim`) ON DELETE SET NULL ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
