# SIDILAN

### Sistem Digital Tindak Lanjut

SIDILAN (Sistem Digital Tindak Lanjut) adalah aplikasi berbasis web untuk membantu pengelolaan, pencatatan, pemantauan, dan tindak lanjut surat antara BNPB Logistik dan BNPB Pusat.

Aplikasi ini dikembangkan untuk mempermudah proses administrasi surat secara digital, mulai dari pencatatan surat masuk dan keluar, penerimaan surat, disposisi, tindak lanjut, hingga monitoring dan pelaporan.

## Preview Online

Aplikasi SIDILAN dapat diakses melalui:

https://sidilan-production.up.railway.app

Preview online digunakan untuk keperluan demonstrasi dan evaluasi aplikasi.

## Fitur Utama

### 1. Manajemen Surat

- Pencatatan surat masuk dan surat keluar.
- Pengelolaan nomor agenda dan nomor surat.
- Informasi klasifikasi, tanggal surat, asal surat, dan perihal.
- Pengisian uraian berkas dan posisi surat.
- Pengelolaan dokumen surat dalam format PDF.

### 2. Disposisi Surat

- Pembuatan disposisi surat.
- Pengisian instruksi dan tujuan disposisi.
- Penentuan batas waktu penyelesaian.
- Pengelolaan petugas atau unit yang ditugaskan.

### 3. Tindak Lanjut

- Pencatatan aktivitas tindak lanjut.
- Pembaruan status penyelesaian.
- Penambahan keterangan tindak lanjut.
- Upload bukti tindak lanjut dalam format PDF secara opsional.

### 4. Monitoring Surat

- Pemantauan status surat.
- Informasi posisi surat.
- Monitoring proses disposisi dan tindak lanjut.
- Pencarian dan penyaringan data surat.

### 5. Laporan dan Riwayat

- Rekapitulasi data surat.
- Laporan berdasarkan status dan unit.
- Riwayat perubahan status surat.
- Pencatatan aktivitas pengguna.

### 6. Manajemen Pengguna

- Administrator.
- Petugas BNPB Logistik.
- Petugas BNPB Pusat.
- Pembatasan akses berdasarkan peran dan unit kerja.

### 7. Pengelolaan Dokumen

- Upload dokumen PDF.
- Preview dan download dokumen.
- Pengelolaan bukti penerimaan dan bukti tindak lanjut.
- Maksimal 10 dokumen per surat dengan ukuran maksimal 10 MB per file.

### 8. Tampilan Tabel

- Mode Compact untuk tampilan tabel yang lebih ringkas.
- Mode Full untuk menampilkan isi tabel secara lebih lengkap.
- Preferensi tampilan tersimpan pada browser masing-masing pengguna.
- Tooltip informasi pada mode Compact.

## Teknologi yang Digunakan

| Komponen              | Teknologi              |
| --------------------- | ---------------------- |
| Backend               | PHP 8.2+               |
| Framework             | Laravel 12             |
| Database              | MySQL                  |
| Template              | Blade                  |
| CSS Framework         | Bootstrap 5.3.8        |
| CSS Utility           | Tailwind CSS 4         |
| JavaScript            | JavaScript             |
| Build Tool            | Vite 6                 |
| HTTP Client           | Axios                  |
| Grafik                | Chart.js 4.6.1         |
| Icon                  | Bootstrap Icons 1.13.2 |
| PHP Dependency        | Composer               |
| JavaScript Dependency | NPM                    |
| Hosting Preview       | Railway                |
| Version Control       | Git dan GitHub         |

## Persyaratan Sistem

Sebelum melakukan instalasi, pastikan laptop sudah memiliki:

1. Windows 10 atau Windows 11.
2. XAMPP dengan PHP minimal 8.2.
3. Composer.
4. Node.js dan NPM.
5. Git.
6. Visual Studio Code atau text editor lainnya.
7. Browser seperti Google Chrome.

Pastikan Apache dan MySQL tersedia melalui XAMPP.

## Panduan Instalasi Lokal

### 1. Install kebutuhan aplikasi

Install terlebih dahulu:

- XAMPP: https://www.apachefriends.org/
- Composer: https://getcomposer.org/download/
- Node.js: https://nodejs.org/
- Git: https://git-scm.com/
- Visual Studio Code: https://code.visualstudio.com/

Setelah instalasi selesai, buka Command Prompt atau PowerShell dan periksa:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

### 2. Clone repository

Buka terminal, kemudian masuk ke folder `htdocs` milik XAMPP.

Contoh:

```powershell
cd C:\xampp\htdocs
```

Clone repository SIDILAN:

```bash
git clone https://github.com/Taqwa21/sidilan.git
```

Masuk ke folder proyek:

```bash
cd sidilan
```

### 3. Install dependency Laravel

Jalankan:

```bash
composer install
```

### 4. Buat file environment

Salin file `.env.example` menjadi `.env`.

Pada Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Sesuaikan konfigurasi aplikasi dan database:

```env
APP_NAME=SIDILAN
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sidilan
DB_USERNAME=root
DB_PASSWORD=
```

Jika MySQL menggunakan password, sesuaikan nilai `DB_PASSWORD`.

### 5. Buat database

1. Jalankan Apache dan MySQL melalui XAMPP.
2. Buka browser.
3. Akses http://localhost/phpmyadmin
4. Pilih menu Database.
5. Buat database baru dengan nama `sidilan`.

### 6. Generate application key

Jalankan:

```bash
php artisan key:generate
```

### 7. Jalankan migration dan seeder

Jalankan:

```bash
php artisan migrate --seed
```

Perintah ini akan:

- Membuat seluruh tabel database.
- Mengisi data role pengguna.
- Mengisi data unit kerja.
- Mengisi data klasifikasi surat.
- Membuat atau memperbarui akun administrator.
- Membuat atau memperbarui akun petugas Logistik dan Pusat.
- Mengisi target disposisi awal.

Catatan: Perintah ini digunakan untuk instalasi database baru. Jangan menggunakan `migrate:fresh` pada database yang sudah berisi data penting karena dapat menghapus seluruh tabel.

### 8. Install dependency frontend

Jalankan:

```bash
npm install
npm run build
```

### 9. Jalankan aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Buka browser:

http://127.0.0.1:8000

SIDILAN sekarang sudah dapat digunakan secara lokal.

## Akun Demo

Akun berikut disediakan untuk kebutuhan demonstrasi dan pengujian.

| Peran            | Email                  | Password    |
| ---------------- | ---------------------- | ----------- |
| Administrator    | admin@sidilan.local    | admin123    |
| Petugas Logistik | logistik@sidilan.local | logistik123 |
| Petugas Pusat    | pusat@sidilan.local    | pusat123    |

Seeder menggunakan mekanisme update atau insert berdasarkan email. Jika akun sudah tersedia, data nama, password, role, dan unit akan diperbarui sesuai konfigurasi seeder.

Catatan: Password sederhana ini hanya untuk kebutuhan preview dan pengujian. Jangan digunakan untuk lingkungan produksi.

## Deployment Railway

SIDILAN menggunakan Railway sebagai platform hosting untuk preview online.

Konfigurasi deployment meliputi:

- Repository GitHub sebagai sumber kode.
- Laravel sebagai aplikasi backend.
- MySQL Railway sebagai database online.
- Environment Variables untuk konfigurasi aplikasi dan database.
- Pre-deploy migration menggunakan perintah:

```bash
php artisan migrate --force
```

Setiap perubahan yang di-push ke branch `main` GitHub akan memicu deployment otomatis melalui Railway apabila integrasi deployment aktif.

Alamat preview:

https://sidilan-production.up.railway.app

## Struktur Proyek

```text
sidilan/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   └── views/
│       ├── layouts/
│       └── letters/
├── routes/
│   └── web.php
├── storage/
├── tests/
├── .env.example
├── artisan
├── composer.json
├── package.json
└── README.md
```

## Status Surat

SIDILAN menggunakan beberapa tahapan status surat:

- Draft
- Terkirim
- Menunggu Konfirmasi
- Diterima
- Didisposisikan
- Dalam Proses
- Menunggu Tindak Lanjut
- Selesai
- Dibatalkan

Setiap perubahan status dicatat dalam riwayat surat untuk mempermudah pemantauan aktivitas.

## Pengembangan Selanjutnya

SIDILAN masih dalam tahap pengembangan. Fitur dan dokumentasi akan diperbarui secara berkala mengikuti kebutuhan sistem dan hasil evaluasi.

## Repository

GitHub: https://github.com/Taqwa21/sidilan

---

Dikembangkan menggunakan Laravel untuk mendukung digitalisasi administrasi surat dan monitoring tindak lanjut.
