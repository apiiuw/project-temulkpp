<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Superadmin::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@temulkpp.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
    }
}
