# Panduan Distribusi Project TemuLKPP

Project ini didistribusikan sebagai folder ZIP dan dijalankan menggunakan Docker. Tidak diperlukan GitHub, virtualenv Python, `php artisan serve`, atau `npm run dev` secara manual.

## Isi penting yang harus ikut dikirim

Pastikan file dan folder berikut ada:

```text
apache/superset-data/superset.db
temulkpp.sql
apache/.env.docker
web/.env.docker
apache/docker-compose.yml
web/docker-compose.yml
```

File `apache/superset-data/superset.db` wajib dipertahankan karena berisi metadata Apache Superset:

- Dashboard
- Chart
- Dataset
- User
- Database connection
- Row-level security
- Konfigurasi embedding

## Setup di komputer client

### 1. Install Docker Desktop

Install dan jalankan Docker Desktop untuk Windows.

Pastikan MySQL juga aktif di komputer client.

### 2. Restore MySQL

Buat database:

```sql
CREATE DATABASE temulkpp;
```

Import file berikut ke database tersebut:

```text
temulkpp.sql
```

### 3. Periksa environment

Sesuaikan konfigurasi berikut jika username, password, atau port MySQL berbeda:

```text
apache/.env.docker
web/.env.docker
```

Untuk MySQL yang berjalan di Windows host, gunakan:

```text
host.docker.internal
```

Jangan mengganti `SUPERSET_SECRET_KEY` jika ingin mempertahankan metadata Superset lama.

### 4. Jalankan Superset

Dari root project:

```powershell
cd apache
docker compose up -d --build
```

Superset tersedia di:

```text
http://localhost:8088
```

### 5. Jalankan Laravel

Buka PowerShell baru:

```powershell
cd web
docker compose up -d --build
```

Laravel tersedia di:

```text
http://localhost:8000
```

## Menjaga data tetap aman

Untuk menghentikan aplikasi:

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

Jangan menghapus atau mengganti:

```text
apache/superset-data/superset.db
```

Jika dashboard Superset hilang, biasanya file metadata tersebut tidak ikut dikirim atau `SUPERSET_SECRET_KEY` berubah.

## Dokumentasi lanjutan

Panduan setup lengkap:

```text
SETUP_CLIENT.md
```

Panduan khusus Superset:

```text
apache/DOCKER_SUPERSET.md
```
