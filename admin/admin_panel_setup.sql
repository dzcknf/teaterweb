-- ============================================================
--  SQL TAMBAHAN UNTUK PANEL ADMIN
--  Jalankan script ini SETELAH database cinema_bioskop sudah ada
--  (lewat tab SQL di phpMyAdmin)
-- ============================================================

-- ----------------------------------------------------------------
-- 1. Tambah kolom 'poster' ke tabel film (jika belum ada)
--    Menyimpan NAMA FILE poster, contoh: film_1234_ab12cd.jpg
-- ----------------------------------------------------------------
ALTER TABLE `film`
  ADD COLUMN `poster` VARCHAR(255) NULL DEFAULT NULL AFTER `deskripsi`;

-- ----------------------------------------------------------------
-- 2. Buat tabel admin
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `username`      VARCHAR(50)   NOT NULL,
  `password_hash` VARCHAR(255)  NOT NULL,
  `nama`          VARCHAR(100)  NOT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------
-- 3. Insert akun admin default
--    Username : admin
--    Password : admin123
--    (Password sudah di-hash dengan bcrypt, kompatibel dengan
--     password_verify() di PHP)
-- ----------------------------------------------------------------
INSERT INTO `admin` (`username`, `password_hash`, `nama`) VALUES
('admin', '$2b$12$pdh1s9TYf8oyyahXk6Hl8evrQ8rlROcp65kzeS9ccPp4Ev0RPvf2m', 'Administrator');

-- ============================================================
--  PENTING: SEGERA GANTI PASSWORD SETELAH LOGIN PERTAMA KALI!
--  Cara ganti password manual lewat phpMyAdmin:
--
--  1. Buat hash password baru dengan menjalankan kode PHP berikut
--     di file sementara (lalu hapus file itu setelah dipakai):
--
--     <?php echo password_hash('password_baru_kamu', PASSWORD_DEFAULT); ?>
--
--  2. Copy hasil hash yang muncul (diawali $2y$ atau $2b$)
--
--  3. Di phpMyAdmin, buka tabel `admin` -> Edit baris admin ->
--     paste hash baru ke kolom `password_hash` -> Simpan
-- ============================================================
