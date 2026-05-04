<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            // Agent Menus
            ['name' => 'Dashboard Agent', 'route' => 'agent.dashboard', 'category' => 'Agent', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20" /></svg>'],
            ['name' => 'Jadwal Layanan', 'route' => 'agent.jadwal', 'category' => 'Agent', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 4.75v2.5m9-2.5v2.5M4.75 9h14.5m-13.5 10.25h12a1.5 1.5 0 0 0 1.5-1.5V8A1.5 1.5 0 0 0 17.75 6.5h-12A1.5 1.5 0 0 0 4.25 8v9.75a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>'],
            ['name' => 'Detail Layanan', 'route' => 'agent.detail-layanan', 'category' => 'Agent', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.75 4.75h12.5A1.5 1.5 0 0 1 19.75 6.25v11.5a1.5 1.5 0 0 1-1.5 1.5H5.75a1.5 1.5 0 0 1-1.5-1.5V6.25a1.5 1.5 0 0 1 1.5-1.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9h8M8 12h8M8 15h5" /></svg>'],

            // Pimpinan Menus
            ['name' => 'Dashboard Pimpinan', 'route' => 'pimpinan.dashboard', 'category' => 'Pimpinan', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20" /></svg>'],
            ['name' => 'Rekap Layanan Selesai', 'route' => 'pimpinan.layanan-selesai', 'category' => 'Pimpinan', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.75 9 17.25l10.5-10.5" /></svg>'],
            ['name' => 'Monitoring Layanan Berjalan', 'route' => 'pimpinan.layanan-berjalan', 'category' => 'Pimpinan', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" /></svg>'],
            ['name' => 'Performa Agent', 'route' => 'pimpinan.performa-agent', 'category' => 'Pimpinan', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19v-2a4 4 0 0 1 4-4h1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 19v-2a4 4 0 0 0-4-4h-1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" /></svg>'],
            ['name' => 'Laporan Cetak', 'route' => 'pimpinan.laporan', 'category' => 'Pimpinan', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 5.75h10.5a1.5 1.5 0 0 1 1.5 1.5v9.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-9.5a1.5 1.5 0 0 1 1.5-1.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.75 9.25h6.5M8.75 12h6.5M8.75 14.75h4" /></svg>'],

            // Superadmin Menus
            ['name' => 'Dashboard Superadmin', 'route' => 'superadmin.dashboard', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 10.5 12 4l8.25 6.5v8.25A1.25 1.25 0 0 1 19 20H5a1.25 1.25 0 0 1-1.25-1.25V10.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20v-5.25A1.75 1.75 0 0 1 10.75 13h2.5A1.75 1.75 0 0 1 15 14.75V20" /></svg>'],
            ['name' => 'Perizinan Menu', 'route' => 'superadmin.permissions', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>'],
            ['name' => 'Master Data Agent', 'route' => 'superadmin.master-agent', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>'],
            ['name' => 'Master Data Pimpinan', 'route' => 'superadmin.master-pimpinan', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632.02-.219.037-.441.037-.666 0-.01 0-.02.001-.031a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198a9.094 9.094 0 0 1-3.741-.479M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>'],
            ['name' => 'Rekap Layanan Selesai (SA)', 'route' => 'superadmin.layanan-selesai', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 12.75 9 17.25l10.5-10.5" /></svg>'],
            ['name' => 'Monitoring Layanan Berjalan (SA)', 'route' => 'superadmin.layanan-berjalan', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6 6 18" /></svg>'],
            ['name' => 'Pengaturan Reservasi', 'route' => 'superadmin.reservation-settings', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'],
            ['name' => 'Performa Agent (SA)', 'route' => 'superadmin.performa-agent', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19v-2a4 4 0 0 1 4-4h1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 19v-2a4 4 0 0 0-4-4h-1.5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.5 9a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z" /></svg>'],
            ['name' => 'Laporan Cetak (SA)', 'route' => 'superadmin.laporan', 'category' => 'Superadmin', 'icon' => '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 5.75h10.5a1.5 1.5 0 0 1 1.5 1.5v9.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5v-9.5a1.5 1.5 0 0 1 1.5-1.5Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.75 9.25h6.5M8.75 12h6.5M8.75 14.75h4" /></svg>'],
        ];

        foreach ($menus as $menuData) {
            $menu = \App\Models\Menu::create($menuData);
            
            // Assign default permissions
            if ($menuData['category'] === 'Agent') {
                \App\Models\RoleMenu::create(['role' => 'agent', 'menu_id' => $menu->id]);
            } elseif ($menuData['category'] === 'Pimpinan') {
                \App\Models\RoleMenu::create(['role' => 'pimpinan', 'menu_id' => $menu->id]);
            } elseif ($menuData['category'] === 'Superadmin') {
                \App\Models\RoleMenu::create(['role' => 'superadmin', 'menu_id' => $menu->id]);
            }
        }
    }
}
