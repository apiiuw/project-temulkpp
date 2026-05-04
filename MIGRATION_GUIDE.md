# Panduan Migrasi Project: Mac ke Windows 🚀

Dokumentasi ini menjelaskan cara memindahkan project **Temu LKPP** (Laravel + Apache Superset) dari macOS ke sistem operasi Windows.

---

## 🛠 Opsi 1: Menggunakan Flashdisk / Manual
(Gunakan cara ini jika Anda ingin cara yang paling sederhana tanpa perlu paham Git).
1. Copy seluruh folder project (setelah menghapus folder dependencies).
2. Pindahkan ke laptop baru.

---

## 🛠 Opsi 2: Menggunakan GitHub (Direkomendasikan)
Jika Anda ingin menggunakan GitHub untuk memindahkan project, ikuti langkah ini:

### 1. Di Laptop Mac (Push)
1.  Buka terminal di root project `temu-lkpp`.
2.  Inisialisasi Git dan tambahkan remote:
    ```bash
    git init
    git add .
    git commit -m "Initial migration commit"
    git branch -M main
    git remote add origin [URL_REPO_GITHUB_ANDA]
    git push -u origin main
    ```
    *(Pastikan file `.gitignore` sudah ada di root agar folder besar tidak ikut ter-upload).*

### 2. Di Laptop Windows (Pull)
1.  Buka terminal di folder tempat Anda ingin menyimpan project (misal: `C:\laragon\www`).
2.  Clone repository:
    ```bash
    git clone [URL_REPO_GITHUB_ANDA]
    ```
3.  **Penting**: GitHub tidak meng-upload file `.env`. Anda harus meng-copy file `web/.env` secara manual dari Mac ke Windows.

---

Sebelum memindahkan folder, lakukan pembersihan agar ukuran file lebih kecil dan menghindari konflik sistem:

1.  **Export Database**:
    Pastikan file `temulkpp.sql` di root folder adalah versi terbaru.
2.  **Hapus Folder Dependencies**:
    Hapus folder berikut karena harus di-install ulang di Windows:
    *   `web/node_modules`
    *   `web/vendor`
    *   `apache/venv`
3.  **Ambil Metadata Superset**:
    Buka Terminal Mac dan jalankan:
    ```bash
    open ~/.superset
    ```
    Copy file `superset.db` (ini berisi semua dashboard/grafik yang sudah Anda buat).

---

## 2. Persiapan di Laptop Windows (Tujuan)

Instal perangkat lunak berikut di Windows:

1.  **Laragon** (Sangat disarankan untuk PHP & MySQL di Windows).
2.  **Node.js** (LTS version).
3.  **Python 3.9 atau terbaru**.
4.  **Visual Studio Build Tools**: 
    *   [Download di sini](https://visualstudio.microsoft.com/visual-cpp-build-tools/)
    *   Saat instalasi, pilih centang **"Desktop development with C++"**. Ini wajib agar instalasi Superset di Windows tidak error.

---

## 3. Langkah Instalasi di Windows

### A. Setup Folder & Database
1.  Letakkan folder project di `C:\laragon\www\temu-lkpp`.
2.  Buka Laragon, klik **Start All**.
3.  Buka Database (Klik tombol **Database** di Laragon), buat database bernama `temu_lkpp`.
4.  Import file `temulkpp.sql` ke database tersebut.

### B. Setup Laravel (Folder `web`)
1.  Buka Terminal (PowerShell atau CMD) di folder `web`.
2.  Jalankan:
    ```powershell
    composer install
    npm install
    php artisan key:generate
    php artisan storage:link
    ```
3.  Sesuaikan file `.env` (DB_USERNAME dan DB_PASSWORD biasanya `root` dan kosong di Laragon).

### C. Setup Superset (Folder `apache`)
1.  Buka folder Home user Anda di Windows (`C:\Users\[NamaUser]`).
2.  Buat folder baru bernama `.superset` (jika belum ada).
3.  Paste file `superset.db` yang tadi di-copy dari Mac ke dalam folder tersebut.
4.  Buka Terminal di folder `apache`:
    ```powershell
    python -m venv venv
    .\venv\Scripts\activate
    pip install apache-superset pymysql
    ```
5.  Inisialisasi Superset:
    ```powershell
    $env:SUPERSET_CONFIG_PATH = "$(Get-Location)\superset_config.py"
    superset db upgrade
    superset init
    ```

---

## 4. Cara Menjalankan di Windows

Gunakan 3 jendela Terminal terpisah:

### Terminal 1: Backend
```bash
cd web
php artisan serve
```

### Terminal 2: Frontend
```bash
cd web
npm run dev
```

### Terminal 3: Superset
```powershell
cd apache
.\venv\Scripts\activate
$env:SUPERSET_CONFIG_PATH = "$(Get-Location)\superset_config.py"
superset run -p 8088 --with-threads --reload --debugger
```

---

## 💡 Troubleshooting di Windows

*   **Error "Microsoft Visual C++ 14.0 or greater is required"**: Anda belum menginstal *Visual Studio Build Tools* (lihat bagian Persiapan).
*   **Error "Command not found"**: Pastikan path Python dan PHP sudah terdaftar di Environment Variables Windows.
*   **Grafik Tidak Muncul**: Pastikan port `8088` (Superset) dan `8000` (Laravel) tidak diblokir oleh Windows Firewall.
