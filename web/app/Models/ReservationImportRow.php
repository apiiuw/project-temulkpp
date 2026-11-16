<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationImportRow extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['raw_data' => 'array', 'normalized_data' => 'array', 'errors' => 'array', 'warnings' => 'array'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ReservationImportBatch::class, 'batch_id');
    }
}
