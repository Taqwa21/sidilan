# SIDILAN

### Sistem Digital Monitoring Tindak Lanjut Surat

SIDILAN adalah aplikasi berbasis web untuk membantu pengelolaan, pencatatan, pemantauan, dan tindak lanjut surat antara Deputi Bidang Logistik dan Peralatan dengan Direktorat terkait di BNPB.

Aplikasi ini dikembangkan untuk mempermudah proses administrasi surat secara digital, mulai dari pencatatan surat masuk dan keluar, penerimaan surat, disposisi, tindak lanjut, hingga monitoring dan pelaporan.

## Status Pengembangan

SIDILAN masih dalam tahap pengembangan dan pengujian.

- Pengembangan dilakukan secara lokal menggunakan XAMPP.
- Database pengembangan menggunakan MySQL.
- Repository kode menggunakan GitHub.
- Server produksi dan domain belum ditentukan.
- Konfigurasi lokal dan produksi dipisahkan melalui file `.env`.

## Fitur Utama

### 1. Manajemen Surat

- Pencatatan surat masuk dan surat keluar.
- Pengelolaan nomor agenda dan nomor surat.
- Informasi klasifikasi, tanggal surat, asal surat, dan perihal.
- Pengisian uraian berkas dan posisi surat.
- Pengelolaan dokumen surat.
- Pengaturan hak akses surat berdasarkan unit kerja.
- Pengelolaan surat dengan status draft sebelum dikirim.

### 2. Disposisi Surat

- Pembuatan disposisi surat.
- Pengisian instruksi dan tujuan disposisi.
- Penentuan unit atau petugas penerima disposisi.
- Pengisian batas waktu penyelesaian.
- Pemantauan status disposisi.

### 3. Tindak Lanjut

- Pencatatan aktivitas tindak lanjut.
- Pembaruan status penyelesaian.
- Penambahan keterangan tindak lanjut.
- Upload bukti tindak lanjut secara opsional.
- Pemantauan perkembangan tindak lanjut surat.

### 4. Monitoring Surat

- Pemantauan status surat.
- Informasi posisi surat.
- Monitoring proses disposisi dan tindak lanjut.
- Informasi penerima disposisi dan penanggung jawab.
- Pemantauan batas waktu penyelesaian.
- Indikator persentase penyelesaian surat.
- Pencarian dan penyaringan data berdasarkan status dan rentang tanggal.

### 5. Laporan dan Riwayat

- Rekapitulasi data surat.
- Laporan berdasarkan status dan unit kerja.
- Riwayat perubahan status surat.
- Pencatatan aktivitas pengguna.
- Pemantauan perjalanan surat melalui riwayat aktivitas.

### 6. Manajemen Pengguna

- Administrator.
- Petugas Deputi Bidang Logistik dan Peralatan.
- Petugas Direktorat.
- Pembatasan akses berdasarkan peran dan unit kerja.

### 7. Pengelolaan Dokumen

- Upload dokumen.
- Preview dokumen.
- Download dokumen.
- Pengelolaan surat asli, bukti penerimaan, dan bukti tindak lanjut.
- Upload dokumen bersifat opsional sesuai kebutuhan proses surat.
- Ukuran maksimal file mengikuti validasi aplikasi.

### 8. Tampilan dan Antarmuka

- Dashboard ringkasan informasi surat.
- Tampilan tabel yang responsif.
- Mode Compact dan Full.
- Preferensi tampilan tersimpan pada browser masing-masing pengguna.
- Tooltip informasi pada mode Compact.
- Tampilan konsisten pada seluruh halaman aplikasi.

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
| Version Control       | Git dan GitHub         |

## Persyaratan Sistem

Sebelum melakukan instalasi, pastikan perangkat sudah memiliki:

1. Windows 10 atau Windows 11.
2. XAMPP dengan PHP minimal 8.2.
3. Composer.
4. Node.js dan NPM.
5. Git.
6. Visual Studio Code atau text editor lainnya.
7. Browser seperti Google Chrome.

Pastikan Apache dan MySQL tersedia melalui XAMPP.

## Panduan Instalasi Lokal

### 1. Install Kebutuhan Aplikasi

Unduh dan instal kebutuhan berikut:

- XAMPP: https://www.apachefriends.org/
- Composer: https://getcomposer.org/download/
- Node.js: https://nodejs.org/
- Git: https://git-scm.com/
- Visual Studio Code: https://code.visualstudio.com/

Periksa instalasi melalui terminal:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

### 2. Clone Repository

Buka PowerShell, kemudian masuk ke folder `htdocs` XAMPP.

Contoh:

```powershell
cd C:\xampp8212\htdocs
```

Clone repository:

```bash
git clone https://github.com/Taqwa21/sidilan.git
```

Masuk ke folder proyek:

```bash
cd sidilan
```

Catatan: Repository SIDILAN bersifat privat sehingga pengguna harus memiliki akses GitHub.

### 3. Install Dependency Laravel

Jalankan:

```bash
composer install
```

### 4. Buat File Environment

Salin `.env.example` menjadi `.env`.

PowerShell:

```powershell
Copy-Item .env.example .env
```

Sesuaikan konfigurasi lokal:

```dotenv
APP_NAME=SiDILAN
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sidilan
DB_USERNAME=root
DB_PASSWORD=
```

Jika MySQL menggunakan password, sesuaikan nilai `DB_PASSWORD`.

### 5. Buat Database

1. Jalankan Apache dan MySQL melalui XAMPP.
2. Buka browser.
3. Akses http://localhost/phpmyadmin.
4. Pilih menu Database.
5. Buat database baru dengan nama `sidilan`.

### 6. Generate Application Key

Jalankan:

```bash
php artisan key:generate
```

Perintah tersebut akan membuat APP_KEY secara otomatis pada file `.env`.

### 7. Jalankan Migration dan Seeder

Jalankan:

```bash
php artisan migrate --seed
```

Perintah tersebut akan membuat struktur tabel dan mengisi data awal aplikasi, meliputi:

- Role pengguna.
- Unit kerja.
- Klasifikasi surat.
- Target disposisi.

Seeder akun demo hanya dijalankan ketika aplikasi menggunakan lingkungan `local`.

Jangan menggunakan `migrate:fresh` pada database yang sudah berisi data penting karena perintah tersebut dapat menghapus seluruh tabel.

### 8. Install Dependency Frontend

Jalankan:

```bash
npm install
npm run build
```

### 9. Jalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Buka browser:

http://127.0.0.1:8000

SIDILAN sekarang sudah dapat digunakan secara lokal.

## Akun Demo Lokal

Akun berikut disediakan untuk kebutuhan demonstrasi dan pengujian lokal.

| Peran            | Email                  | Password    |
| ---------------- | ---------------------- | ----------- |
| Administrator    | admin@sidilan.local    | admin123    |
| Petugas Logistik | logistik@sidilan.local | logistik123 |
| Petugas Pusat    | pusat@sidilan.local    | pusat123    |

Akun demo dibuat melalui seeder yang hanya dapat dijalankan pada lingkungan `local`.

Catatan: Akun dan password tersebut hanya untuk pengujian lokal. Jangan digunakan pada lingkungan produksi.

## Persiapan Produksi

SIDILAN dirancang agar dapat dipasang pada server yang mendukung PHP, Laravel, dan MySQL.

Konfigurasi produksi menggunakan file `.env` tersendiri dan tidak menggunakan konfigurasi database lokal XAMPP.

Persiapan produksi meliputi:

- Menyediakan server dengan PHP dan ekstensi yang dibutuhkan Laravel.
- Menyediakan database MySQL.
- Mengatur domain dan HTTPS.
- Mengatur environment variables.
- Mengatur document root ke direktori `public`.
- Menjalankan migration dan seeder data master.
- Membuat akun Administrator pertama melalui perintah Artisan.

Perintah inisialisasi database produksi:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Setelah database siap, buat akun Administrator pertama:

```bash
php artisan sidilan:buat-admin
```

Perintah tersebut hanya dapat dijalankan ketika `APP_ENV=production`.

Setelah Administrator berhasil dibuat, akun petugas dapat ditambahkan melalui halaman manajemen pengguna aplikasi.

Detail deployment akan disesuaikan setelah server dan penyedia hosting ditentukan.

## Struktur Proyek

```text
sidilan/
├── app/
│   ├── Console/
│   │   └── Commands/
│   ├── Http/
│   │   └── Controllers/
│   └── Models/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── public/
├── resources/
│   └── views/
├── routes/
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
