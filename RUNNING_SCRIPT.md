# Panduan Menjalankan Aplikasi Temu LKPP

Dokumentasi ini menjelaskan langkah-langkah untuk menjalankan aplikasi Temu LKPP secara lengkap, mulai dari Backend Laravel, Frontend (NPM), hingga Sistem Analitik (Superset).

---

## 📋 Persyaratan Sistem
Pastikan perangkat Anda sudah terinstal:
*   **PHP** (v8.2 atau terbaru)
*   **MySQL** (Database sedang berjalan)
*   **Node.js & NPM**
*   **Python** (Untuk menjalankan Superset)

---

## 🚀 Langkah-langkah Menjalankan Aplikasi

Aplikasi ini membutuhkan **3 Jendela Terminal** yang berjalan secara bersamaan.

### Jendela 1: Backend Laravel (Server Utama)
Jendela ini berfungsi untuk menjalankan inti aplikasi Laravel.
1.  Buka Terminal dan masuk ke folder `web`.
2.  Jalankan perintah berikut:
    ```bash
    php artisan serve
    ```
3.  **Status:** Biarkan terminal ini tetap terbuka. Aplikasi web dapat diakses di: `http://127.0.0.1:8000`

### Jendela 2: Frontend Assets (NPM)
Jendela ini berfungsi untuk memproses tampilan (CSS/Javascript) agar aplikasi terlihat rapi.
1.  Buka Terminal baru dan masuk ke folder `web`.
2.  Jalankan perintah berikut:
    ```bash
    npm run dev
    ```
3.  **Status:** Biarkan terminal ini tetap terbuka selama Anda menggunakan aplikasi.

### Jendela 3: Analitik Superset (Grafik & Statistik)
Jendela ini berfungsi agar grafik dan statistik di dashboard (Agent, Pimpinan, Superadmin) dapat muncul.
1.  Buka Terminal baru dan masuk ke folder `apache`.
2.  Aktifkan lingkungan virtual Python:
    ```bash
    source venv/bin/activate
    ```
3.  Beritahu sistem lokasi file konfigurasi:
    ```bash
    export SUPERSET_CONFIG_PATH=$(pwd)/superset_config.py
    ```
4.  Jalankan server grafik:
    ```bash
    superset run -p 8088 --with-threads --reload --debugger
    ```
5.  **Status:** Biarkan terminal ini tetap terbuka. Grafik tidak akan muncul jika jendela ini tertutup.

---

## 🔗 Daftar Akses Cepat

| Layanan | URL Akses | Keterangan |
| :--- | :--- | :--- |
| **Halaman Reservasi** | `http://127.0.0.1:8000/reservasi` | Halaman utama untuk tamu membuat janji |
| **Halaman Front Desk** | `http://127.0.0.1:8000/front-desk` | Digunakan petugas untuk check-in tamu |
| **Halaman Login** | `http://127.0.0.1:8000/auth/login` | Portal masuk Agent, Pimpinan, dan Superadmin |
| **Panel Analitik** | `http://127.0.0.1:8088` | Dashboard internal grafik (Admin) |

---

## 🔐 Daftar Akun Login

Gunakan akun berikut untuk masuk ke sistem melalui **Halaman Login**:

| Role | Email | Password |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin@temulkpp.com` | `password` |
| **Agent** | `agent.1@temulkpp.com` | `Agent@12345` |
| **Pimpinan** | `pimpinan.1@temulkpp.com` | `Pimpinan@12345` |

> [!TIP]
> Terdapat 7 akun Agent (`agent.1` s/d `agent.7`) dan 3 akun Pimpinan (`pimpinan.1` s/d `pimpinan.3`) yang tersedia di database.


---

## 💡 Catatan Penting
*   **Urutan Jalankan:** Bebas, namun disarankan menjalankan Laravel (`php artisan serve`) terlebih dahulu.
*   **Jika Grafik Error:** Pastikan Terminal Jendela 3 (Superset) sudah berjalan dengan status "Running".
*   **Database:** Pastikan MySQL Anda aktif sebelum menjalankan `php artisan serve`.

---
*Dokumentasi ini dibuat untuk memudahkan operasional harian aplikasi Temu LKPP.*