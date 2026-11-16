<?php

declare(strict_types=1);

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\ReservationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationSettingController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::all();
        $settings = ReservationSetting::all()->pluck('value', 'key');
        
        return view('roles.superadmin.reservation-settings.index', [
            'title' => 'Pengaturan Reservasi',
            'serviceTypes' => $serviceTypes,
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'available_days' => ['required', 'array', 'min:1'],
            'available_days.*' => ['integer', 'distinct', 'in:0,1,2,3,4,5,6'],
            'max_reservations_per_slot' => ['required', 'integer', 'min:1', 'max:1000'],
            'consultation_duration_minutes' => ['required', 'integer', 'min:10', 'max:480'],
            'morning_start' => ['required', 'date_format:H:i'],
            'morning_end' => ['required', 'date_format:H:i'],
            'afternoon_start' => ['required', 'date_format:H:i'],
            'afternoon_end' => ['required', 'date_format:H:i'],
        ]);

        $toMinutes = static function (string $time): int {
            [$hours, $minutes] = array_map('intval', explode(':', $time));
            return ($hours * 60) + $minutes;
        };
        $morningStart = $toMinutes($validated['morning_start']);
        $morningEnd = $toMinutes($validated['morning_end']);
        $afternoonStart = $toMinutes($validated['afternoon_start']);
        $afternoonEnd = $toMinutes($validated['afternoon_end']);
        $duration = (int) $validated['consultation_duration_minutes'];
        $scheduleErrors = [];

        if ($morningEnd <= $morningStart) {
            $scheduleErrors[] = 'Jam selesai sesi pagi harus setelah jam mulai.';
        }
        if ($afternoonEnd <= $afternoonStart) {
            $scheduleErrors[] = 'Jam selesai sesi siang harus setelah jam mulai.';
        }
        if ($afternoonStart < $morningEnd) {
            $scheduleErrors[] = 'Sesi pagi dan siang tidak boleh tumpang tindih.';
        }
        if ($duration > ($morningEnd - $morningStart) || $duration > ($afternoonEnd - $afternoonStart)) {
            $scheduleErrors[] = 'Durasi sesi tidak boleh melebihi durasi jam operasional.';
        }
        if ($scheduleErrors !== []) {
            throw ValidationException::withMessages(['schedule' => $scheduleErrors]);
        }

        DB::transaction(function () use ($validated): void {
            foreach ($validated as $key => $value) {
                ReservationSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => is_array($value) ? json_encode(array_values($value), JSON_THROW_ON_ERROR) : $value],
                );
            }
        });

        return back()->with('success', 'Pengaturan reservasi berhasil diperbarui.');
    }

    public function resetSettings()
    {
        $defaults = [
            'available_days' => json_encode([1, 2, 3, 4]),
            'max_reservations_per_slot' => '7',
            'consultation_duration_minutes' => '40',
            'morning_start' => '08:00',
            'morning_end' => '11:20',
            'afternoon_start' => '13:00',
            'afternoon_end' => '15:40',
        ];

        DB::transaction(function () use ($defaults): void {
            foreach ($defaults as $key => $value) {
                ReservationSetting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        return back()->with('success', 'Pengaturan reservasi telah dikembalikan ke pengaturan awal.');
    }

    public function storeServiceType(Request $request)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:service_types,name']]);
        ServiceType::create(['name' => trim($validated['name'])]);
        return back()->with('success', 'Jenis layanan berhasil ditambahkan.');
    }

    public function updateServiceType(Request $request, ServiceType $serviceType)
    {
        $request->validate(['name' => 'required|string|max:255|unique:service_types,name,' . $serviceType->id]);
        $serviceType->update(['name' => $request->name]);
        return back()->with('success', 'Jenis layanan berhasil diperbarui.');
    }

    public function toggleServiceType(ServiceType $serviceType)
    {
        $serviceType->update(['is_active' => ! $serviceType->is_active]);
        return back()->with('success', 'Status jenis layanan berhasil diubah.');
    }

    public function destroyServiceType(ServiceType $serviceType)
    {
        $serviceType->delete();
        return back()->with('success', 'Jenis layanan berhasil dihapus.');
    }
}
