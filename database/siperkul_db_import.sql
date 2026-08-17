-- SIPERKUL import for InfinityFree / shared hosting
-- Import via phpMyAdmin after selecting database if0_42674372_siperkul

-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2026
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siperkul`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_fakultas`
--
CREATE TABLE `tb_fakultas` (
  `id_fakultas` int(11) NOT NULL,
  `kode_fakultas` varchar(10) NOT NULL,
  `nama_fakultas` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_fakultas`
--
INSERT INTO `tb_fakultas` (`id_fakultas`, `kode_fakultas`, `nama_fakultas`) VALUES
(1, 'FTIK', 'Fakultas Telematika Energi'),
(2, 'FTK', 'Fakultas Teknologi Infrastruktur dan Kewilayahan'),
(3, 'KDB', 'Fakultas Ketenagalistrikan dan Energi Terbarukan'),
(4, 'FEB', 'Fakultas Teknologi dan Bisnis Energi');

-- --------------------------------------------------------

--
-- Table structure for table `tb_prodi`
--
CREATE TABLE `tb_prodi` (
  `id_prodi` int(11) NOT NULL,
  `id_fakultas` int(11) NOT NULL,
  `kode_prodi` varchar(10) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_prodi`
--
INSERT INTO `tb_prodi` (`id_prodi`, `id_fakultas`, `kode_prodi`, `nama_prodi`) VALUES
(1, 1, 'TI', 'S1 Teknik Informatika'),
(2, 1, 'SI', 'S1 Sistem Informasi'),
(3, 3, 'TE', 'S1 Teknik Elektro'),
(4, 2, 'TS', 'S1 Teknik Sipil');

-- --------------------------------------------------------

--
-- Table structure for table `tb_mata_kuliah`
--
CREATE TABLE `tb_mata_kuliah` (
  `id_mk` int(11) NOT NULL,
  `id_prodi` int(11) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(150) NOT NULL,
  `sks` int(2) NOT NULL,
  `semester` int(2) NOT NULL,
  `jenis` enum('Wajib','Pilihan') NOT NULL DEFAULT 'Wajib',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_dosen`
--
CREATE TABLE `tb_dosen` (
  `id_dosen` int(11) NOT NULL,
  `nidn` varchar(20) DEFAULT NULL,
  `kode_dosen` varchar(10) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_gedung`
--
CREATE TABLE `tb_gedung` (
  `id_gedung` int(11) NOT NULL,
  `kode_gedung` varchar(10) NOT NULL,
  `nama_gedung` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_ruangan`
--
CREATE TABLE `tb_ruangan` (
  `id_ruangan` int(11) NOT NULL,
  `id_gedung` int(11) NOT NULL,
  `kode_ruangan` varchar(20) NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `lantai` int(2) NOT NULL,
  `nomor_ruang` varchar(10) NOT NULL,
  `kapasitas_kuliah` int(4) NOT NULL,
  `kapasitas_ujian` int(4) NOT NULL,
  `status` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_tahun_akademik`
--
CREATE TABLE `tb_tahun_akademik` (
  `id_ta` int(11) NOT NULL,
  `tahun_akademik` varchar(10) NOT NULL,
  `semester` enum('Ganjil','Genap','Pendek') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_tahun_akademik`
--
INSERT INTO `tb_tahun_akademik` (`id_ta`, `tahun_akademik`, `semester`, `is_active`) VALUES
(1, '2025/2026', 'Ganjil', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tb_users`
--
CREATE TABLE `tb_users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role` enum('Administrator','BAAK','Operator Fakultas','Operator Prodi','Viewer') NOT NULL,
  `status` enum('Menunggu','Aktif','Ditolak') NOT NULL DEFAULT 'Aktif',
  `id_fakultas` int(11) DEFAULT NULL,
  `id_prodi` int(11) DEFAULT NULL,
  `id_dosen` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tb_users`
--
INSERT INTO `tb_users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`, `status`, `id_fakultas`, `id_prodi`, `id_dosen`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', NULL, 'Administrator', 'Aktif', NULL, NULL, NULL), -- password: password
(2, 'baak', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff BAAK', NULL, 'BAAK', 'Aktif', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_jadwal`
--
CREATE TABLE `tb_jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `id_prodi` int(11) NOT NULL,
  `id_mk` int(11) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `sesi` int(2) NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `kapasitas_mhs` int(4) NOT NULL,
  `id_ta` int(11) NOT NULL,
  `status` enum('Aktif','Batal') NOT NULL DEFAULT 'Aktif',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tb_audit_trail`
--
CREATE TABLE `tb_audit_trail` (
  `id_audit` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

ALTER TABLE `tb_fakultas`
  ADD PRIMARY KEY (`id_fakultas`),
  ADD UNIQUE KEY `kode_fakultas` (`kode_fakultas`);

ALTER TABLE `tb_prodi`
  ADD PRIMARY KEY (`id_prodi`),
  ADD UNIQUE KEY `kode_prodi` (`kode_prodi`),
  ADD KEY `id_fakultas` (`id_fakultas`);

ALTER TABLE `tb_mata_kuliah`
  ADD PRIMARY KEY (`id_mk`),
  ADD KEY `id_prodi` (`id_prodi`);

ALTER TABLE `tb_dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `kode_dosen` (`kode_dosen`);

ALTER TABLE `tb_gedung`
  ADD PRIMARY KEY (`id_gedung`),
  ADD UNIQUE KEY `kode_gedung` (`kode_gedung`);

ALTER TABLE `tb_ruangan`
  ADD PRIMARY KEY (`id_ruangan`),
  ADD UNIQUE KEY `kode_ruangan` (`kode_ruangan`),
  ADD KEY `id_gedung` (`id_gedung`);

ALTER TABLE `tb_tahun_akademik`
  ADD PRIMARY KEY (`id_ta`);

ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_fakultas` (`id_fakultas`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `id_dosen` (`id_dosen`);

ALTER TABLE `tb_jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `id_mk` (`id_mk`),
  ADD KEY `id_dosen` (`id_dosen`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `id_ta` (`id_ta`),
  ADD KEY `created_by` (`created_by`);

ALTER TABLE `tb_audit_trail`
  ADD PRIMARY KEY (`id_audit`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `tb_fakultas` MODIFY `id_fakultas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `tb_prodi` MODIFY `id_prodi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `tb_mata_kuliah` MODIFY `id_mk` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_dosen` MODIFY `id_dosen` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_gedung` MODIFY `id_gedung` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_ruangan` MODIFY `id_ruangan` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_tahun_akademik` MODIFY `id_ta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tb_users` MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `tb_jadwal` MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tb_audit_trail` MODIFY `id_audit` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

ALTER TABLE `tb_prodi`
  ADD CONSTRAINT `tb_prodi_ibfk_1` FOREIGN KEY (`id_fakultas`) REFERENCES `tb_fakultas` (`id_fakultas`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tb_mata_kuliah`
  ADD CONSTRAINT `tb_mata_kuliah_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `tb_prodi` (`id_prodi`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tb_ruangan`
  ADD CONSTRAINT `tb_ruangan_ibfk_1` FOREIGN KEY (`id_gedung`) REFERENCES `tb_gedung` (`id_gedung`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tb_users`
  ADD CONSTRAINT `tb_users_ibfk_1` FOREIGN KEY (`id_fakultas`) REFERENCES `tb_fakultas` (`id_fakultas`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_users_ibfk_2` FOREIGN KEY (`id_prodi`) REFERENCES `tb_prodi` (`id_prodi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_users_ibfk_dosen` FOREIGN KEY (`id_dosen`) REFERENCES `tb_dosen` (`id_dosen`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tb_jadwal`
  ADD CONSTRAINT `tb_jadwal_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `tb_prodi` (`id_prodi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_jadwal_ibfk_2` FOREIGN KEY (`id_mk`) REFERENCES `tb_mata_kuliah` (`id_mk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_jadwal_ibfk_3` FOREIGN KEY (`id_dosen`) REFERENCES `tb_dosen` (`id_dosen`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_jadwal_ibfk_4` FOREIGN KEY (`id_ruangan`) REFERENCES `tb_ruangan` (`id_ruangan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_jadwal_ibfk_5` FOREIGN KEY (`id_ta`) REFERENCES `tb_tahun_akademik` (`id_ta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_jadwal_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `tb_users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tb_audit_trail`
  ADD CONSTRAINT `tb_audit_trail_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
