<?php

namespace Database\Seeders;

use App\Models\Pimpinan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PimpinanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 3) as $number) {
            Pimpinan::updateOrCreate(
                ['email' => "pimpinan.{$number}@temulkpp.com"],
                [
                    'name' => "Pimpinan {$number}",
                    'password' => Hash::make('Pimpinan@12345'),
                ]
            );
        }
    }
}
