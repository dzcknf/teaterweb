-- ============================================================
--  DATABASE CINEMA BIOSKOP
--  Compatible: phpMyAdmin / XAMPP / MySQL 5.7+
--  Dibuat: 2026
-- ============================================================

-- -- Buat & pilih database
-- CREATE DATABASE IF NOT EXISTS `cinemaiky_db`
--   CHARACTER SET utf8mb4
--   COLLATE utf8mb4_unicode_ci;

-- USE `cinemaiky_db`;

-- ============================================================
--  HAPUS TABEL LAMA (urutan terbalik agar FK tidak error)
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `transaksi`;
DROP TABLE IF EXISTS `kursi`;
DROP TABLE IF EXISTS `jadwal`;
DROP TABLE IF EXISTS `pelanggan`;
DROP TABLE IF EXISTS `studio`;
DROP TABLE IF EXISTS `film`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  1. TABEL FILM
-- ============================================================
CREATE TABLE `film` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `judul`         VARCHAR(150)    NOT NULL,
  `genre`         VARCHAR(60)     NOT NULL,
  `durasi`        SMALLINT UNSIGNED NOT NULL COMMENT 'Durasi dalam menit',
  `rating`        ENUM('G','PG','PG-13','R','NC-17','SU','R13','R17','R21','D17') NOT NULL DEFAULT 'SU',
  `deskripsi`     TEXT,
  `tanggal_rilis` DATE            NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  2. TABEL STUDIO
-- ============================================================
CREATE TABLE `studio` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nama_studio`  VARCHAR(50)   NOT NULL,
  `kapasitas`    SMALLINT UNSIGNED NOT NULL COMMENT 'Jumlah kursi total',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  3. TABEL JADWAL
-- ============================================================
CREATE TABLE `jadwal` (
  `id`         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `id_film`    INT UNSIGNED   NOT NULL,
  `id_studio`  INT UNSIGNED   NOT NULL,
  `tanggal`    DATE           NOT NULL,
  `jam_tayang` TIME           NOT NULL,
  `harga`      DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_jadwal_film`   FOREIGN KEY (`id_film`)   REFERENCES `film`(`id`)   ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_jadwal_studio` FOREIGN KEY (`id_studio`) REFERENCES `studio`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  4. TABEL KURSI
-- ============================================================
CREATE TABLE `kursi` (
  `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_studio`    INT UNSIGNED  NOT NULL,
  `nomor_kursi`  VARCHAR(10)   NOT NULL COMMENT 'Contoh: A1, B3',
  `baris`        CHAR(1)       NOT NULL COMMENT 'Huruf baris: A, B, C ...',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kursi_studio` (`id_studio`, `nomor_kursi`),
  CONSTRAINT `fk_kursi_studio` FOREIGN KEY (`id_studio`) REFERENCES `studio`(`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  5. TABEL PELANGGAN
-- ============================================================
CREATE TABLE `pelanggan` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nama`        VARCHAR(100)  NOT NULL,
  `email`       VARCHAR(150)  NOT NULL,
  `no_telepon`  VARCHAR(20)   NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  6. TABEL TRANSAKSI
-- ============================================================
CREATE TABLE `transaksi` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `id_pelanggan`      INT UNSIGNED  NOT NULL,
  `id_jadwal`         INT UNSIGNED  NOT NULL,
  `id_kursi`          INT UNSIGNED  NOT NULL,
  `total_harga`       DECIMAL(10,2) NOT NULL,
  `waktu_pesan`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_pembayaran` ENUM('pending','lunas','dibatalkan') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`),
  -- Satu kursi tidak boleh dipesan dua kali untuk jadwal yang sama
  UNIQUE KEY `uq_booking` (`id_jadwal`, `id_kursi`),
  CONSTRAINT `fk_trx_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan`(`id`) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_trx_jadwal`    FOREIGN KEY (`id_jadwal`)    REFERENCES `jadwal`(`id`)    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_trx_kursi`     FOREIGN KEY (`id_kursi`)     REFERENCES `kursi`(`id`)     ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
--  DATA DUMMY
-- ============================================================

-- ---- FILM (3 film) ----
INSERT INTO `film` (`judul`, `genre`, `durasi`, `rating`, `deskripsi`, `tanggal_rilis`) VALUES
('Garuda Perkasa',     'Action',  118, 'R13',  'Kisah pilot tempur Indonesia membela kedaulatan udara negeri.', '2026-03-15'),
('Cinta Di Atas Awan', 'Romance', 105, 'SU',   'Dua jiwa yang bertemu di ketinggian 30.000 kaki dan jatuh cinta.', '2026-04-20'),
('Rimba Gelap',        'Horror',  95,  'R17',  'Sekelompok mahasiswa tersesat di hutan terlarang Kalimantan.', '2026-05-10');

-- ---- STUDIO (2 studio) ----
INSERT INTO `studio` (`nama_studio`, `kapasitas`) VALUES
('Studio 1 – Reguler', 80),
('Studio 2 – VIP',     40);

-- ---- KURSI Studio 1 (baris A & B, masing-masing 5 kursi) ----
INSERT INTO `kursi` (`id_studio`, `nomor_kursi`, `baris`) VALUES
(1,'A1','A'),(1,'A2','A'),(1,'A3','A'),(1,'A4','A'),(1,'A5','A'),
(1,'B1','B'),(1,'B2','B'),(1,'B3','B'),(1,'B4','B'),(1,'B5','B');

-- ---- KURSI Studio 2 (baris A saja, 5 kursi VIP) ----
INSERT INTO `kursi` (`id_studio`, `nomor_kursi`, `baris`) VALUES
(2,'A1','A'),(2,'A2','A'),(2,'A3','A'),(2,'A4','A'),(2,'A5','A');

-- ---- JADWAL ----
INSERT INTO `jadwal` (`id_film`, `id_studio`, `tanggal`, `jam_tayang`, `harga`) VALUES
-- Garuda Perkasa – Studio 1
(1, 1, '2026-06-28', '10:00:00', 45000.00),
(1, 1, '2026-06-28', '13:00:00', 45000.00),
(1, 1, '2026-06-29', '15:00:00', 45000.00),
-- Cinta Di Atas Awan – Studio 2 VIP
(2, 2, '2026-06-28', '11:00:00', 85000.00),
(2, 2, '2026-06-29', '14:00:00', 85000.00),
-- Rimba Gelap – Studio 1
(3, 1, '2026-06-28', '19:00:00', 50000.00),
(3, 1, '2026-06-29', '21:00:00', 50000.00);

-- ---- PELANGGAN ----
INSERT INTO `pelanggan` (`nama`, `email`, `no_telepon`) VALUES
('Budi Santoso',    'budi.santoso@email.com',    '081234567890'),
('Siti Rahayu',     'siti.rahayu@email.com',     '082345678901'),
('Ahmad Fauzan',    'ahmad.fauzan@email.com',    '083456789012'),
('Dewi Lestari',    'dewi.lestari@email.com',    '084567890123'),
('Rizky Pratama',   'rizky.pratama@email.com',   '085678901234');

-- ---- TRANSAKSI ----
INSERT INTO `transaksi` (`id_pelanggan`, `id_jadwal`, `id_kursi`, `total_harga`, `waktu_pesan`, `status_pembayaran`) VALUES
-- Budi beli kursi A1 jadwal 1 (Garuda Perkasa, 28 Jun 10:00, Studio1)
(1, 1,  1, 45000.00, '2026-06-27 09:00:00', 'lunas'),
-- Siti beli kursi A2 jadwal 1
(2, 1,  2, 45000.00, '2026-06-27 09:15:00', 'lunas'),
-- Ahmad beli kursi A1 jadwal 4 (Cinta Di Atas Awan, VIP)
(3, 4, 11, 85000.00, '2026-06-27 10:00:00', 'lunas'),
-- Dewi beli kursi A2 jadwal 4 (VIP) – masih pending
(4, 4, 12, 85000.00, '2026-06-27 10:30:00', 'pending'),
-- Rizky beli kursi B1 jadwal 6 (Rimba Gelap)
(5, 6,  6, 50000.00, '2026-06-27 11:00:00', 'lunas'),
-- Budi beli kursi A3 jadwal 2 (Garuda Perkasa, 28 Jun 13:00)
(1, 2,  3, 45000.00, '2026-06-27 11:30:00', 'dibatalkan'),
-- Siti beli kursi A1 jadwal 5 (Cinta Di Atas Awan, 29 Jun VIP)
(2, 5, 11, 85000.00, '2026-06-27 12:00:00', 'lunas');


-- ============================================================
--  CONTOH QUERY BERGUNA
-- ============================================================

-- Lihat jadwal lengkap hari ini
-- SELECT j.tanggal, j.jam_tayang, f.judul, s.nama_studio, j.harga
-- FROM jadwal j
-- JOIN film f ON f.id = j.id_film
-- JOIN studio s ON s.id = j.id_studio
-- WHERE j.tanggal = CURDATE()
-- ORDER BY j.jam_tayang;

-- Lihat transaksi beserta nama pelanggan & film
-- SELECT t.id, p.nama, f.judul, k.nomor_kursi, t.total_harga, t.status_pembayaran
-- FROM transaksi t
-- JOIN pelanggan p  ON p.id  = t.id_pelanggan
-- JOIN jadwal   j  ON j.id  = t.id_jadwal
-- JOIN film     f  ON f.id  = j.id_film
-- JOIN kursi    k  ON k.id  = t.id_kursi
-- ORDER BY t.waktu_pesan DESC;

-- Cek kursi yang sudah terpesan untuk jadwal tertentu
-- SELECT k.nomor_kursi, k.baris
-- FROM transaksi t
-- JOIN kursi k ON k.id = t.id_kursi
-- WHERE t.id_jadwal = 1 AND t.status_pembayaran != 'dibatalkan';
