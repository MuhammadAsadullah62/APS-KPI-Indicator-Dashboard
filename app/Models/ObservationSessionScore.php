<?php

namespace App\Models;

use App\Support\ObservationAnalytics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservationSessionScore extends Model
{
    protected $fillable = [
        'observation_session_id',
        'bucket',
        'metric_name',
        'rating',
    ];

    protected static function booted(): void
    {
        static::saved(static fn () => ObservationAnalytics::flushCaches());
        static::deleted(static fn () => ObservationAnalytics::flushCaches());
    }

    protected function casts(): array
    {
        return [
            'rating' => 'float',
        ];
    }

    public function observationSession(): BelongsTo
    {
        return $this->belongsTo(ObservationSession::class);
    }
}
