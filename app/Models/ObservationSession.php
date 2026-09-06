<?php

namespace App\Models;

use App\Support\ObservationAnalytics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservationSession extends Model
{
    protected $fillable = [
        'observation_id',
        'sort_order',
        'session_notes',
    ];

    protected static function booted(): void
    {
        static::saved(static fn () => ObservationAnalytics::flushCaches());
        static::deleted(static fn () => ObservationAnalytics::flushCaches());
    }

    /** @var array{quantitative: array<string, float>, qualitative: array<string, float>, session_notes: string}|null */
    private ?array $rubricSessionArray = null;

    public function observation(): BelongsTo
    {
        return $this->belongsTo(Observation::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ObservationSessionScore::class);
    }

    /**
     * @return array{quantitative: array<string, float>, qualitative: array<string, float>, session_notes: string}
     */
    public function toRubricSessionArray(): array
    {
        if ($this->rubricSessionArray !== null) {
            return $this->rubricSessionArray;
        }

        $quant = [];
        $qual = [];
        $scores = $this->relationLoaded('scores') ? $this->scores : $this->scores()->get();
        foreach ($scores as $score) {
            if ($score->bucket === 'quantitative') {
                $quant[$score->metric_name] = (float) $score->rating;
            } elseif ($score->bucket === 'qualitative') {
                $qual[$score->metric_name] = (float) $score->rating;
            }
        }

        $sessionNotes = is_string($this->session_notes) ? $this->session_notes : '';

        return $this->rubricSessionArray = [
            'quantitative' => $quant,
            'qualitative' => $qual,
            'session_notes' => $sessionNotes,
        ];
    }
}
