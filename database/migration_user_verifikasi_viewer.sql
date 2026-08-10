-- Verifikasi akun Viewer (SSO)
USE `siperkul`;

ALTER TABLE `tb_users`
  MODIFY `username` varchar(50) DEFAULT NULL,
  MODIFY `password` varchar(255) DEFAULT NULL,
  ADD COLUMN `status` enum('Menunggu','Aktif','Ditolak') NOT NULL DEFAULT 'Aktif' AFTER `role`,
  ADD COLUMN `id_dosen` int(11) DEFAULT NULL AFTER `id_prodi`;

ALTER TABLE `tb_users`
  ADD KEY `id_dosen` (`id_dosen`),
  ADD CONSTRAINT `tb_users_ibfk_dosen`
    FOREIGN KEY (`id_dosen`) REFERENCES `tb_dosen` (`id_dosen`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Akun lama (admin/baak/dll) tetap aktif
UPDATE `tb_users` SET `status` = 'Aktif' WHERE `status` = 'Aktif' OR `status` IS NULL;
