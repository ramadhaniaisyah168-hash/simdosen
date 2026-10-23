# SIM Dosen — Universitas Muhammadiyah Bengkulu

Tugas Praktikum 2345: Web Tradisional (PHP + MySQL) dan Web Service/REST API untuk manajemen data dosen.
Berjalan di **XAMPP (lokal)** dan **InfinityFree (hosting)** dengan kode yang sama — lingkungan dikenali otomatis.

## Akun demo
| Role | Username | Password | Hak akses |
|---|---|---|---|
| Admin | `admin` | `admin123` | Semua menu: dosen, data master, pengguna, role, laporan |
| Operator | `operator` | `operator123` | CRUD dosen + data master, laporan |
| Pimpinan | `pimpinan` | `pimpinan123` | Hanya lihat dosen, data master, laporan |
| Dosen | `dosen1` / `dosen2` | `dosen123` | Hanya profil sendiri + ganti password |

## A. Menjalankan di lokal (XAMPP)
1. Salin folder `simdosen` ke `C:\xampp\htdocs\` sehingga ada `C:\xampp\htdocs\simdosen\index.php`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka phpMyAdmin → pilih basisdata **`sim_dosen`** (buat dulu bila belum ada, collation `utf8mb4_general_ci`).
4. Tab **Impor** → pilih `database/sim_dosen.sql` → **Kirim**.
   > Tabel lama bernama sama di basisdata itu akan dihapus dan dibuat ulang. Ekspor dulu bila ada data yang ingin disimpan.
5. Buka **http://localhost/simdosen/cek.php** — semua baris harus **OK**.
6. Buka **http://localhost/simdosen/login.php** dan masuk dengan akun demo.

Pengaturan lokal (di `includes/config.php`, bagian LOKAL): host `127.0.0.1`, port `3307` lalu `3306` (dicoba berurutan), basisdata `sim_dosen`, user `root`, password kosong. Ubah `DB_NAME` bila memakai nama basisdata lain.

## B. Hosting di InfinityFree
1. Panel InfinityFree → **MySQL Databases**: catat *MySQL Hostname* (mis. `sql123.infinityfree.com`), *username* (`if0_43191408`), dan nama basisdata `if0_43191408_sim_dosen`.
2. Buka `includes/config.php`, bagian **HOSTING**, isi `DB_HOST` dan `DB_PASS` (nilainya sama dengan proyek SIM Mahasiswa). Periksa `DB_NAME` dan `DB_USER`.
3. phpMyAdmin hosting → pilih basisdata `if0_43191408_sim_dosen` → **Impor** `database/sim_dosen.sql`.
4. File Manager / FTP → unggah **isi** folder `simdosen` (bukan folder luarnya) ke `htdocs/`, sehingga `htdocs/index.php` ada.
5. Buka `https://domain-anda/cek.php` → semua **OK** → lalu **hapus `cek.php`** dari hosting.
6. Login di `https://domain-anda/login.php`.

## Struktur
```
index.php  login.php  logout.php  dashboard.php  profil.php
dosen.php  dosen_form.php  dosen_detail.php  dosen_hapus.php
master.php          (CRUD 6 tabel master + roles)
pengguna.php  pengguna_form.php  pengguna_hapus.php
laporan.php  laporan_csv.php
cek.php             (diagnosis; hapus di hosting)
api/                (REST API JSON)
includes/           (config, db, auth + RBAC, helpers, layout)
assets/             (style.css, app.js)
database/sim_dosen.sql
```

## Basisdata (9 tabel)
`roles`, `pengguna`, `dosen`, `jabatan_fungsional`, `kepangkatan`, `pendidikan`, `rumpun_ilmu`, `status_kepegawaian`, `penempatan`.
Kolom tabel `dosen` mengikuti "Daftar Dosen Aktif": Nomor Registrasi, NUPTK, Nama, NIP, Jabatan Fungsional, Kepangkatan, Pendidikan Terakhir, Rumpun Ilmu, Status Serdos, Status Pegawai, Ikatan Kerja, Status Aktivitas, Penempatan.

## Keamanan yang diterapkan
Prepared statement (PDO) di semua query · `password_hash`/`password_verify` · token CSRF pada semua form · `session_regenerate_id` saat login · pembatasan percobaan login (5x gagal → tunggu 30 detik) · RBAC di tiap halaman dan API · escape output (`htmlspecialchars`) · folder `includes/` dan `database/` diblokir lewat `.htaccess`.

## REST API
Autentikasi memakai sesi (cookie). Kirim `Content-Type: application/json` untuk POST/PUT/DELETE.

| Metode | Endpoint | Keterangan |
|---|---|---|
| POST | `api/login.php` | `{"username":"admin","password":"admin123"}` |
| POST | `api/logout.php` | keluar |
| GET | `api/statistik.php` | statistik publik (tanpa data pribadi) |
| GET | `api/dosen.php?q=&jabatan=&penempatan=&aktivitas=&serdos=&page=1&per_page=10` | daftar dosen |
| GET | `api/dosen.php?id=1` | detail dosen |
| POST | `api/dosen.php` | tambah dosen |
| PUT | `api/dosen.php?id=1` | ubah dosen (field yang tidak dikirim tetap) |
| DELETE | `api/dosen.php?id=1` | hapus dosen |
| GET/POST/PUT/DELETE | `api/master.php?t=jabatan_fungsional&id=..` | data master (`nama`, `aktif`) |

Contoh (curl):
```
curl -c c.txt -H "Content-Type: application/json" -d '{"username":"admin","password":"admin123"}' http://localhost/simdosen/api/login.php
curl -b c.txt "http://localhost/simdosen/api/dosen.php?per_page=5"
```
Bila hosting memblokir PUT/DELETE, kirim `POST` dengan `?_method=PUT` atau header `X-HTTP-Method-Override`.
Catatan: InfinityFree memasang tantangan anti-bot pada klien non-browser (Postman/curl); uji API dari browser atau lewat `fetch()` di halaman sendiri.

## Pemecahan masalah
- **Tidak bisa login** → buka `cek.php`. Bila baris "Akun demo" GAGAL di lokal, klik **Kembalikan password akun demo**.
- **Koneksi gagal / Unknown database** → pastikan basisdata dibuat dan `sim_dosen.sql` sudah diimpor; cek `includes/config.php`.
- **Access denied di hosting** → host/password MySQL di `config.php` belum sama dengan panel InfinityFree.
- **Halaman putih / 500 di hosting** → pilih PHP 7.4 atau 8.x di panel, lalu muat ulang.
