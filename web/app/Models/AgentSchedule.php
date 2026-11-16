<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSchedule extends Model
{
    /** @var list<string> */
    public const MORNING_TIMES = ['08:00', '08:40', '09:20', '10:00', '10:40', '11:20'];

    /** @var list<string> */
    public const AFTERNOON_TIMES = ['13:00', '13:40', '14:20', '15:00', '15:40'];

    /** @var array<int, string> */
    public const OPERATIONAL_DAYS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
    ];

    /** @var array<int, string> */
    public const PARITIES = [
        0 => 'Minggu Ganjil',
        1 => 'Minggu Genap',
    ];

    public $timestamps = true;

    protected $fillable = [
        'day',
        'time',
        'agent_id',
        'parity',
    ];

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'agent_id' => 'integer',
            'parity' => 'integer',
        ];
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
