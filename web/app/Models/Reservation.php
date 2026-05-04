<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_jam' => 'datetime',
            'checked_in_at' => 'datetime',
            'waktu_mulai_tatap_muka' => 'datetime',
            'waktu_selesai_tatap_muka' => 'datetime',
        ];
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}
