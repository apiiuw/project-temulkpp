<?php

declare(strict_types=1);

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ReservationImportBatch;
use App\Services\LkppReservationImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

final class ReservationImportController extends Controller
{
    public function __construct(private readonly LkppReservationImportService $imports) {}

    public function index(): View
    {
        return view('roles.superadmin.reservation-import.index', ['title' => 'Import Data Reservasi', 'batches' => ReservationImportBatch::query()->latest()->paginate(10)]);
    }

    public function validateFile(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);
        try {
            $batch = $this->imports->validate($request->file('file'), (int) $request->user('superadmin')->id);
        } catch (Throwable $exception) {
            Log::error('Reservation import validation failed', [
                'exception' => $exception,
                'filename' => $request->file('file')?->getClientOriginalName(),
            ]);

            return back()->withErrors(['file' => 'File gagal divalidasi. Periksa format CSV dan data di dalamnya.'])->withInput();
        }
        return redirect()->route('superadmin.reservation-import.show', $batch)->with('success', 'File berhasil divalidasi.');
    }

    public function show(ReservationImportBatch $batch): View
    {
        $batch->load(['rows' => fn ($query) => $query->orderBy('row_number')]);
        return view('roles.superadmin.reservation-import.show', ['title' => 'Preview Import Data Reservasi', 'batch' => $batch]);
    }

    public function import(ReservationImportBatch $batch): RedirectResponse
    {
        try {
            $count = $this->imports->import($batch);
        } catch (Throwable $exception) {
            Log::error('Reservation import failed', [
                'exception' => $exception,
                'batch_id' => $batch->id,
            ]);

            return back()->withErrors(['import' => 'Import gagal diproses. Periksa log aplikasi untuk detail.']);
        }
        return redirect()->route('superadmin.reservation-import.show', $batch)->with('success', "{$count} baris berhasil diimport.");
    }
}
