<?php

namespace App\Providers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.sidebar-agent', function ($view): void {
            $agent = Auth::guard('agent')->user();

            if (! $agent) {
                $view->with('agentTaskSummary', [
                    'todayTotal' => 0,
                    'needsAction' => 0,
                    'inProgress' => 0,
                ]);

                return;
            }

            $today = Carbon::today()->toDateString();

            $baseQuery = Reservation::query()
                ->where('agent_id', $agent->id)
                ->whereDate('tanggal_jam', $today);

            $view->with('agentTaskSummary', [
                'todayTotal' => (clone $baseQuery)->count(),
                'needsAction' => (clone $baseQuery)->whereIn('status', ['pending', 'checked_in_front_desk'])->count(),
                'inProgress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            ]);
        });
    }
}
