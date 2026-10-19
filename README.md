# Artikel App

Website artikel berbasis **Laravel 13 + MySQL + Blade + Tailwind CSS v4** untuk ujian masuk IT CMI.
Pengunjung dapat membaca artikel yang sudah *Published*. User yang login dapat mengelola artikelnya sendiri
dan melihat laporan artikel (INNER JOIN `articles` × `users`).

---

## 1. Kebutuhan Sistem

| Software | Versi minimal | Cek dengan |
|---|---|---|
| PHP | 8.3 | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL / MariaDB | 8.0 / 10.6 | `mysql --version` |
| Node.js + npm | 20.19 | `node -v` dan `npm -v` |
| Git | — | `git --version` |

Ekstensi PHP yang dibutuhkan (biasanya sudah aktif): `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`.

> **Windows:** paket seperti [Laragon](https://laragon.org) atau [XAMPP](https://www.apachefriends.org) sudah menyertakan
> PHP dan MySQL. Pastikan `php` dan `composer` bisa dipanggil dari terminal.
>
> **macOS (Homebrew):** `brew install php composer mysql node`, lalu jalankan MySQL dengan `brew services start mysql`.

---

## 2. Instalasi dari Awal

### Langkah 1 — Clone repository

```bash
git clone https://github.com/AliRodja/artikel-app.git
cd artikel-app
```

### Langkah 2 — Install dependency PHP

```bash
composer install
```

Perintah ini membaca `composer.lock` dan mengunduh Laravel beserta library lain ke folder `vendor/`.

### Langkah 3 — Buat file konfigurasi `.env`

```bash
# macOS / Linux / Git Bash
cp .env.example .env

# Windows (Command Prompt)
copy .env.example .env
```

Lalu buat *application key* (dipakai untuk enkripsi session & cookie):

```bash
php artisan key:generate
```

### Langkah 4 — Jalankan MySQL dan buat database

Pastikan service MySQL sudah berjalan:

- **macOS (Homebrew):** `brew services start mysql`
- **Laragon / XAMPP:** klik *Start* pada MySQL di control panel.

Buat database kosong bernama `artikel_app`, pilih salah satu cara:

**Cara A — lewat terminal**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE artikel_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

> Jika user `root` tidak memakai password, jalankan `mysql -u root` (tanpa `-p`).

**Cara B — lewat phpMyAdmin / HeidiSQL / TablePlus**

Buat database baru dengan nama `artikel_app` dan collation `utf8mb4_unicode_ci`.

### Langkah 5 — Hubungkan aplikasi ke database

Buka file `.env` dan sesuaikan bagian berikut dengan MySQL di komputer Anda:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=artikel_app
DB_USERNAME=root
DB_PASSWORD=          # isi jika user MySQL Anda memakai password
```

Kosongkan cache konfigurasi agar perubahan `.env` terbaca, lalu cek koneksi:

```bash
php artisan config:clear
php artisan db:show
```

Jika koneksi berhasil, akan tampil informasi seperti:

```
MySQL ............ 8.x
Connection ....... mysql
Database ......... artikel_app
Host ............. 127.0.0.1
```

Jika muncul error, lihat bagian [Troubleshooting](#6-troubleshooting).

### Langkah 6 — Buat tabel dan isi data contoh

```bash
php artisan migrate --seed
```

- `migrate` membuat tabel `users`, `articles`, `password_reset_tokens`, `sessions`, `cache`, dan `jobs`.
- `--seed` mengisi data contoh: 1 akun demo dengan 20 artikel, 10 artikel milik user lain,
  dan 3 artikel **tanpa penulis** (untuk membuktikan INNER JOIN pada halaman Laporan Artikel).

Untuk mengulang database dari nol kapan saja:

```bash
php artisan migrate:fresh --seed
```

### Langkah 7 — Aktifkan akses gambar upload

```bash
php artisan storage:link
```

Gambar artikel disimpan di `storage/app/public/articles`. Perintah ini membuat shortcut `public/storage`
agar gambar tersebut bisa dibuka dari browser.

### Langkah 8 — Install dan build aset front-end (Tailwind CSS)

```bash
npm install
npm run build
```

### Langkah 9 — Jalankan aplikasi

```bash
php artisan serve
```

Buka **http://localhost:8000** di browser.

> **Mode development:** jika ingin mengubah tampilan, jalankan `npm run dev` di terminal terpisah
> (biarkan tetap berjalan) agar perubahan CSS langsung terlihat tanpa build ulang.

---

## 3. Akun Demo

| Email | Password |
|---|---|
| `admin@example.com` | `password` |

User lain hasil seeder juga memakai password `password` (email-nya dapat dilihat di tabel `users`).

---

## 4. Menguji Lupa & Reset Password

Aplikasi memakai `MAIL_MAILER=log`, sehingga email **tidak benar-benar dikirim**, melainkan ditulis ke file log.

1. Buka halaman **Masuk → Lupa password?** dan masukkan `admin@example.com`.
2. Buka file `storage/logs/laravel.log`, cari baris paling bawah yang berisi `reset-password/...`.
3. Salin link tersebut ke browser, isi password baru, lalu masuk dengan password baru.

Link reset berlaku 60 menit dan hanya bisa dipakai satu kali.

---

## 5. Ringkasan Perintah

```bash
git clone https://github.com/AliRodja/artikel-app.git
cd artikel-app
composer install
cp .env.example .env
php artisan key:generate
# buat database "artikel_app" di MySQL, lalu sesuaikan DB_* di .env
php artisan config:clear
php artisan db:show
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

---

## 6. Troubleshooting

| Pesan error | Penyebab | Solusi |
|---|---|---|
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL belum berjalan, atau host/port salah | Jalankan MySQL (`brew services start mysql` / Start di Laragon/XAMPP), cek `DB_HOST` & `DB_PORT` |
| `SQLSTATE[HY000] [1045] Access denied for user 'root'` | Username/password MySQL salah | Sesuaikan `DB_USERNAME` & `DB_PASSWORD` di `.env`, lalu `php artisan config:clear` |
| `SQLSTATE[HY000] [1049] Unknown database 'artikel_app'` | Database belum dibuat | Ulangi [Langkah 4](#langkah-4--jalankan-mysql-dan-buat-database) |
| `could not find driver` | Ekstensi `pdo_mysql` belum aktif | Aktifkan `extension=pdo_mysql` di `php.ini`, lalu buka ulang terminal |
| `No application encryption key has been specified` | `APP_KEY` di `.env` masih kosong | `php artisan key:generate` |
| `Vite manifest not found` | Aset front-end belum di-build | `npm install && npm run build` |
| Gambar artikel tidak muncul (404) | Symlink storage belum dibuat | `php artisan storage:link` |
| `419 Page Expired` saat submit form | Session kedaluwarsa / tabel `sessions` belum ada | Refresh halaman; pastikan `php artisan migrate` sudah dijalankan |
| Perubahan `.env` tidak berpengaruh | Konfigurasi masih ter-cache | `php artisan optimize:clear` |
