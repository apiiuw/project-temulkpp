<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSchedule extends Model
{
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
