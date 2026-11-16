<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReservationImportBatch extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer', 'valid_rows' => 'integer', 'invalid_rows' => 'integer',
            'warning_rows' => 'integer', 'imported_rows' => 'integer',
        ];
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ReservationImportRow::class, 'batch_id');
    }
}
