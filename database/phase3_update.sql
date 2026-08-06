-- Table structure for tb_tahun_akademik
CREATE TABLE IF NOT EXISTS `tb_tahun_akademik` (
  `id_ta` int(11) NOT NULL AUTO_INCREMENT,
  `tahun_akademik` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0' COMMENT '1=Aktif, 0=Non-Aktif',
  PRIMARY KEY (`id_ta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for tb_tahun_akademik
INSERT INTO `tb_tahun_akademik` (`tahun_akademik`, `semester`, `status`) VALUES
('2023/2024', 'Ganjil', 0),
('2023/2024', 'Genap', 1);

-- Table structure for tb_audit_trail
CREATE TABLE IF NOT EXISTS `tb_audit_trail` (
  `id_audit` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(100) NOT NULL,
  `keterangan` text,
  `tanggal` datetime NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  PRIMARY KEY (`id_audit`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `tb_audit_trail_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for tb_jadwal
CREATE TABLE IF NOT EXISTS `tb_jadwal` (
  `id_jadwal` int(11) NOT NULL AUTO_INCREMENT,
  `id_prodi` int(11) NOT NULL,
  `id_mk` int(11) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `hari` varchar(10) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `kapasitas_mhs` int(11) NOT NULL,
  `id_ta` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (`id_jadwal`),
  KEY `id_prodi` (`id_prodi`),
  KEY `id_mk` (`id_mk`),
  KEY `id_dosen` (`id_dosen`),
  KEY `id_ruangan` (`id_ruangan`),
  KEY `id_ta` (`id_ta`),
  CONSTRAINT `tb_jadwal_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `tb_prodi` (`id_prodi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_jadwal_ibfk_2` FOREIGN KEY (`id_mk`) REFERENCES `tb_mata_kuliah` (`id_mk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_jadwal_ibfk_3` FOREIGN KEY (`id_dosen`) REFERENCES `tb_dosen` (`id_dosen`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_jadwal_ibfk_4` FOREIGN KEY (`id_ruangan`) REFERENCES `tb_ruangan` (`id_ruangan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tb_jadwal_ibfk_5` FOREIGN KEY (`id_ta`) REFERENCES `tb_tahun_akademik` (`id_ta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
