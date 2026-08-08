USE `siperkul`;
ALTER TABLE `tb_users`
  ADD COLUMN `email` varchar(150) DEFAULT NULL AFTER `nama_lengkap`,
  ADD UNIQUE KEY `email` (`email`);
