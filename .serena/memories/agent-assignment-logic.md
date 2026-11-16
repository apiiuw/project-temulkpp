# Agent assignment logic (updated 2026-10-17)

Penetapan agent ada di 2 file:
- web/app/Http/Controllers/Users/ReservasiController.php -> resolveAssignedAgentId + pickFairAgentId (saat buat reservasi)
- web/app/Http/Controllers/FrontDesk/ReservationCheckInController.php -> resolveFallbackAgentId (fallback saat check-in)

Ranking kandidat (tanpa random, deterministik):
1. same_day_reservations_count (reservasi di hari yang sama)
2. same_week_reservations_count (rentang startOfWeek..endOfWeek Carbon)
3. active_reservations_count (status pending/checked_in_front_desk/in_progress)
4. total_reservations_count (seluruh waktu)
5. id terkecil

Kandidat dibuang jika sudah kebagian di slot yang sama (1 slot = maksimal 7 agent berbeda).
crc32 seed sudah DIHAPUS. Method pickFairAgentId sekarang terima 1 argumen (Collection) tanpa Carbon.

Belum ada: batas maksimal reservasi per agent per hari, dan pembagian berdasarkan kategori layanan (4 service_types) karena resolveAssignedAgentId tidak memfilter jenis_layanan.
