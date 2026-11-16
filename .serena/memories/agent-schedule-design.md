# Jadwal agent (agent_schedules) - FINAL, SUDAH JALAN DI DB

Status 2026-10-17: migrate + seed SELESAI di container temulkpp-backend. 616 baris terisi (parity 0: 308, parity 1: 308).

Skema: day (1-4), time HH:MM, agent_id, parity (0=minggu ganjil, 1=minggu genap), unik (day,time,parity,agent_id).
Parity dipakai controllers: parity = (int) isoWeek() % 2 (Carbon ISO week). Terverifikasi: 2026-10-19 (Senin) = minggu 43 = parity 1, 11 slot masing-masing 7 agent, slot 08:00 = agent 1,4,5,9,12,13,16.

Verifikasi SQL: semua (parity,day,time) tepat 7 baris; tidak ada agent pagi+siang di hari sama; beban parity 0 = 22 (id1-8), 17 (id9-12), 16 (id13-16); parity 1 terbalik. Dua minggu = 38/39 slot per agent.

Slot: pagi 08:00,08:40,09:20,10:00,10:40,11:20 (6) + siang 13:00,13:40,14:20,15:00,15:40 (5). Hari 1-4 Senin-Kamis. Pool 7 pagi + 7 siang per hari, 2 agent tidak berjadwal tiap hari (libur berputar tiap minggu).

File: web/database/migrations/2026_10_17_000001_create_agent_schedules_table.php, web/database/seeders/AgentScheduleSeeder.php, web/app/Models/AgentSchedule.php, ReservasiController::scheduledAgentIdsFor, ReservationCheckInController::resolveFallbackAgentId.

Fallback kalau jumlah agent != 16: seeder memakai greedyPlan (beban hari kerja terendah dulu, tanpa acak), semua parity memakai pola sama.

agents.id sudah direnumber 1..16 berurutan. Backup: C:/Users/23105/AppData/Local/Temp/opencode/backup_agents_reservasi.sql. Dump temulkpp.sql lama masih id lompat (belum diperbaiki).

Belum ditangani: race pada batas 7/slot (belum ada lock transaksi), service_types id 6 'test' masih aktif, layar admin CRUD agent_schedules.