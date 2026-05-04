<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 7) as $number) {
            Agent::updateOrCreate(
                ['email' => "agent.{$number}@temulkpp.com"],
                [
                    'name' => "Agent {$number}",
                    'password' => Hash::make('Agent@12345'),
                ]
            );
        }
    }
}
