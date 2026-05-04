<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceDetailController extends Controller
{
    public function index(Request $request)
    {
        $agentId = Auth::guard('agent')->id();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'status' => trim((string) $request->query('status', '')),
            'rate' => trim((string) $request->query('rate', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];

        $query = Reservation::query()
            ->where('agent_id', $agentId);

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(function ($builder) use ($search): void {
                $builder->where('kode_reservasi', 'like', '%' . $search . '%')
                    ->orWhere('nama_lengkap', 'like', '%' . $search . '%')
                    ->orWhere('asal_pt', 'like', '%' . $search . '%')
                    ->orWhere('jabatan', 'like', '%' . $search . '%')
                    ->orWhere('jenis_layanan', 'like', '%' . $search . '%');
            });
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('tanggal_jam', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('tanggal_jam', '<=', $filters['date_to']);
        }

        $reservations = $query
            ->orderByDesc('tanggal_jam')
            ->paginate(15)
            ->withQueryString();

        $reservationDetails = $reservations->getCollection()->map(function (Reservation $reservation): array {
            $notePayload = json_decode((string) $reservation->catatan_tatap_muka, true);
            $noteText = null;
            $noteFilePath = null;

            if (is_array($notePayload)) {
                $noteText = $notePayload['teks'] ?? null;
                $noteFilePath = $notePayload['file'] ?? null;
            } elseif (is_string($reservation->catatan_tatap_muka) && $reservation->catatan_tatap_muka !== '') {
                $noteText = $reservation->catatan_tatap_muka;
            }

            $startTime = $reservation->waktu_mulai_tatap_muka;
            $endTime = $reservation->waktu_selesai_tatap_muka;
            $durationLabel = '-';

            if ($startTime && $endTime) {
                $durationSeconds = max(0, $startTime->diffInSeconds($endTime, false));
                $durationLabel = gmdate('H:i:s', $durationSeconds);
            } elseif ($startTime) {
                $durationSeconds = max(0, $startTime->diffInSeconds(Carbon::now(), false));
                $durationLabel = gmdate('H:i:s', $durationSeconds) . ' (berjalan)';
            }

            $tamuLampiranUrl = $reservation->lampiran ? asset('storage/' . ltrim($reservation->lampiran, '/')) : null;

            return [
                'kode_reservasi' => $reservation->kode_reservasi,
                'nama_lengkap' => $reservation->nama_lengkap,
                'asal_pt' => $reservation->asal_pt,
                'jabatan' => $reservation->jabatan,
                'jenis_layanan' => $reservation->jenis_layanan,
                'detail_keperluan' => $reservation->detail_keperluan,
                'status' => str_replace('_', ' ', $reservation->status),
                'tanggal_jam' => Carbon::parse($reservation->tanggal_jam)->translatedFormat('d F Y, H:i') . ' WIB',
                'waktu_mulai_tatap_muka' => $startTime ? Carbon::parse($startTime)->translatedFormat('d F Y, H:i:s') . ' WIB' : '-',
                'waktu_selesai_tatap_muka' => $endTime ? Carbon::parse($endTime)->translatedFormat('d F Y, H:i:s') . ' WIB' : '-',
                'durasi_tatap_muka' => $durationLabel,
                'catatan_teks' => $noteText ?: '-',
                'catatan_file_url' => $noteFilePath ? asset('storage/' . ltrim($noteFilePath, '/')) : null,
                'catatan_file_name' => $noteFilePath ? basename($noteFilePath) : '-',
                'tamu_lampiran_url' => $tamuLampiranUrl,
                'tamu_lampiran_name' => $reservation->lampiran ? basename($reservation->lampiran) : '-',
            ];
        })->values();

        return view('roles.agents.service-detail.index', [
            'title' => 'Detail Layanan Agent',
            'reservationDetails' => $reservationDetails,
            'reservations' => $reservations,
            'filters' => $filters,
        ]);
    }
}
