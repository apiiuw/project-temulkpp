# Panduan Setup TemuLKPP

Panduan ini untuk menjalankan project TemuLKPP dari folder ZIP menggunakan Windows, Docker Desktop, MySQL, Laravel, dan Apache Superset. Paket sudah mencakup perbaikan dashboard Superset, responsive layout sampai tablet, mobile sidebar drawer, dan auto-provision chart Pimpinan.

## Status Paket

Paket siap dikirim setelah langkah berikut dilakukan:

- Jangan kirim file `.env.docker` berisi secret. Salin dari file `.env.docker.example`, lalu isi secret secara aman.
- Pertahankan `apache/superset-data/superset.db`; file ini berisi metadata Superset.
- Jangan perlu mengirim `web/vendor`, `web/node_modules`, atau `apache/venv`. Docker akan membuat dependency dari lockfile/image.
- Pastikan `temulkpp.sql` dikirim bersama ZIP karena diperlukan untuk restore database client.

## 1. Persyaratan

Install terlebih dahulu:

- Docker Desktop
- MySQL Server atau Laragon dengan MySQL aktif
- HeidiSQL/phpMyAdmin (opsional, untuk import database)

Pastikan port berikut tersedia:

- `3306` untuk MySQL
- `8000` untuk Laravel
- `8088` untuk Apache Superset
- `5173` untuk Vite frontend

## 2. Extract project

Extract ZIP ke folder sederhana, contoh:

```text
C:\Projects\temulkpp
```

Struktur penting:

```text
temulkpp/
├── apache/
│   └── superset-data/
│       └── superset.db
├── web/
├── temulkpp.sql
└── SETUP_CLIENT.md
```

Jangan menghapus atau memindahkan file berikut:

```text
apache/superset-data/superset.db
temulkpp.sql
```

`superset.db` berisi metadata dashboard, chart, dataset, user, koneksi database, native filter, dan konfigurasi Superset. File ini adalah database metadata SQLite milik Superset, bukan database data reservasi.

Pada paket ini metadata yang diharapkan:

```text
Dashboard  : 3
Chart      : 29
Dataset    : 4
Connection : 1 (MySQL)
```

Import `temulkpp.sql` mengisi data reservasi/agent di MySQL. Superset membaca data chart dari koneksi MySQL tersebut, sedangkan definisi dashboard/chart/dataset tetap dibaca dari `apache/superset-data/superset.db`. Keduanya wajib tersedia agar dashboard tampil dengan data.

## 3. Restore database MySQL

Pastikan MySQL sudah aktif, lalu buat database:

```sql
CREATE DATABASE temulkpp;
```

Import file berikut ke database `temulkpp`:

```text
temulkpp.sql
```

Import bisa dilakukan melalui HeidiSQL atau phpMyAdmin.

Jika menggunakan command prompt:

```cmd
mysql -u root -p temulkpp < temulkpp.sql
```

Sesuaikan username/password MySQL jika berbeda.

## 4. Siapkan konfigurasi environment

Buat file konfigurasi dari template:

```powershell
Copy-Item apache/.env.docker.example apache/.env.docker
Copy-Item web/.env.docker.example web/.env.docker
```

Generate Laravel application key before the first backend build, or fill it manually in `web/.env.docker`:

```powershell
cd web
docker compose build backend
docker compose run --rm backend php artisan key:generate --show
```

Copy the generated value into `web/.env.docker` as `APP_KEY`. Provider cache files are intentionally not included in the package; this prevents the dev-only Pail provider from breaking the production image.

Edit `apache/.env.docker` dan `web/.env.docker`. Sesuaikan minimal:

- Username/password MySQL
- Nama database: `temulkpp`
- `SUPERSET_SECRET_KEY` dengan key asli yang dipakai membuat `apache/superset-data/superset.db`
- `GUEST_TOKEN_JWT_SECRET`
- password admin Superset
- `APP_KEY` Laravel
- URL Superset

`SUPERSET_SECRET_KEY` harus sama dengan key saat `apache/superset-data/superset.db` dibuat. Simpan key dan password di kanal rahasia, bukan di repository atau ZIP publik.

Jika key diganti, Superset bisa gagal membaca kredensial database terenkripsi dan menampilkan error `Invalid decryption key`.

Untuk MySQL yang berjalan di Windows host, gunakan host:

```text
host.docker.internal
```

## 5. Jalankan Apache Superset

Buka PowerShell di folder project, lalu:

```powershell
cd apache
docker compose up -d --build
```

Cek status:

```powershell
docker compose ps
```

Lihat log jika ada masalah:

```powershell
docker compose logs -f superset
```

Superset tersedia di:

```text
http://localhost:8088
```

Saat startup, Compose otomatis menjalankan `add_pimpinan_charts.py` dan
`add_triwulan_filter.py`. Kedua script idempoten. `add_pimpinan_charts.py`
memperbaiki chart berikut:

- `Kinerja Semua Agent`: horizontal, label aktif, urutan agent descending, `Tanpa Agent` disembunyikan.
- `Tren Permasalahan per Tahun`: horizontal, label aktif, memakai plugin `echarts_timeseries_bar`.

`add_triwulan_filter.py` menambahkan kolom `triwulan` (`Triwulan I`–`Triwulan IV`,
dihitung dari `QUARTER(tanggal_jam)`) pada dataset 1/4/5 lalu memasang native
filter **Triwulan** di semua dashboard Superset — polanya sama dengan native
filter **Tahun**, sehingga keduanya saling mengunci (Tahun 2024 + Triwulan I =
Januari–Maret 2024).

Jangan menghapus `apache/superset-data/superset.db`. Jangan menjalankan `docker compose down -v`.

### Verifikasi metadata Superset

Setelah container Superset healthy, jalankan dari folder `client-package/apache`:

```powershell
docker compose exec superset python -c "from superset.app import create_app; app=create_app(); app.app_context().push(); from superset import db; from superset.models.dashboard import Dashboard; from superset.models.slice import Slice; from superset.connectors.sqla.models import SqlaTable; from superset.models.core import Database; print('dashboards=', db.session.query(Dashboard).count()); print('charts=', db.session.query(Slice).count()); print('datasets=', db.session.query(SqlaTable).count()); print('connections=', db.session.query(Database).count())"
```

Output minimum yang diharapkan:

```text
dashboards=3
charts=29
datasets=4
connections=1
```

Jika angka tersebut muncul dan MySQL sudah berisi data dari `temulkpp.sql`, dashboard Pimpinan, Agent, dan Superadmin akan memakai metadata/chart yang sama seperti paket sumber.

## 6. Jalankan Laravel

Buka PowerShell baru:

```powershell
cd ..\web
docker compose up -d --build
```

Cek status:

```powershell
docker compose ps
```

Aplikasi tersedia di:

```text
http://localhost:8000
```

Frontend Vite tersedia di:

```text
http://localhost:5173
```

Backend Docker me-mount source `app`, `bootstrap`, `config`, `resources`, dan `routes`, sehingga perubahan Blade/CSS/JS terbaru digunakan container. Provider cache Laravel dibersihkan saat image dibangun agar package dev-only seperti Pail tidak menyebabkan container 500.

## 7. Urutan akses

1. Pastikan MySQL aktif.
2. Jalankan Superset dari folder `apache`.
3. Jalankan Laravel dari folder `web`.
4. Buka `http://localhost:8000`.
5. Login melalui halaman login aplikasi.

Urutan ini penting: import MySQL lebih dulu, lalu jalankan Superset agar koneksi metadata dapat disinkronkan ke host MySQL client melalui `MYSQL_URI`.

## 8. Akun aplikasi

Gunakan akun yang tersedia pada database hasil import. Jika password tidak diketahui, gunakan data akun yang diberikan oleh administrator project.

Akun login Superset menggunakan konfigurasi pada:

```text
apache/.env.docker
```

## 9. Menghentikan aplikasi

Untuk menghentikan container tanpa menghapus data:

```powershell
cd apache
docker compose down

cd ..\web
docker compose down
```

Jangan menjalankan:

```powershell
docker compose down -v
```

Jangan menghapus folder:

```text
apache/superset-data
```

Karena folder tersebut menyimpan metadata Superset.

## 10. Troubleshooting

### Layout tidak responsive saat DevTools terbuka

Breakpoint desktop berada di `1280px`. Di bawah breakpoint tersebut sidebar berubah menjadi drawer dan mobile header muncul. Ini sengaja agar layout tetap usable saat DevTools di-dock di kanan browser.

Setelah update source, jalankan:

```powershell
cd web
docker compose up -d --build backend frontend
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan view:cache
```

Lalu hard refresh browser dengan `Ctrl + F5`.

### Container backend 500 dengan `Laravel\\Pail\\PailServiceProvider not found`

Jangan copy `bootstrap/cache/packages.php` atau `services.php` dari environment development. Hapus cache tersebut, lalu rebuild:

```powershell
cd web
Remove-Item bootstrap/cache/*.php -Force -ErrorAction SilentlyContinue
docker compose up -d --build backend
```

### Dashboard gagal disimpan

Pastikan provisioning sudah selesai dan log tidak berisi error. Metadata dashboard Superset 6.1 memakai `native_filter_configuration`, bukan `native_filters`. Jalankan ulang:

```powershell
cd ..\apache
docker compose exec superset python /app/add_pimpinan_charts.py
```

### Laravel tidak bisa konek MySQL

Periksa:

- MySQL sedang aktif
- Database `temulkpp` sudah dibuat
- `temulkpp.sql` sudah di-import
- Username/password di `web/.env.docker` benar
- Host database Docker menggunakan `host.docker.internal`

### Superset dashboard hilang

Pastikan file ini masih ada:

```text
apache/superset-data/superset.db
```

Jangan menjalankan `docker compose down -v` dan jangan mengganti `SUPERSET_SECRET_KEY`.

### Grafik di Laravel tidak muncul

Pastikan container Superset aktif:

```powershell
cd apache
docker compose ps
```

Kemudian pastikan URL berikut bisa dibuka:

```text
http://localhost:8088
```

### Perubahan source belum terlihat

Rebuild container:

```powershell
cd apache
docker compose up -d --build

cd ..\web
docker compose up -d --build
```

Validasi status akhir:

```powershell
cd ..\apache
docker compose ps
Invoke-WebRequest http://localhost:8088/health

cd ..\web
docker compose ps
Invoke-WebRequest http://localhost:8000/up
```
