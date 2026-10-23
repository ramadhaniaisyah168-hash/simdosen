-- =====================================================================
-- SIM DOSEN - Universitas Muhammadiyah Bengkulu
-- Berkas dump basisdata (struktur + data awal)
-- Cara pakai: pilih basisdata di phpMyAdmin -> tab Impor -> pilih berkas ini.
-- Berkas ini TIDAK membuat/memilih basisdata, jadi bisa diimpor ke basisdata
-- mana pun (lokal: sim_dosen, InfinityFree: if0_xxxxxxx_sim_dosen).
-- PERHATIAN: tabel lama dengan nama yang sama akan DIHAPUS lalu dibuat ulang.
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `pengguna`;
DROP TABLE IF EXISTS `dosen`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `jabatan_fungsional`;
DROP TABLE IF EXISTS `kepangkatan`;
DROP TABLE IF EXISTS `pendidikan`;
DROP TABLE IF EXISTS `rumpun_ilmu`;
DROP TABLE IF EXISTS `status_kepegawaian`;
DROP TABLE IF EXISTS `penempatan`;

-- ---------------------------------------------------------------- roles
CREATE TABLE `roles` (
  `id_role` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_role` VARCHAR(30) NOT NULL,
  `nama_role` VARCHAR(60) NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `uq_kode_role` (`kode_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------ master (6 tabel)
CREATE TABLE `jabatan_fungsional` (
  `id_jabatan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_jabatan` VARCHAR(80) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_jabatan`),
  UNIQUE KEY `uq_nama_jabatan` (`nama_jabatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kepangkatan` (
  `id_pangkat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pangkat` VARCHAR(80) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pangkat`),
  UNIQUE KEY `uq_nama_pangkat` (`nama_pangkat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pendidikan` (
  `id_pendidikan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pendidikan` VARCHAR(60) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pendidikan`),
  UNIQUE KEY `uq_nama_pendidikan` (`nama_pendidikan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rumpun_ilmu` (
  `id_rumpun` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_rumpun` VARCHAR(100) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_rumpun`),
  UNIQUE KEY `uq_nama_rumpun` (`nama_rumpun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `status_kepegawaian` (
  `id_status` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_status` VARCHAR(80) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `uq_nama_status` (`nama_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `penempatan` (
  `id_penempatan` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_penempatan` VARCHAR(120) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_penempatan`),
  UNIQUE KEY `uq_nama_penempatan` (`nama_penempatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------- dosen
CREATE TABLE `dosen` (
  `id_dosen` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_registrasi` VARCHAR(30) NOT NULL,
  `nuptk` VARCHAR(20) DEFAULT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `nip` VARCHAR(25) DEFAULT NULL,
  `id_jabatan` INT UNSIGNED DEFAULT NULL,
  `id_pangkat` INT UNSIGNED DEFAULT NULL,
  `id_pendidikan` INT UNSIGNED DEFAULT NULL,
  `id_rumpun` INT UNSIGNED DEFAULT NULL,
  `status_serdos` ENUM('Sudah','Belum') NOT NULL DEFAULT 'Belum',
  `id_status` INT UNSIGNED DEFAULT NULL,
  `ikatan_kerja` ENUM('Dosen Tetap','Dosen Tidak Tetap') NOT NULL DEFAULT 'Dosen Tetap',
  `status_aktivitas` ENUM('Aktif','Cuti','Tugas Belajar','Izin Belajar','Pensiun') NOT NULL DEFAULT 'Aktif',
  `id_penempatan` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dosen`),
  UNIQUE KEY `uq_nomor_registrasi` (`nomor_registrasi`),
  KEY `idx_nama` (`nama`),
  KEY `fk_dosen_jabatan` (`id_jabatan`),
  KEY `fk_dosen_pangkat` (`id_pangkat`),
  KEY `fk_dosen_pendidikan` (`id_pendidikan`),
  KEY `fk_dosen_rumpun` (`id_rumpun`),
  KEY `fk_dosen_status` (`id_status`),
  KEY `fk_dosen_penempatan` (`id_penempatan`),
  CONSTRAINT `fk_dosen_jabatan` FOREIGN KEY (`id_jabatan`) REFERENCES `jabatan_fungsional` (`id_jabatan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dosen_pangkat` FOREIGN KEY (`id_pangkat`) REFERENCES `kepangkatan` (`id_pangkat`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dosen_pendidikan` FOREIGN KEY (`id_pendidikan`) REFERENCES `pendidikan` (`id_pendidikan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dosen_rumpun` FOREIGN KEY (`id_rumpun`) REFERENCES `rumpun_ilmu` (`id_rumpun`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dosen_status` FOREIGN KEY (`id_status`) REFERENCES `status_kepegawaian` (`id_status`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_dosen_penempatan` FOREIGN KEY (`id_penempatan`) REFERENCES `penempatan` (`id_penempatan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------- pengguna
CREATE TABLE `pengguna` (
  `id_pengguna` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(150) NOT NULL,
  `email` VARCHAR(120) DEFAULT NULL,
  `id_role` INT UNSIGNED NOT NULL,
  `id_dosen` INT UNSIGNED DEFAULT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengguna`),
  UNIQUE KEY `uq_username` (`username`),
  KEY `fk_pengguna_role` (`id_role`),
  KEY `fk_pengguna_dosen` (`id_dosen`),
  CONSTRAINT `fk_pengguna_role` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pengguna_dosen` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================ DATA AWAL
INSERT INTO `roles` (`id_role`,`kode_role`,`nama_role`,`deskripsi`) VALUES
(1,'admin','Admin','Akses penuh: dosen, data master, pengguna, role, dan laporan.'),
(2,'operator','Operator','Mengelola data dosen dan data master serta melihat laporan.'),
(3,'pimpinan','Pimpinan','Hanya melihat data dosen, data master, dan laporan.'),
(4,'dosen','Dosen','Hanya melihat profil sendiri dan mengganti password.');

INSERT INTO `jabatan_fungsional` (`id_jabatan`,`nama_jabatan`,`aktif`) VALUES
(1,'Tenaga Pengajar',1),(2,'Asisten Ahli',1),(3,'Lektor',1),(4,'Lektor Kepala',1),(5,'Profesor',1);

INSERT INTO `kepangkatan` (`id_pangkat`,`nama_pangkat`,`aktif`) VALUES
(1,'III/a - Penata Muda',1),(2,'III/b - Penata Muda Tingkat I',1),(3,'III/c - Penata',1),
(4,'III/d - Penata Tingkat I',1),(5,'IV/a - Pembina',1),(6,'IV/b - Pembina Tingkat I',1),
(7,'Belum Ada / Non-PNS',1);

INSERT INTO `pendidikan` (`id_pendidikan`,`nama_pendidikan`,`aktif`) VALUES
(1,'S1',1),(2,'S2',1),(3,'S3',1),(4,'Profesi',1);

INSERT INTO `rumpun_ilmu` (`id_rumpun`,`nama_rumpun`,`aktif`) VALUES
(1,'Ilmu Komputer',1),(2,'Teknik',1),(3,'Ekonomi dan Bisnis',1),(4,'Pendidikan',1),
(5,'Pertanian',1),(6,'Hukum',1),(7,'Agama Islam',1);

INSERT INTO `status_kepegawaian` (`id_status`,`nama_status`,`aktif`) VALUES
(1,'PNS',1),(2,'PNS DPK',1),(3,'PPPK',1),(4,'Dosen Yayasan',1),(5,'Dosen Tidak Tetap',1);

INSERT INTO `penempatan` (`id_penempatan`,`nama_penempatan`,`aktif`) VALUES
(1,'Teknik Informatika',1),(2,'Sistem Informasi',1),(3,'Teknik Sipil',1),(4,'Agribisnis',1),
(5,'Manajemen',1),(6,'Pendidikan Matematika',1),(7,'Ilmu Hukum',1);

-- Data dosen contoh (fiktif, hanya untuk demo)
INSERT INTO `dosen` (`id_dosen`,`nomor_registrasi`,`nuptk`,`nama`,`nip`,`id_jabatan`,`id_pangkat`,`id_pendidikan`,`id_rumpun`,`status_serdos`,`id_status`,`ikatan_kerja`,`status_aktivitas`,`id_penempatan`) VALUES
(1,'REG-0001','1234567890120001','Dr. Rahmat Hidayat, M.Kom.','197805122005011002',4,5,3,1,'Sudah',2,'Dosen Tetap','Aktif',1),
(2,'REG-0002','1234567890120002','Sri Wahyuni, S.T., M.T.','198210302008012004',3,4,2,2,'Sudah',2,'Dosen Tetap','Aktif',3),
(3,'REG-0003','1234567890120003','Budi Santoso, S.Kom., M.Cs.',NULL,2,7,2,1,'Belum',4,'Dosen Tetap','Aktif',2),
(4,'REG-0004','1234567890120004','Prof. Dr. Ir. Hendra Kusuma, M.Si.','196907151994031003',5,6,3,5,'Sudah',1,'Dosen Tetap','Aktif',4),
(5,'REG-0005','1234567890120005','Dewi Lestari, S.E., M.M.',NULL,3,7,2,3,'Sudah',4,'Dosen Tetap','Aktif',5),
(6,'REG-0006','1234567890120006','Andi Pratama, S.Pd., M.Pd.',NULL,1,7,2,4,'Belum',4,'Dosen Tetap','Tugas Belajar',6),
(7,'REG-0007','1234567890120007','Fitri Handayani, S.H., M.H.',NULL,2,7,2,6,'Belum',5,'Dosen Tidak Tetap','Aktif',7),
(8,'REG-0008','1234567890120008','Ahmad Fauzi, S.Kom., M.T.I.','198811202019031008',2,3,2,1,'Belum',3,'Dosen Tetap','Aktif',1),
(9,'REG-0009','1234567890120009','Lina Marlina, S.Si., M.Kom.',NULL,1,7,2,1,'Belum',4,'Dosen Tetap','Cuti',2),
(10,'REG-0010','1234567890120010','Dr. Yusuf Maulana, M.Ag.','197503102003121001',4,5,3,7,'Sudah',1,'Dosen Tetap','Aktif',7),
(11,'REG-0011','1234567890120011','Nurul Aini, S.T., M.Eng.',NULL,2,7,2,2,'Belum',4,'Dosen Tetap','Izin Belajar',3),
(12,'REG-0012','1234567890120012','Rizky Ramadhan, S.E., M.Si.',NULL,1,7,2,3,'Belum',5,'Dosen Tidak Tetap','Aktif',5);

-- Akun demo (password di-hash dengan password_hash / bcrypt)
--   admin / admin123   operator / operator123   pimpinan / pimpinan123
--   dosen1 / dosen123  (terhubung ke data dosen id 1)   dosen2 / dosen123 (id 8)
INSERT INTO `pengguna` (`username`,`password`,`nama_lengkap`,`email`,`id_role`,`id_dosen`,`aktif`) VALUES
('admin','$2y$10$juIhpLcrvVuBpYFfYkr3qeayMYvsXINpcAifq/3ReamWYpXVk0fjq','Administrator SIM Dosen','admin@um-bengkulu.ac.id',1,NULL,1),
('operator','$2y$10$PWOweuDxfUZucLaGrWilYecuDeG5PEsdfw4i7g/QRgM18zkiPSsrK','Operator Kepegawaian','operator@um-bengkulu.ac.id',2,NULL,1),
('pimpinan','$2y$10$R16CLor7OH4h86u8vnzleO155QSBjcY08lohFXvhb6xzL0fCNpFPa','Pimpinan Universitas','pimpinan@um-bengkulu.ac.id',3,NULL,1),
('dosen1','$2y$10$JoszAB5r5d2pSjkIWQNx0eYVde6bp03b02JasxiEZb1FpbqpU3GAu','Dr. Rahmat Hidayat, M.Kom.','rahmat@um-bengkulu.ac.id',4,1,1),
('dosen2','$2y$10$JoszAB5r5d2pSjkIWQNx0eYVde6bp03b02JasxiEZb1FpbqpU3GAu','Ahmad Fauzi, S.Kom., M.T.I.','fauzi@um-bengkulu.ac.id',4,8,1);
