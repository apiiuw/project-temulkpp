# PRD dan Panduan Menjalankan Temu LKPP

## 1. Ringkasan Produk

Temu LKPP adalah aplikasi manajemen reservasi layanan tatap muka/layanan konsultasi LKPP. Aplikasi menghubungkan pengguna umum, petugas front desk, Agent, Pimpinan, dan Superadmin dalam satu alur kerja.

Sistem terdiri dari dua layanan utama:

- **Aplikasi web Laravel** di folder `web`: reservasi, autentikasi, operasional layanan, laporan, dan integrasi Superset.
- **Apache Superset** di folder `apache`: dashboard analitik yang di-embed ke halaman dashboard internal.

Frontend menggunakan Blade, Vite, Tailwind CSS, dan Flowbite. Backend menggunakan Laravel 13 dengan PHP 8.3 serta MySQL.

## 2. Tujuan Produk

- Memudahkan pengguna membuat reservasi layanan.
- Mengelola perubahan jadwal dan status kedatangan.
- Membantu Agent menangani jadwal dan proses pertemuan.
- Memberi Pimpinan dan Superadmin metrik operasional, laporan, serta performa Agent.
- Menyediakan akses dashboard analitik berdasarkan role.

## 3. Pengguna dan Hak Akses

### Pengguna umum

- Membuka halaman reservasi.
- Membuat reservasi.
- Mengunduh bukti reservasi.
- Mengatur ulang jadwal atau membatalkan reservasi menggunakan kode reservasi.

### Front Desk

- Membuka daftar reservasi.
- Mengonfirmasi check-in berdasarkan kode reservasi.

### Agent

- Login melalui portal internal.
- Melihat dashboard dan jadwal.
- Melihat detail layanan.
- Memulai dan mengakhiri sesi tatap muka.
- Mengakses dashboard dengan pembatasan data berbasis Agent melalui Row-Level Security Superset.

### Pimpinan

- Melihat dashboard.
- Melihat layanan selesai dan layanan berjalan.
- Melihat performa Agent.
- Melihat dan mencetak laporan.

### Superadmin

- Melihat dashboard dan laporan.
- Mengelola hak akses menu.
- Mengelola master data Agent dan Pimpinan.
- Mengaktifkan/nonaktifkan akun.
- Mengatur service type dan aturan reservasi.

## 4. URL Utama

Saat Laravel berjalan di port default `8000`:

| Fungsi | URL |
| --- | --- |
| Reservasi publik | `http://127.0.0.1:8000/reservasi` |
| Atur ulang jadwal | `http://127.0.0.1:8000/atur-ulang-jadwal` |
| Front Desk | `http://127.0.0.1:8000/front-desk` |
| Login internal | `http://127.0.0.1:8000/auth/login` |
| Verifikasi tiket Agent | `http://127.0.0.1:8000/agent/konfirmasi-tiket` |
| Dashboard Agent | `http://127.0.0.1:8000/agent/dashboard` |
| Dashboard Pimpinan | `http://127.0.0.1:8000/pimpinan/dashboard` |
| Dashboard Superadmin | `http://127.0.0.1:8000/superadmin/dashboard` |
| Superset | `http://127.0.0.1:8088` |

## 5. Persyaratan Sistem

- PHP `8.3+` dengan ekstensi Laravel yang dibutuhkan.
- Composer.
- MySQL/MariaDB aktif di port `3306`.
- Node.js dan npm.
- Python untuk Apache Superset. Project lama merekomendasikan Python `3.9` sampai `3.11` di Windows.
- Visual Studio Build Tools dengan workload **Desktop development with C++** jika instalasi package Superset Windows membutuhkan kompilasi native.
- Git, bila project diambil dari repository.

## 6. Struktur Project

```text
project-temulkpp/
├── web/                  # Laravel 13, PHP 8.3, Blade, Vite
├── apache/               # Apache Superset dan konfigurasinya
├── temulkpp.sql          # Dump database MySQL
├── superset.db           # Metadata/dashboard Superset lokal
├── olah_data/            # Data sumber, termasuk file Excel
├── RUNNING_SCRIPT.md     # Panduan lama menjalankan project
└── MIGRATION_GUIDE.md    # Panduan migrasi ke Windows
```

## 7. Setup Awal

Jalankan perintah dari root project kecuali jika disebutkan lain.

### 7.1 Siapkan database MySQL

1. Pastikan MySQL aktif.
2. Buat database bernama `temulkpp`.
3. Import dump database:

```powershell
mysql -u root -p temulkpp < temulkpp.sql
```

Jika memakai Laragon, langkah yang setara dapat dilakukan melalui HeidiSQL/phpMyAdmin. Sesuaikan nama database dengan `DB_DATABASE` di `web/.env`.

### 7.2 Install dependency Laravel dan frontend

```powershell
cd web
composer install
npm install
php artisan key:generate
php artisan storage:link
```

File `web/.env` sudah ada di working copy. Project tidak memiliki `web/.env.example`, sehingga jangan menghapus `.env` tanpa membuat konfigurasi pengganti terlebih dahulu. Minimal konfigurasi database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=temulkpp
DB_USERNAME=root
DB_PASSWORD=
```

Konfigurasi integrasi Superset juga harus tersedia:

```dotenv
SUPERSET_URL=http://localhost:8088
SUPERSET_ADMIN_USERNAME=<superset-admin-username>
SUPERSET_ADMIN_PASSWORD=<superset-admin-password>
SUPERSET_DASHBOARD_ID=<dashboard-id>
SUPERSET_EMBED_ID=<dashboard-id>
```

Untuk Superset, set secret pada environment sebelum menjalankan service:

```powershell
$env:SUPERSET_SECRET_KEY = "<strong-secret>"
$env:GUEST_TOKEN_JWT_SECRET = "<strong-guest-token-secret>"
```

### 7.3 Migrasi dan seed database, bila diperlukan

Untuk database kosong:

```powershell
php artisan migrate
php artisan db:seed
```

Jika memakai `temulkpp.sql` yang sudah berisi schema dan data, jangan menjalankan migrasi reset secara sembarangan. Cek status terlebih dahulu:

```powershell
php artisan migrate:status
```

Seeder membuat 7 Agent, 3 Pimpinan, 1 Superadmin, dan menu aplikasi.

### 7.4 Siapkan Apache Superset

```powershell
cd ..\apache
python -m venv venv
.\venv\Scripts\activate
pip install -r requirements.txt
```

Project menyimpan konfigurasi Superset pada `apache/superset_config.py`. File ini memakai database metadata SQLite di `%USERPROFILE%\.superset\superset.db`. Jika metadata dashboard belum tersedia di lokasi tersebut, salin `superset.db` ke folder itu.

Inisialisasi Superset:

```powershell
$env:SUPERSET_CONFIG_PATH = "$(Get-Location)\superset_config.py"
superset db upgrade
superset init
```

Akun admin Superset harus cocok dengan `SUPERSET_ADMIN_USERNAME` dan `SUPERSET_ADMIN_PASSWORD` di `web/.env`. Dashboard ID harus cocok dengan ID yang dikirim aplikasi Laravel.

## 8. Menjalankan Aplikasi

### Mode rekomendasi: tiga terminal

Project membutuhkan Laravel, Vite, dan Superset aktif bersamaan untuk pengalaman penuh.

**Terminal 1: Laravel**

```powershell
cd web
php artisan serve
```

Buka `http://127.0.0.1:8000`.

**Terminal 2: Vite**

```powershell
cd web
npm run dev
```

Biarkan proses tetap hidup selama development agar asset CSS/JavaScript diproses oleh Vite.

**Terminal 3: Superset**

```powershell
cd apache
.\venv\Scripts\activate
$env:SUPERSET_CONFIG_PATH = "$(Get-Location)\superset_config.py"
superset run -p 8088 --with-threads --reload --debugger
```

Buka Superset di `http://127.0.0.1:8088`. Dashboard internal tidak akan tampil lengkap jika service ini mati.

### Mode satu perintah Composer

`web/composer.json` menyediakan script `dev` yang menjalankan Laravel, queue listener, log viewer, dan Vite secara bersamaan:

```powershell
cd web
composer run dev
```

Mode ini tidak menjalankan Superset. Superset tetap perlu dijalankan di terminal terpisah jika dashboard analitik digunakan.

### Mode production asset

Build asset frontend:

```powershell
cd web
npm run build
```

Untuk deployment production, gunakan web server PHP yang sesuai, nonaktifkan `APP_DEBUG`, gunakan secret berbeda, dan jangan memakai `--debugger` pada Superset.

## 9. Akun Seed Development

Seeder project mendefinisikan akun berikut:

| Role | Email | Password |
| --- | --- | --- |
| Superadmin | `superadmin@temulkpp.com` | `password` |
| Agent 1-7 | `agent.1@temulkpp.com` sampai `agent.7@temulkpp.com` | `Agent@12345` |
| Pimpinan 1-3 | `pimpinan.1@temulkpp.com` sampai `pimpinan.3@temulkpp.com` | `Pimpinan@12345` |

Kredensial ini hanya untuk development. Ganti password dan secret sebelum deployment.

## 10. Verifikasi dan Testing

Laravel memiliki test Unit dan Feature berbasis Pest/PHPUnit. Jalankan:

```powershell
cd web
php artisan test
```

Test yang terlihat dalam repository mencakup validasi jadwal reservasi, pembuatan PDF reservasi, dan proses check-in Front Desk.

Pemeriksaan tambahan:

```powershell
php artisan route:list
php artisan config:clear
php artisan about
```

Catatan: `phpstan.neon` tidak ditemukan dalam project, sehingga perintah PHPStan level 9 belum dapat dijalankan tanpa menambahkan konfigurasi dan dependency PHPStan terlebih dahulu.

## 11. Troubleshooting

### `php` atau `composer` tidak ditemukan

Tambahkan PHP dan Composer ke PATH Windows, lalu buka terminal baru. Laragon dapat membantu menyediakan PHP dan MySQL lokal.

### Database connection error

Periksa MySQL aktif, database `temulkpp` ada, dan nilai `DB_*` di `web/.env` benar.

### Asset CSS/JavaScript tidak tampil

Pastikan `npm install` sudah selesai dan `npm run dev` berjalan. Untuk asset statis, jalankan `npm run build`.

### `SQLSTATE` terkait session/cache/jobs

Environment saat ini memakai `SESSION_DRIVER=database`, `CACHE_STORE=database`, dan `QUEUE_CONNECTION=database`. Pastikan tabel terkait sudah tersedia melalui migrasi atau gunakan dump database yang benar.

### Dashboard Superset gagal dimuat

Periksa tiga hal: Superset aktif di port `8088`, kredensial admin cocok, dan `SUPERSET_DASHBOARD_ID` cocok dengan dashboard Superset. Periksa log Laravel di `web/storage/logs` jika endpoint guest token mengembalikan HTTP 500.

### Superset gagal di Windows

Gunakan Python versi yang didukung, aktifkan virtual environment, install Visual Studio Build Tools, lalu ulangi `pip install -r requirements.txt`.

## 12. Risiko dan Catatan Keamanan

- `web/.env` berisi `APP_KEY`, kredensial Superset, dan secret konfigurasi. Jangan commit atau membagikan file tersebut.
- `apache/superset_config.py` membaca `SUPERSET_SECRET_KEY` dan `GUEST_TOKEN_JWT_SECRET` dari environment. Isi secret kuat sebelum deployment.
- `WTF_CSRF_ENABLED=False` di konfigurasi Superset menurunkan perlindungan CSRF. Evaluasi ulang sebelum production.
- `APP_DEBUG=true`, `LOG_LEVEL=debug`, dan password seed sederhana hanya cocok untuk development.
- CORS dan Content Security Policy saat ini mengizinkan origin localhost. Sesuaikan dengan domain deployment.
- Dump database dan file `superset.db` dapat mengandung data operasional/sensitif. Atur akses file dan backup secara terbatas.

## 13. Definition of Done Operasional

- MySQL aktif dan database dapat diakses Laravel.
- `php artisan route:list` berjalan tanpa error.
- Halaman `/reservasi` dapat dibuka.
- Reservasi dapat dibuat dan bukti dapat diunduh.
- Login Agent, Pimpinan, dan Superadmin berhasil.
- Front Desk dapat mengonfirmasi check-in.
- Superset dapat dibuka di port `8088`.
- Dashboard internal memperoleh guest token tanpa error.
- `php artisan test` selesai tanpa kegagalan.
- Secret development tidak dipakai pada production.
