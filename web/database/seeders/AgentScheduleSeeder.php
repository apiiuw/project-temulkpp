<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentSchedule;
use Illuminate\Database\Seeder;

class AgentScheduleSeeder extends Seeder
{
    /** Slot pagi: 08:00 s/d 11:20 (6 slot). */
    private const MORNING_TIMES = ['08:00', '08:40', '09:20', '10:00', '10:40', '11:20'];

    /** Slot siang: 13:00 s/d 15:40 (5 slot). */
    private const AFTERNOON_TIMES = ['13:00', '13:40', '14:20', '15:00', '15:40'];

    /** Hari operasional: 1=Senin s/d 4=Kamis. */
    private const DAYS = [1, 2, 3, 4];

    /** Daya tampung per pilihan waktu. */
    private const PER_SLOT = 7;

    /** Pola minggu ganjil (parity 0) dan genap (parity 1). */
    private const PARITIES = [0, 1];

    /**
     * Jadwal tetap dua pola bergantian per minggu (ganjil/genap).
     *
     * Tiap hari tepat 7 agent pagi dan 7 agent siang. Karena 16 agent tetapi
     * hanya 14 kursi jadwal per hari, liburnya dirotasi dua arah:
     *
     * - Minggu ganjil: agent 1-8 kerja penuh 2 pagi + 2 siang (22 slot),
     *   agent 9-12 2 pagi + 1 siang (17 slot), agent 13-16 1 pagi + 2 siang (16 slot).
     * - Minggu genap: peran terbalik, agent 9-16 kerja penuh (22 slot),
     *   agent 1-4 2 pagi + 1 siang (17 slot), agent 5-8 1 pagi + 2 siang (16 slot).
     *
     * Dua minggu berjalan tiap agent menghasilkan 38 atau 39 slot, jadi libur
     * berputar tanpa mengubah total beban. Tidak ada agent yang selalu pagi
     * atau selalu siang, dan tidak pernah pagi + siang di hari yang sama.
     */
    public function run(): void
    {
        $agentIds = Agent::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (count($agentIds) !== 16) {
            $this->command?->warn(sprintf(
                'Agent aktif %d (diharapkan 16), memakai pola greedy untuk semua minggu.',
                count($agentIds),
            ));
        }

        $rows = [];
        foreach (self::PARITIES as $parity) {
            $plan = count($agentIds) === 16
                ? $this->patternPlan($agentIds, $parity)
                : $this->greedyPlan($agentIds);

            foreach ($plan as $day => $pools) {
                foreach (self::MORNING_TIMES as $time) {
                    foreach ($pools['pagi'] as $agentId) {
                        $rows[] = $this->row($day, $time, $agentId, $parity);
                    }
                }
                foreach (self::AFTERNOON_TIMES as $time) {
                    foreach ($pools['siang'] as $agentId) {
                        $rows[] = $this->row($day, $time, $agentId, $parity);
                    }
                }
            }
        }

        AgentSchedule::query()->delete();
        AgentSchedule::query()->insert($rows);

        $this->command?->info(sprintf(
            'Jadwal dibuat: %d baris (%d pola minggu x %d hari x 11 slot x 7 agent).',
            count($rows),
            count(self::PARITIES),
            count(self::DAYS),
        ));

        foreach (self::PARITIES as $parity) {
            $load = [];
            foreach ($rows as $row) {
                if ($row['parity'] === $parity) {
                    $load[$row['agent_id']] = ($load[$row['agent_id']] ?? 0) + 1;
                }
            }

            $this->command?->info(sprintf(
                'Minggu %s: beban min %d, max %d slot.',
                $parity === 0 ? 'ganjil' : 'genap',
                min($load),
                max($load),
            ));
        }
    }

    /**
     * Pola pasti untuk 16 agent.
     *
     * Minggu ganjil: kelompok penuh = urutan 0-7, kelebihan pagi = 8-11,
     * kelebihan siang = 12-15. Minggu genap perannya ditukar: kelompok penuh
     * = 8-15, kelebihan pagi = 0-3, kelebihan siang = 4-7.
     *
     * @return array<int, array{pagi: list<int>, siang: list<int>}>
     */
    private function patternPlan(array $agentIds, int $parity): array
    {
        if ($parity === 0) {
            $full = array_slice($agentIds, 0, 8);       // 2 pagi + 2 siang, tanpa libur
            $moreMorning = array_slice($agentIds, 8, 4);   // 2 pagi + 1 siang
            $moreAfternoon = array_slice($agentIds, 12, 4);  // 1 pagi + 2 siang
        } else {
            $full = array_slice($agentIds, 8, 8);
            $moreMorning = array_slice($agentIds, 0, 4);
            $moreAfternoon = array_slice($agentIds, 4, 4);
        }

        $plan = [];
        foreach (self::DAYS as $index => $day) {
            $morningPool = [];
            $afternoonPool = [];

            // Penuh: pagi pada hari ke-(i % 4) dan ke-((i + 1) % 4).
            foreach ($full as $position => $id) {
                if ($index === $position % 4 || $index === ($position + 1) % 4) {
                    $morningPool[] = $id;
                } else {
                    $afternoonPool[] = $id;
                }
            }

            // Kelebihan pagi: pagi hari (j % 4) dan ((j + 1) % 4),
            // siang hari ((j + 2) % 4), libur hari ((j + 3) % 4).
            foreach ($moreMorning as $position => $id) {
                if ($index === $position % 4 || $index === ($position + 1) % 4) {
                    $morningPool[] = $id;
                } elseif ($index === ($position + 2) % 4) {
                    $afternoonPool[] = $id;
                }
            }

            // Kelebihan siang: pagi hari (k % 4),
            // siang hari ((k + 1) % 4) dan ((k + 2) % 4), libur hari ((k + 3) % 4).
            foreach ($moreAfternoon as $position => $id) {
                if ($index === $position % 4) {
                    $morningPool[] = $id;
                } elseif ($index === ($position + 1) % 4 || $index === ($position + 2) % 4) {
                    $afternoonPool[] = $id;
                }
            }

            sort($morningPool);
            sort($afternoonPool);

            if (count($morningPool) !== self::PER_SLOT || count($afternoonPool) !== self::PER_SLOT) {
                $this->command?->warn(sprintf(
                    'Minggu %s hari %d: pagi %d agent, siang %d agent (diharapkan %d).',
                    $parity === 0 ? 'ganjil' : 'genap',
                    $day,
                    count($morningPool),
                    count($afternoonPool),
                    self::PER_SLOT,
                ));
            }

            $plan[$day] = ['pagi' => $morningPool, 'siang' => $afternoonPool];
        }

        return $plan;
    }

    /**
     * Fallback bila jumlah agent bukan 16: pilih pool pagi lalu siang dari
     * agent dengan hari kerja paling sedikit, seri dipecah beban slot paling
     * ringan lalu id terkecil, tanpa acak.
     *
     * @return array<int, array{pagi: list<int>, siang: list<int>}>
     */
    private function greedyPlan(array $agentIds): array
    {
        $morningDays = array_fill_keys($agentIds, 0);
        $afternoonDays = array_fill_keys($agentIds, 0);
        $plan = [];

        foreach (self::DAYS as $day) {
            $sortKey = fn (int $id): string => sprintf(
                '%02d%02d%02d%02d',
                $morningDays[$id] + $afternoonDays[$id],
                $morningDays[$id],
                6 * $morningDays[$id] + 5 * $afternoonDays[$id],
                $id,
            );

            $morningPool = $this->takeLowest($agentIds, $sortKey, self::PER_SLOT);
            foreach ($morningPool as $id) {
                $morningDays[$id]++;
            }

            $remaining = array_values(array_diff($agentIds, $morningPool));
            $afternoonPool = $this->takeLowest($remaining, $sortKey, self::PER_SLOT);
            foreach ($afternoonPool as $id) {
                $afternoonDays[$id]++;
            }

            $plan[$day] = ['pagi' => $morningPool, 'siang' => $afternoonPool];
        }

        return $plan;
    }

    private function takeLowest(array $pool, callable $key, int $count): array
    {
        usort($pool, fn (int $a, int $b): int => $key($a) <=> $key($b));

        return array_slice($pool, 0, $count);
    }

    private function row(int $day, string $time, int $agentId, int $parity): array
    {
        return [
            'day' => $day,
            'time' => $time,
            'agent_id' => $agentId,
            'parity' => $parity,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
