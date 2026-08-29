<?php

namespace App\Models;

use App\Enums\Department;
use App\Enums\Wing;
use App\Support\ObservationAnalytics;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Observation extends Model
{
    protected $fillable = [
        'observer_id',
        'observee_id',
        'aggregate_percent',
        'notes',
        'observation_wing',
        'observation_department',
    ];

    protected static function booted(): void
    {
        $flush = static fn () => ObservationAnalytics::flushCaches();

        static::saved($flush);
        static::deleted($flush);
    }

    protected function casts(): array
    {
        return [
            'observation_wing' => Wing::class,
            'observation_department' => Department::class,
        ];
    }

    public function observer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observer_id');
    }

    public function observee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'observee_id');
    }

    public function observationSessions(): HasMany
    {
        return $this->hasMany(ObservationSession::class)->orderBy('sort_order');
    }

    /** Virtual attribute for API and Blade — same shape as the former JSON column. */
    protected function sessionsPayload(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sessionsPayloadArray(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function sessionsPayloadArray(): array
    {
        $sessions = $this->relationLoaded('observationSessions')
            ? $this->observationSessions
            : $this->observationSessions()->get();

        return $sessions
            ->sortBy('sort_order')
            ->values()
            ->map(fn (ObservationSession $s) => $s->toRubricSessionArray())
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $sessions
     */
    private static function sessionNotesFromSessionPayload(array $session): ?string
    {
        foreach (['session_notes', 'Session_notes'] as $k) {
            if (! isset($session[$k]) || ! is_string($session[$k])) {
                continue;
            }
            $t = trim($session[$k]);

            return $t === '' ? null : $t;
        }

        return null;
    }

    /**
     * Normalised {bucket, metric_name, rating} rows for one rubric block.
     *
     * @param  array<string, mixed>  $session
     * @param  list<string>  $keys
     * @return list<array{bucket: string, metric_name: string, rating: float}>
     */
    private static function scoreRowsFromBlock(array $session, array $keys, string $bucket): array
    {
        $block = null;
        foreach ($keys as $k) {
            if (isset($session[$k]) && is_array($session[$k])) {
                $block = $session[$k];
                break;
            }
        }

        if ($block === null) {
            return [];
        }

        $rows = [];
        foreach ($block as $name => $val) {
            if (! is_numeric($val)) {
                continue;
            }
            $metric = is_string($name) || is_int($name) ? (string) $name : '';
            if ($metric === '') {
                continue;
            }
            $rows[] = ['bucket' => $bucket, 'metric_name' => $metric, 'rating' => (float) $val];
        }

        return $rows;
    }

    public function syncSessionsFromPayload(array $sessions): void
    {
        DB::transaction(function () use ($sessions) {
            $this->observationSessions()->delete();

            $now = now();

            foreach (array_values($sessions) as $order => $session) {
                if (! is_array($session)) {
                    continue;
                }

                $os = $this->observationSessions()->create([
                    'sort_order' => $order,
                    'session_notes' => self::sessionNotesFromSessionPayload($session),
                ]);

                $rows = [
                    ...self::scoreRowsFromBlock($session, ['quantitative', 'Quantitative'], 'quantitative'),
                    ...self::scoreRowsFromBlock($session, ['qualitative', 'Qualitative'], 'qualitative'),
                ];

                if ($rows === []) {
                    continue;
                }

                // One INSERT per session rather than one per rubric item.
                ObservationSessionScore::insert(array_map(static fn (array $row) => [
                    ...$row,
                    'observation_session_id' => $os->getKey(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $rows));
            }
        });

        ObservationAnalytics::flushCaches();
    }
}
