<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Reservation;

class ReservationCodeSeeder extends Seeder
{
    public function run()
    {
        $reservations = Reservation::query()
            ->whereNull('kode_reservasi')
            ->orWhere('kode_reservasi', '=', '')
            ->cursor();

        foreach ($reservations as $reservation) {
            $datePart = $reservation->tanggal_jam
                ? Carbon::parse($reservation->tanggal_jam)->format('Ymd')
                : Carbon::now()->format('Ymd');

            do {
                $generatedCode = 'RES-' . $datePart . '-' . strtoupper(Str::random(6));
            } while (Reservation::where('kode_reservasi', $generatedCode)->exists());

            $reservation->kode_reservasi = $generatedCode;
            $reservation->save();
        }
    }
}
