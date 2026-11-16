<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Agents\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agents\ScheduleController as AgentScheduleController;
use App\Http\Controllers\Agents\ServiceDetailController as AgentServiceDetailController;
use App\Http\Controllers\Agents\TicketVerificationController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\Superadmin\ReservationSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontDesk\ReservationCheckInController;
use App\Http\Controllers\Users\ReservasiController;
use App\Http\Controllers\Users\AturUlangJadwalController;
use App\Http\Controllers\Users\PenilaianController;
use App\Http\Controllers\SupersetController;
use App\Http\Controllers\Superadmin\ReservationImportController;


// Users
Route::redirect('/', '/reservasi');
Route::get('/reservasi', [ReservasiController::class, 'index'])->name('reservasi');
Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
Route::get('/reservasi/{kodeReservasi}/download', [ReservasiController::class, 'download'])->name('reservasi.download');
Route::get('/atur-ulang-jadwal', [AturUlangJadwalController::class, 'index'])->name('atur-ulang-jadwal');
Route::put('/atur-ulang-jadwal/{kodeReservasi}', [AturUlangJadwalController::class, 'update'])->name('atur-ulang-jadwal.update');
Route::delete('/atur-ulang-jadwal/{kodeReservasi}', [AturUlangJadwalController::class, 'destroy'])->name('atur-ulang-jadwal.destroy');

// Penilaian tamu (publik, tanpa login)
Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian');
Route::get('/penilaian/{kodeReservasi}', [PenilaianController::class, 'show'])->name('penilaian.show');
Route::post('/penilaian/{kodeReservasi}', [PenilaianController::class, 'store'])->name('penilaian.store');

// Front Desk
Route::get('/front-desk', [ReservationCheckInController::class, 'index'])->name('front-desk');
Route::patch('/front-desk/{kodeReservasi}/confirm', [ReservationCheckInController::class, 'confirm'])->name('front-desk.confirm');

// Public Agent Tools
Route::get('/agent/konfirmasi-tiket', [TicketVerificationController::class, 'index'])->name('agent.konfirmasi-tiket');

// Auth System
// Halaman login selalu terbuka: satu browser boleh login beberapa aktor
// sekaligus (session tiap guard terpisah), jadi tidak perlu logout dulu.
Route::get('/auth/login', [LoginController::class, 'create'])->name('login');
Route::post('/auth/login', [LoginController::class, 'store'])->name('login.store');

Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('logout');

// Agents
Route::middleware(['auth:agent'])->prefix('agent')->group(function () {
    Route::get('/dashboard', [AgentDashboardController::class, 'index'])->middleware('menu:agent')->name('agent.dashboard');
    Route::get('/jadwal', [AgentScheduleController::class, 'index'])->middleware('menu:agent')->name('agent.jadwal');
    Route::get('/detail-layanan', [AgentServiceDetailController::class, 'index'])->middleware('menu:agent')->name('agent.detail-layanan');
    Route::get('/file/{reservation}/{type}', [AgentServiceDetailController::class, 'downloadFile'])->name('agent.file.download');
    Route::get('/tatap-muka/{kodeReservasi}', [App\Http\Controllers\Agents\MeetingController::class, 'show'])->name('agent.tatap-muka');
    Route::post('/tatap-muka/{kodeReservasi}/start', [App\Http\Controllers\Agents\MeetingController::class, 'start'])->name('agent.tatap-muka.start');
    Route::post('/tatap-muka/{kodeReservasi}/end', [App\Http\Controllers\Agents\MeetingController::class, 'end'])->name('agent.tatap-muka.end');
});

// Shared Superset API
Route::middleware(['auth:agent,pimpinan,superadmin'])->group(function () {
    Route::get('/superset/guest-token', [SupersetController::class, 'getGuestToken'])->name('superset.guest-token');
});

// Pimpinan
Route::middleware(['auth:pimpinan'])->prefix('pimpinan')->group(function () {
    Route::get('/dashboard', [PimpinanDashboardController::class, 'index'])->middleware('menu:pimpinan')->name('pimpinan.dashboard');
    Route::get('/layanan-selesai', [PimpinanDashboardController::class, 'completedServices'])->middleware('menu:pimpinan')->name('pimpinan.layanan-selesai');
    Route::get('/layanan-berjalan', [PimpinanDashboardController::class, 'ongoingServices'])->middleware('menu:pimpinan')->name('pimpinan.layanan-berjalan');
    Route::get('/performa-agent', [PimpinanDashboardController::class, 'agentPerformance'])->middleware('menu:pimpinan')->name('pimpinan.performa-agent');
    Route::get('/laporan', [PimpinanDashboardController::class, 'report'])->middleware('menu:pimpinan')->name('pimpinan.laporan');
    Route::get('/laporan/cetak', [PimpinanDashboardController::class, 'printReport'])->middleware('menu:pimpinan')->name('pimpinan.laporan.cetak');
});

// Superadmin
Route::middleware(['auth:superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/dashboard', [SuperadminController::class, 'dashboard'])->middleware('menu:superadmin')->name('superadmin.dashboard');
    Route::get('/perizinan', [SuperadminController::class, 'permissions'])->middleware('menu:superadmin')->name('superadmin.permissions');
    Route::post('/perizinan', [SuperadminController::class, 'updatePermissions'])
            ->middleware('menu:superadmin')
            ->name('superadmin.permissions.update');
    Route::get('/master-data/agent', [SuperadminController::class, 'masterAgent'])->middleware('menu:superadmin')->name('superadmin.master-agent');
    Route::post('/master-data/agent', [SuperadminController::class, 'storeAgent'])->middleware('menu:superadmin')->name('superadmin.master-agent.store');
    Route::put('/master-data/agent/{agent}', [SuperadminController::class, 'updateAgent'])->middleware('menu:superadmin')->name('superadmin.master-agent.update');
    Route::patch('/master-data/agent/{agent}/toggle-status', [SuperadminController::class, 'toggleAgentStatus'])->middleware('menu:superadmin')->name('superadmin.master-agent.toggle-status');
    Route::get('/master-data/pimpinan', [SuperadminController::class, 'masterPimpinan'])->middleware('menu:superadmin')->name('superadmin.master-pimpinan');
    Route::post('/master-data/pimpinan', [SuperadminController::class, 'storePimpinan'])->middleware('menu:superadmin')->name('superadmin.master-pimpinan.store');
    Route::put('/master-data/pimpinan/{pimpinan}', [SuperadminController::class, 'updatePimpinan'])->middleware('menu:superadmin')->name('superadmin.master-pimpinan.update');
    Route::patch('/master-data/pimpinan/{pimpinan}/toggle-status', [SuperadminController::class, 'togglePimpinanStatus'])->middleware('menu:superadmin')->name('superadmin.master-pimpinan.toggle-status');
    Route::get('/layanan-selesai', [SuperadminController::class, 'completedServices'])->middleware('menu:superadmin')->name('superadmin.layanan-selesai');
    Route::get('/layanan-berjalan', [SuperadminController::class, 'ongoingServices'])->middleware('menu:superadmin')->name('superadmin.layanan-berjalan');
    Route::get('/performa-agent', [SuperadminController::class, 'agentPerformance'])->middleware('menu:superadmin')->name('superadmin.performa-agent');
    Route::get('/laporan', [SuperadminController::class, 'report'])->middleware('menu:superadmin')->name('superadmin.laporan');
    Route::get('/laporan/cetak', [SuperadminController::class, 'printReport'])->middleware('menu:superadmin')->name('superadmin.laporan.cetak');

    // Reservation Settings
    Route::get('/pengaturan-reservasi', [ReservationSettingController::class, 'index'])->middleware('menu:superadmin')->name('superadmin.reservation-settings');
    Route::get('/import-data-reservasi', [ReservationImportController::class, 'index'])->middleware('menu:superadmin')->name('superadmin.reservation-import.index');
    Route::post('/import-data-reservasi/validate', [ReservationImportController::class, 'validateFile'])->middleware('menu:superadmin')->name('superadmin.reservation-import.validate');
    Route::get('/import-data-reservasi/{batch}', [ReservationImportController::class, 'show'])->middleware('menu:superadmin')->name('superadmin.reservation-import.show');
    Route::post('/import-data-reservasi/{batch}/import', [ReservationImportController::class, 'import'])->middleware('menu:superadmin')->name('superadmin.reservation-import.import');
    Route::post('/pengaturan-reservasi', [ReservationSettingController::class, 'updateSettings'])->middleware('menu:superadmin')->name('superadmin.reservation-settings.update');
    Route::post('/pengaturan-reservasi/reset', [ReservationSettingController::class, 'resetSettings'])->middleware('menu:superadmin')->name('superadmin.reservation-settings.reset');
    Route::post('/service-types', [ReservationSettingController::class, 'storeServiceType'])->middleware('menu:superadmin')->name('superadmin.service-types.store');
    Route::patch('/service-types/{serviceType}/toggle', [ReservationSettingController::class, 'toggleServiceType'])->middleware('menu:superadmin')->name('superadmin.service-types.toggle');
    Route::delete('/service-types/{serviceType}', [ReservationSettingController::class, 'destroyServiceType'])->middleware('menu:superadmin')->name('superadmin.service-types.destroy');
});
