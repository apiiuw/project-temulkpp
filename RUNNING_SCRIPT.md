# Menjalankan TemuLKPP dengan Docker

Project ini menggunakan Docker untuk Laravel dan Apache Superset. Tidak perlu menjalankan `php artisan serve`, `npm run dev`, membuat Python virtualenv, atau menjalankan `superset run` secara manual.

## Prasyarat

- Docker Desktop aktif
- MySQL aktif di komputer host
- Database `temulkpp` sudah dibuat
- File `temulkpp.sql` sudah di-import

## 1. Jalankan MySQL

Pastikan MySQL aktif dan database `temulkpp` tersedia.

Jika database belum dibuat:

```sql
CREATE DATABASE temulkpp;
```

Import file:

```text
temulkpp.sql
```

## 2. Jalankan Apache Superset

Buka PowerShell pertama dari root project:

```powershell
cd apache
docker compose up -d --build
```

Cek status:

```powershell
docker compose ps
```

Superset berjalan di:

```text
http://localhost:8088
```

## 3. Jalankan Laravel

Buka PowerShell kedua dari root project:

```powershell
cd web
docker compose up -d --build
```

Cek status:

```powershell
docker compose ps
```

Aplikasi Laravel berjalan di:

```text
http://localhost:8000
```

Frontend Vite berjalan di:

```text
http://localhost:5173
```

## 4. Urutan penggunaan

1. MySQL aktif
2. Container Superset aktif
3. Container Laravel aktif
4. Buka `http://localhost:8000`

## 5. Melihat log

Log Superset:

```powershell
cd apache
docker compose logs -f superset
```

Log Laravel:

```powershell
cd web
docker compose logs -f backend
```

## 6. Menghentikan aplikasi

```powershell
cd apache
docker compose down

cd ..\web
docker compose down
```

Jangan gunakan `docker compose down -v` karena dapat menghapus data volume Docker.

Jangan hapus file:

```text
apache/superset-data/superset.db
```

File tersebut menyimpan dashboard, chart, dataset, user, dan koneksi Superset.

## 7. Jika ada perubahan source code

Rebuild service yang terkait:

```powershell
cd apache
docker compose up -d --build

cd ..\web
docker compose up -d --build
```
