<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    private const MIN_RATING = 1;

    private const MAX_RATING = 5;

    private const MAX_COMMENT_LENGTH = 1000;

    /**
     * Halaman awal: hanya form untuk memasukkan kode reservasi.
     * Kode yang dikirim langsung diarahkan ke halaman penilaian reservasi
     * tersebut agar alamatnya dapat dibagikan atau dibuka ulang.
     */
    public function index(Request $request)
    {
        $searchedCode = $this->normalizeCode((string) $request->query('kode_reservasi', ''));

        if ($searchedCode !== '') {
            return redirect()->route('penilaian.show', ['kodeReservasi' => $searchedCode]);
        }

        return view('roles.users.penilaian.index', [
            'title' => 'Penilaian Layanan',
        ]);
    }

    /**
     * Halaman penilaian satu reservasi: detail tamu, agent, dan form penilaian.
     */
    public function show(string $kodeReservasi)
    {
        $reservation = $this->findReservation($kodeReservasi);

        if (! $reservation) {
            return redirect()->route('penilaian')
                ->withErrors(['kode_reservasi' => 'Reservasi dengan kode tersebut tidak ditemukan.']);
        }

        return view('roles.users.penilaian.show', [
            'title' => 'Penilaian Layanan',
            'reservation' => $reservation,
            'ratingState' => $this->resolveRatingState($reservation),
            'maxCommentLength' => self::MAX_COMMENT_LENGTH,
        ]);
    }

    public function store(Request $request, string $kodeReservasi)
    {
        $ratingRules = array_fill_keys(
            array_keys(Reservation::RATING_QUESTIONS),
            'required|integer|between:' . self::MIN_RATING . ',' . self::MAX_RATING,
        );

        $validatedData = $request->validate($ratingRules + [
            'rating_comment' => 'nullable|string|max:' . self::MAX_COMMENT_LENGTH,
        ]);

        $reservation = $this->findReservation($kodeReservasi);

        if (! $reservation) {
            return redirect()->route('penilaian')
                ->withErrors(['kode_reservasi' => 'Reservasi dengan kode tersebut tidak ditemukan.']);
        }

        $ratingState = $this->resolveRatingState($reservation);

        if (! $ratingState['can_rate']) {
            return redirect()->route('penilaian.show', ['kodeReservasi' => $reservation->kode_reservasi])
                ->withErrors(['rating' => $ratingState['block_reason']]);
        }

        $ratingPayload = collect(Reservation::RATING_QUESTIONS)
            ->keys()
            ->mapWithKeys(fn ($column) => [$column => (int) $validatedData[$column]])
            ->all();

        $reservation->update($ratingPayload + [
            'rating_comment' => trim((string) ($validatedData['rating_comment'] ?? '')) ?: null,
            'rated_at' => Carbon::now(),
        ]);

        return redirect()->route('penilaian.show', ['kodeReservasi' => $reservation->kode_reservasi])
            ->with('rating_success', 'Terima kasih atas penilaian Anda.');
    }

    private function findReservation(string $kodeReservasi): ?Reservation
    {
        $normalizedCode = $this->normalizeCode($kodeReservasi);

        if ($normalizedCode === '') {
            return null;
        }

        return Reservation::query()
            ->with('agent:id,name')
            ->where('kode_reservasi', $normalizedCode)
            ->first();
    }

    private function normalizeCode(string $kodeReservasi): string
    {
        return strtoupper(trim($kodeReservasi));
    }

    /**
     * Menentukan apakah reservasi masih boleh dinilai tamu.
     *
     * Aturan: hanya status completed, belum pernah dinilai, dan masih pada
     * hari kalender yang sama dengan selesainya sesi tatap muka.
     *
     * @return array{can_rate: bool, block_reason: string|null}
     */
    private function resolveRatingState(Reservation $reservation): array
    {
        if ($reservation->has_complete_rating) {
            return [
                'can_rate' => false,
                'block_reason' => 'Terima kasih, penilaian untuk reservasi ini sudah Anda berikan sebelumnya.',
            ];
        }

        if ($reservation->status !== 'completed') {
            return [
                'can_rate' => false,
                'block_reason' => 'Penilaian hanya tersedia setelah sesi tatap muka selesai.',
            ];
        }

        $finishedAt = $this->resolveFinishedAt($reservation);

        if ($finishedAt === null) {
            return [
                'can_rate' => false,
                'block_reason' => 'Waktu selesai layanan belum tercatat, sehingga penilaian belum dapat diterima.',
            ];
        }

        if (Carbon::now()->toDateString() !== $finishedAt->toDateString()) {
            return [
                'can_rate' => false,
                'block_reason' => 'Masa penilaian hanya dibuka pada hari yang sama dengan selesainya layanan.',
            ];
        }

        return [
            'can_rate' => true,
            'block_reason' => null,
        ];
    }

    /**
     * Waktu selesai layanan. Data historis hasil import tidak selalu punya
     * waktu selesai tatap muka, maka jatuh tempo ke tanggal_jam reservasi.
     */
    private function resolveFinishedAt(Reservation $reservation): ?Carbon
    {
        $rawValue = $reservation->getRawOriginal('waktu_selesai_tatap_muka')
            ?: $reservation->getRawOriginal('tanggal_jam');

        if ($rawValue instanceof Carbon) {
            $rawValue = $rawValue->format('Y-m-d H:i:s');
        }

        if (! is_string($rawValue) || $rawValue === '') {
            return null;
        }

        $normalizedValue = substr(str_replace('T', ' ', $rawValue), 0, 19);

        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $normalizedValue, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }
}
