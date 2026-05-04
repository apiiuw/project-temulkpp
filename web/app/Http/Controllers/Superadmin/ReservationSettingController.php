<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\ReservationSetting;
use Illuminate\Http\Request;

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
            'available_days' => 'required|array',
            'max_reservations_per_slot' => 'required|integer|min:1',
            'consultation_duration_minutes' => 'required|integer|min:10',
            'morning_start' => 'required|date_format:H:i',
            'morning_end' => 'required|date_format:H:i',
            'afternoon_start' => 'required|date_format:H:i',
            'afternoon_end' => 'required|date_format:H:i',
        ]);

        foreach ($validated as $key => $value) {
            ReservationSetting::where('key', $key)->update([
                'value' => is_array($value) ? json_encode($value) : $value
            ]);
        }

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

        foreach ($defaults as $key => $value) {
            ReservationSetting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Pengaturan reservasi telah dikembalikan ke pengaturan awal.');
    }

    public function storeServiceType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:service_types,name']);
        ServiceType::create(['name' => $request->name]);
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
