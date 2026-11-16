# Project overview: TemuLKPP

Aplikasi reservasi layanan tatap muka LKPP. Dua stack terpisah dalam satu repo.

## Stack
- web/  : Laravel 13 + PHP 8.3, Blade + Vite + Tailwind/Flowbite, MySQL (host), Docker (backend :8000, frontend vite :5173)
- apache/ : Apache Superset 6.1.0 di Docker :8088, metadata SQLite di apache/superset-data/superset.db
- temulkpp.sql : dump MySQL (schema + 3000+ baris data historis lkpp_gsheet)
- olah_data/, data/ : sumber Excel/CSV

## Auth: 3 guard terpisah (config/auth.php): agent, pimpinan, superadmin. Model masing-masing: Agent, Pimpinan, Superadmin. Route guard pakai auth:agent / auth:pimpinan / auth:superadmin.
- Front desk TANPA login: /front-desk murni by kode_reservasi.
- Public tools tanpa login: /reservasi, /atur-ulang-jadwal, /agent/konfirmasi-tiket.

## Authorization menu: middleware menu:<role> = EnsureMenuPermission, cek tabel menus + role_menu (route name prefix match). Superadmin CRUD di /superadmin/perizinan.

## Status lifecycle reservations
pending -> checked_in_front_desk -> in_progress -> completed
pending -> expired_front_desk (lewat grace 15 menit)
dialihkan_ke_l2
Front desk: grace 15 menit, wajib hari sama, self-healing expired->pending. Agen auto-ditugaskan saat reservasi (fairness: min reservasi hari sama -> min active -> seed crc32 deterministik).

## Slot & aturan reservasi (tabel reservation_settings, key/value)
available_days [1,2,3,4] (Senin-Kamis), max_reservations_per_slot 7, durasi 40 menit, 08:00-11:20 & 13:00-15:40. service_types tabel terpisah (is_active).

## Import historis LKPP (LkppReservationImportService)
CSV 2 baris per reservasi (mulai + selesai). Validasi -> tabel staging reservation_import_batches/rows -> import dengan lockForUpdate. Normalisasi nama agent lowercase. import_hash sha256 untuk dedup. source='lkpp_gsheet'.

## Superset embed
SupersetController::getGuestToken: login admin -> guest_token, RLS clause agent_id=<id> HANYA untuk role agent. Dashboard id per role dari config/superset.php (env SUPERSET_*_DASHBOARD_ID). View dashboard Blade pakai @superset-ui/embed-sdk 0.2.0.
superset_config.py: EMBEDDED_SUPERSET, GUEST_ROLE_NAME=Admin, PUBLIC_ROLE_LIKE=Admin, WTF_CSRF_ENABLED False, PREVENT_UNSAFE_DB_CONNECTIONS False (setup dev/lokal longgar).
Boot container: superset db upgrade -> sync_mysql_connection.py (update URI dari MYSQL_URI) -> superset init -> add_pimpinan_charts.py (idempotent bikin dataset SQL Lab reservation_agent_analytics + charts pimpinan) -> add_triwulan_filter.py (idempotent: kolom triwulan QUARTER(tanggal_jam) di ds1/4/5 + native filter "Triwulan" di dashboard 2/3/4/5, pola sama dengan native filter "Tahun"; keduanya AND -> Tahun 2024 + Triwulan I = Jan-Mar 2024).

## Titik rawan yang terdeteksi
- Cek kapasitas slot di ReservasiController::store tidak pakai lock -> race bisa lebih dari max per slot.
- MeetingController::show punya side effect update status (GET).
- AturUlangJadwalController::update create row baru + hard delete row lama (kode berubah, agent_id hilang).
- Data historis: tanggal_jam vs waktu_mulai_tatap_muka beda 7 jam (Zona WIB vs UTC) -> hati-hati filter tanggal.
- Tidak ada SoftDeletes di Reservation.
