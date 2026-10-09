# Panel Admin CinemaXXI — Panduan Instalasi

## Struktur Folder

Panel admin ini didesain untuk di-hosting **terpisah** dari website utama
(misal di subdomain `admin.namadomain.com`), tapi tetap konek ke
**database yang sama**.

```
admin/                          ← upload folder ini ke subdomain admin
├── index.php                   ← Halaman login
├── logout.php                  ← Proses logout
├── dashboard.php                ← Halaman ringkasan setelah login
├── includes/
│   ├── config.php               ← Koneksi database (EDIT INI)
│   ├── auth_check.php           ← Proteksi session, wajib di-include di tiap halaman
│   └── sidebar.php              ← Komponen menu sidebar
├── film/
│   ├── list.php                 ← Daftar film + pencarian
│   ├── tambah.php               ← Form tambah film + upload poster
│   ├── edit.php                 ← Form edit film
│   └── hapus.php                ← Proses hapus film
├── jadwal/
│   ├── list.php                 ← Daftar jadwal tayang
│   ├── tambah.php               ← Form tambah jadwal
│   ├── edit.php                 ← Form edit jadwal
│   └── hapus.php                ← Proses hapus jadwal
└── assets/
    ├── css/admin.css            ← Tema admin (gelap, selaras web utama)
    └── js/admin.js               ← Konfirmasi hapus, preview poster, dll

assets/img/poster/               ← UPLOAD KE WEBSITE UTAMA (bukan ke admin/)
                                    Folder ini diakses bersama oleh admin & web utama
```

---

## PENTING: Folder Poster Harus Bisa Diakses Web Utama

Karena admin panel ada di subdomain terpisah tapi poster harus tampil di
**website utama** (`namadomain.com`), kamu punya 2 pilihan struktur:

### Opsi A — Subdomain sebagai folder dalam domain utama (DIREKOMENDASIKAN)
Jika subdomain `admin.namadomain.com` dibuat sebagai folder
`public_html/admin/` (cek di cPanel > Subdomains, lihat "Document Root"),
maka folder poster otomatis bisa diakses keduanya:

```
public_html/
├── index.php, detail.php, dll      (website utama)
├── assets/img/poster/                (folder upload poster - SHARED)
└── admin/                            (subdomain admin)
    ├── index.php
    └── includes/config.php
```

Konfigurasi `POSTER_UPLOAD_DIR` di `includes/config.php` sudah otomatis
mengarah ke `../../assets/img/poster/` relatif dari folder `admin/includes/`,
yang berarti tepat ke `public_html/assets/img/poster/`. **Tidak perlu diubah.**

### Opsi B — Subdomain benar-benar terpisah (folder lain)
Jika `admin.namadomain.com` di-set ke document root yang BERBEDA (misal
`public_html/admin_subdomain/`), maka folder poster TIDAK otomatis nyambung.
Kamu harus edit path ini di `includes/config.php`:

```php
// Ganti dengan PATH ABSOLUT ke folder poster website utama
define('POSTER_UPLOAD_DIR', '/home/USERNAME_CPANEL/public_html/assets/img/poster/');
```

Tanyakan ke hosting / cek di cPanel > File Manager untuk path absolut yang benar
(biasanya terlihat di address bar File Manager, contoh: `/home/dzcx7776/public_html/...`).

---

## Langkah Instalasi

### 1. Jalankan SQL Tambahan
Buka phpMyAdmin → pilih database yang sama dengan website utama →
tab **SQL** → paste isi file `admin_panel_setup.sql` → klik **Go**.

Script ini akan:
- Menambah kolom `poster` ke tabel `film`
- Membuat tabel `admin`
- Membuat 1 akun admin default

### 2. Edit `includes/config.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username_database_kamu');
define('DB_PASS', 'password_database_kamu');
define('DB_NAME', 'nama_database_kamu');  // SAMA dengan web utama
```

### 3. Upload Folder `admin/`
Upload ke document root subdomain admin (lihat panduan struktur di atas).

### 4. Pastikan Folder Poster Writable
Folder `assets/img/poster/` (di website utama) harus punya permission
**755** atau **775** agar PHP bisa menyimpan file upload ke sana.

### 5. Login
Buka `https://admin.namadomain.com/`

```
Username : admin
Password : admin123
```

**⚠️ SEGERA GANTI PASSWORD setelah login pertama!**
Lihat instruksi cara ganti password di bagian bawah file `admin_panel_setup.sql`.

---

## Catatan Keamanan

- Semua password disimpan dalam bentuk **hash bcrypt**, tidak pernah plain text
- Semua query database menggunakan **prepared statement** (aman dari SQL Injection)
- Upload poster divalidasi lewat **MIME type asli file** (bukan hanya ekstensi),
  jadi file `.php` yang di-rename jadi `.jpg` tetap akan ditolak
- Setiap halaman admin (kecuali `index.php`/login) memanggil `auth_check.php`
  yang otomatis redirect ke login jika belum ada session
- Disarankan akses admin panel via **HTTPS** (aktifkan SSL gratis lewat
  cPanel > SSL/TLS Status > AutoSSL)
