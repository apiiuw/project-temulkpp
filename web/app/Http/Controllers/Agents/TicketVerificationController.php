<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class TicketVerificationController extends Controller
{
    public function index(Request $request)
    {
        $nomorTiket = strtoupper(trim((string) $request->query('nomor_tiket', '')));
        $reservation = null;

        if ($nomorTiket !== '') {
            $reservation = Reservation::query()
                ->with('agent')
                ->where('kode_reservasi', $nomorTiket)
                ->first();
        }

        return view('roles.agents.ticket-verification.index', [
            'title' => 'Konfirmasi Nomor Tiket',
            'nomorTiket' => $nomorTiket,
            'reservation' => $reservation,
        ]);
    }
}
