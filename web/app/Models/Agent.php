<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class Agent extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Daftar agent untuk dropdown filter (laporan, monitoring, performa).
     *
     * Diurutkan secara natural: Agent 1, Agent 2, ... Agent 9, Agent 10, ...
     * Bukan urut alfabet biasa yang menghasilkan Agent 1, Agent 10, Agent 11,
     * ... Agent 2, ... (yang membuat angka agent tidak berurutan).
     *
     * @return Collection<int, Agent>
     */
    public static function optionsSortedByName(): Collection
    {
        return static::query()
            ->get(['id', 'name'])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
