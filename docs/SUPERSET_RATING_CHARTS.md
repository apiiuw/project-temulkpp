# Chart Penilaian Tamu di Superset

Status: **sudah terpasang** (dibuat lewat Superset API, 23 Okt 2026).
Semuanya chart Superset biasa, jadi bebas diedit di UI Superset.

## Yang sudah ada

| Objek | Id | Keterangan |
|---|---|---|
| Dataset `rating_rata_rata` | 5 | Virtual (SQL), database MySQL, schema `temulkpp` |
| Dataset `rating_skor` | 6 | Virtual (SQL), database MySQL, schema `temulkpp` |
| Chart "Distribusi Rata-rata Penilaian" | 36 | Pie/Donut, dimension `skor_rata_rata`, metric `COUNT(*)` |
| Chart "Sebaran Skala Penilaian (1-5)" | 37 | Pie/Donut, dimension `skor`, metric `COUNT(*)` |
| Row `ROW-RATING` | – | Berisi kedua chart, width 6 + 6, height 30, di akhir grid |

Dashboard: `Dashboard Superadmin Overview` (id 3,
uuid `da1f9b3b-8911-4eb7-a7eb-9df03b41bb1c`).

Backup layout sebelum diubah:
`%TEMP%\opencode\superadmin_position_backup.json` dan
`superadmin_meta_backup.json`.

## SQL yang dipakai

Dataset virtual dipilih karena dataset `reservations` (id 1) masih memakai
metadata lama — kolom rating tidak muncul di daftar kolomnya.

**`rating_rata_rata`** — satu baris per reservasi yang dinilai lengkap:

```sql
SELECT
    id                  AS id,
    kode_reservasi      AS kode_reservasi,
    tahun               AS tahun,
    agent_id            AS agent_id,
    rated_at            AS rated_at,
    CAST(ROUND(
        (rating_kemudahan + rating_efisiensi + rating_sikap
         + rating_kejelasan + rating_kepuasan) / 5
    ) AS SIGNED)        AS skor_rata_rata
FROM reservations
WHERE rating_kemudahan  IS NOT NULL
  AND rating_efisiensi  IS NOT NULL
  AND rating_sikap      IS NOT NULL
  AND rating_kejelasan  IS NOT NULL
  AND rating_kepuasan   IS NOT NULL
```

`CAST ... AS SIGNED` dipakai agar label potongan chart tampil sebagai
integer bersih (`1`–`5`), bukan desimal.

**`rating_skor`** — *unpivot* 5 kolom rating jadi 5 baris per reservasi:

```sql
SELECT tahun, kode_reservasi, 'Kemudahan Verifikasi'  AS pertanyaan, rating_kemudahan AS skor
FROM reservations WHERE rating_kemudahan IS NOT NULL
UNION ALL
SELECT tahun, kode_reservasi, 'Efisiensi Waktu'       AS pertanyaan, rating_efisiensi AS skor
FROM reservations WHERE rating_efisiensi IS NOT NULL
UNION ALL
SELECT tahun, kode_reservasi, 'Sikap Pelayanan'       AS pertanyaan, rating_sikap     AS skor
FROM reservations WHERE rating_sikap     IS NOT NULL
UNION ALL
SELECT tahun, kode_reservasi, 'Kejelasan dan Solusi'  AS pertanyaan, rating_kejelasan AS skor
FROM reservations WHERE rating_kejelasan IS NOT NULL
UNION ALL
SELECT tahun, kode_reservasi, 'Kepuasan Keseluruhan'  AS pertanyaan, rating_kepuasan  AS skor
FROM reservations WHERE rating_kepuasan  IS NOT NULL
```

Kolom `pertanyaan` disediakan untuk chart tambahan (mis. bar rata-rata per
pertanyaan). Dua chart utama memakai `skor`.

## Filter Tahun

Native filter **Tahun** diarahkan ke `dataset 1: tahun`. Karena native filter
Superset menyaring semua chart di dashboard yang memiliki kolom bernama sama,
dan **kedua dataset virtual di atas memuat `tahun`**, chart baru ikut
tersaring otomatis tanpa konfigurasi tambahan.

## Filter Triwulan

Native filter **Triwulan** (`Triwulan I`–`Triwulan IV`) dibuat oleh
`apache/add_triwulan_filter.py` dengan pola yang sama seperti filter **Tahun**:
`filter_select`, `controlValues` identik, tanpa default, dan target dataset
mengikuti target Tahun di dashboard tsb (dashboard 2 & 3 → dataset 1;
dashboard 4 → dataset 1 + 4; dashboard 5 → dataset 5).

Kolomnya `triwulan` = `CASE QUARTER(tanggal_jam)` dihitung di SQL dataset
(calculated column di dataset 1, ditambahkan ke SQL dataset 4 & 5):

| Label | Rentang |
|---|---|
| Triwulan I | Januari – Maret |
| Triwulan II | April – Juni |
| Triwulan III | Juli – September |
| Triwulan IV | Oktober – Desember |

Filter **Tahun** dan **Triwulan** digabung Superset dengan AND, jadi
**Tahun 2024 + Triwulan I = Januari–Maret 2024**. Tanpa pilihan tahun,
Triwulan I berarti Januari–Maret di seluruh tahun. Baris dengan
`tanggal_jam IS NULL` tidak ikut ketika salah satu triwulan dipilih.

## Mengedit

Buka Superset (`http://localhost:8088`, `admin`/`admin`) → **Charts** → pilih
chart → ubah di Explore → Save. Untuk mengubah SQL dataset: **Datasets** →
pilih dataset → **Edit** → ubah SQL → **Save**. Karena semuanya chart Superset
asli, tidak ada perubahan kode Laravel yang diperlukan — halaman memakai
embedded dashboard + guest token, jadi perubahan langsung tampil di
`localhost:8000`.

## Catatan

- `reservations.tahun` terisi `2024`, `2025`, `2026`.
- Hanya baris dengan **kelima** kolom rating terisi yang ikut dihitung, sama
  dengan aturan form penilaian (kelima jawaban wajib).
- Embedding dashboard tetap aktif: `allowed_domains = ["http://localhost:8000"]`.
