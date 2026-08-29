<?php

namespace App\Support;

use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class ObservationAnalytics
{
    /** Org-wide ranking data survives ~10 min between observation writes. */
    private const CACHE_TTL = 600;

    private const CACHE_KEYS = ['obs:analytics:summaries:all', 'obs:analytics:board:all'];

    /** Per-request memo so a single dashboard render never repeats a query. */
    private static array $memo = [];

    /**
     * Drop every cached / memoised aggregate. Called from the Observation model
     * events whenever an observation (or its sessions / scores) changes.
     */
    public static function flushCaches(): void
    {
        self::$memo = [];
        self::$normalizedBlockCache = [];
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }

    public const QUANT_METRICS = ['Student Achievement', 'Student Progress', 'Lesson Planning', 'Assessment Quality', 'Attendance'];

    public const QUAL_METRICS = ['Student-Centricity', 'Professional Ethics', 'Classroom Culture', 'Communication', 'Collaboration', 'Innovation'];

    public const QUAL_KPI_METRICS = [
        'Student-Centricity',
        'Professional Ethics',
        'Classroom Culture',
        'Communication',
        'Innovation',
    ];

    public static function summariesByObservee(?int $observerOnly = null): Collection
    {
        $memoKey = 'summaries:'.($observerOnly ?? 'all');

        if (isset(self::$memo[$memoKey])) {
            return self::$memo[$memoKey];
        }

        $build = function () use ($observerOnly): array {
            $query = Observation::query()->whereNotNull('aggregate_percent');

            if ($observerOnly !== null) {
                $query->where('observer_id', $observerOnly);
            }

            return $query
                ->groupBy('observee_id')
                ->selectRaw('observee_id, ROUND(AVG(aggregate_percent), 2) as avg_score, COUNT(*) as observation_count')
                ->get()
                ->map(fn ($row) => [
                    'observee_id' => (int) $row->observee_id,
                    'avg_score' => (float) $row->avg_score,
                    'observation_count' => (int) $row->observation_count,
                ])
                ->all();
        };

        $rows = $observerOnly === null
            ? Cache::remember('obs:analytics:summaries:all', self::CACHE_TTL, $build)
            : $build();

        return self::$memo[$memoKey] = collect($rows)
            ->map(fn (array $r) => (object) $r)
            ->keyBy('observee_id');
    }

    /**
     * All ranked observees (section heads + faculty) in one pass: a single
     * summary query + a single user query, sorted by score descending. The
     * ranked* helpers are in-memory slices of this, so a dashboard that needs
     * five wing tables issues two queries, not a dozen.
     */
    public static function rankingBoard(?int $observerOnly = null): Collection
    {
        $memoKey = 'board:'.($observerOnly ?? 'all');

        if (isset(self::$memo[$memoKey])) {
            return self::$memo[$memoKey];
        }

        $summaries = self::summariesByObservee($observerOnly);
        if ($summaries->isEmpty()) {
            return self::$memo[$memoKey] = collect();
        }

        $users = User::query()
            ->whereIn('id', $summaries->keys()->all())
            ->with(['avatarMedia', 'assignedDepartments'])
            ->get()
            ->keyBy('id');

        return self::$memo[$memoKey] = $summaries
            ->map(function ($row) use ($users) {
                $user = $users->get((int) $row->observee_id);

                return $user === null ? null : [
                    'user' => $user,
                    'avg_score' => (float) $row->avg_score,
                    'observation_count' => (int) $row->observation_count,
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $r) => $r['avg_score'])
            ->values();
    }

    /**
     * @param  (callable(User): bool)|null  $keep
     */
    public static function rankedUsers(?callable $keep = null, ?int $observerOnly = null): Collection
    {
        $rows = self::rankingBoard($observerOnly);

        if ($keep !== null) {
            $rows = $rows->filter(fn (array $r) => $keep($r['user']))->values();
        }

        return $rows->map(function (array $row, int $idx) {
            $row['rank'] = $idx + 1;

            return $row;
        });
    }

    public static function rankedSectionHeads(?int $observerOnly = null): Collection
    {
        return self::rankedUsers(fn (User $u) => $u->isSectionHead(), $observerOnly);
    }

    public static function rankedFaculty(?int $observerOnly = null): Collection
    {
        return self::rankedUsers(fn (User $u) => $u->isFaculty(), $observerOnly);
    }

    public static function rankedStaffCombined(?int $observerOnly = null): Collection
    {
        return self::rankedUsers(fn (User $u) => $u->isSectionHead() || $u->isFaculty(), $observerOnly);
    }

    public static function rankedFacultyInWing(?\App\Enums\Wing $wing, ?int $observerOnly = null): Collection
    {
        return self::rankedUsers(
            fn (User $u) => $u->isFaculty() && $u->wing === $wing,
            $observerOnly
        );
    }

    /** Memo of normalised-key => numeric-value maps, keyed by the block's identity. */
    private static array $normalizedBlockCache = [];

    private static function numericMetricFromRubricBlock(array $block, string $canonicalName): ?float
    {
        if (isset($block[$canonicalName]) && is_numeric($block[$canonicalName])) {
            return (float) $block[$canonicalName];
        }

        $cacheKey = md5(serialize($block));
        $normalized = self::$normalizedBlockCache[$cacheKey] ??= self::normalizeRubricBlock($block);

        $want = self::normalizeMetricName($canonicalName);

        return $normalized[$want] ?? null;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, float>
     */
    private static function normalizeRubricBlock(array $block): array
    {
        $out = [];
        foreach ($block as $rawKey => $val) {
            if (! is_numeric($val)) {
                continue;
            }
            $key = is_string($rawKey) || is_int($rawKey) ? (string) $rawKey : '';
            $out[self::normalizeMetricName($key)] = (float) $val;
        }

        return $out;
    }

    private static function normalizeMetricName(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function sessionsIterable(Observation $observation): array
    {
        if (! $observation->relationLoaded('observationSessions')) {
            $observation->load('observationSessions.scores');
        } else {
            $observation->loadMissing('observationSessions.scores');
        }

        return $observation->observationSessions
            ->sortBy('sort_order')
            ->values()
            ->map(fn (ObservationSession $s) => $s->toRubricSessionArray())
            ->all();
    }

    private static function quantitativeBlockFromSession(array $session): array
    {
        foreach (['quantitative', 'Quantitative'] as $k) {
            if (isset($session[$k]) && is_array($session[$k])) {
                return $session[$k];
            }
        }

        return [];
    }

    private static function qualitativeBlockFromSession(array $session): array
    {
        foreach (['qualitative', 'Qualitative'] as $k) {
            if (isset($session[$k]) && is_array($session[$k])) {
                return $session[$k];
            }
        }

        return [];
    }

    /**
     * True when the session has a numeric 1–5 value for every quantitative and qualitative rubric name.
     *
     * @param  array<string, mixed>  $session
     */
    public static function rubricSessionArrayIsComplete(array $session): bool
    {
        if (! is_array($session)) {
            return false;
        }
        $q = self::quantitativeBlockFromSession($session);
        $l = self::qualitativeBlockFromSession($session);

        return self::rubricBlockHasAllValidRatings($q, self::QUANT_METRICS)
            && self::rubricBlockHasAllValidRatings($l, self::QUAL_METRICS);
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<string>  $metricNames
     */
    private static function rubricBlockHasAllValidRatings(array $block, array $metricNames): bool
    {
        foreach ($metricNames as $name) {
            $v = self::numericMetricFromRubricBlock($block, $name);
            if ($v === null || $v < 1 || $v > 5) {
                return false;
            }
        }

        return true;
    }

    /**
     * True when every session in the array is a complete quant + qual rubric (same as audit portal).
     *
     * @param  list<array<string, mixed>>  $sessions
     */
    public static function sessionsPayloadRubricIsComplete(array $sessions): bool
    {
        if ($sessions === []) {
            return false;
        }
        foreach (array_values($sessions) as $session) {
            if (! is_array($session) || ! self::rubricSessionArrayIsComplete($session)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 70% mean quantitative % + 30% mean qualitative % per session, then mean across sessions. Null if any session is incomplete.
     *
     * @param  list<array<string, mixed>>  $sessions
     */
    public static function computeWeightedAggregateFromSessionsPayload(array $sessions): ?int
    {
        if (! self::sessionsPayloadRubricIsComplete($sessions)) {
            return null;
        }
        $sessionScores = [];
        foreach (array_values($sessions) as $session) {
            if (! is_array($session)) {
                return null;
            }
            $q = self::quantitativeBlockFromSession($session);
            $l = self::qualitativeBlockFromSession($session);
            $qAvg = self::meanBlockToPercent0to100($q, self::QUANT_METRICS);
            $lAvg = self::meanBlockToPercent0to100($l, self::QUAL_METRICS);
            $sessionScores[] = ($qAvg * 0.7) + ($lAvg * 0.3);
        }

        if ($sessionScores === []) {
            return null;
        }

        return (int) round(array_sum($sessionScores) / count($sessionScores));
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  list<string>  $metricNames
     */
    private static function meanBlockToPercent0to100(array $block, array $metricNames): float
    {
        $sum = 0.0;
        $c = 0;
        foreach ($metricNames as $name) {
            $v = self::numericMetricFromRubricBlock($block, $name);
            if ($v === null) {
                return 0.0;
            }
            $sum += (float) $v;
            $c++;
        }
        if ($c < 1) {
            return 0.0;
        }

        return ($sum / ($c * 5.0)) * 100.0;
    }

    public static function averagedSessionMetrics(iterable $observations): array
    {
        $signature = self::observationsSignature($observations);
        if ($signature !== null && isset(self::$memo[$signature])) {
            return self::$memo[$signature];
        }

        $quantSums = [];
        $quantN = [];
        $qualSums = [];
        $qualN = [];

        foreach ($observations as $observation) {
            if (! $observation instanceof Observation) {
                continue;
            }
            foreach (self::sessionsIterable($observation) as $session) {
                if (! is_array($session)) {
                    continue;
                }
                if (! self::rubricSessionArrayIsComplete($session)) {
                    continue;
                }
                $qBlock = self::quantitativeBlockFromSession($session);
                $lBlock = self::qualitativeBlockFromSession($session);

                foreach (self::QUANT_METRICS as $metricName) {
                    $v = self::numericMetricFromRubricBlock($qBlock, $metricName);
                    if ($v !== null) {
                        $quantSums[$metricName] = ($quantSums[$metricName] ?? 0) + $v;
                        $quantN[$metricName] = ($quantN[$metricName] ?? 0) + 1;
                    }
                }
                foreach (self::QUAL_METRICS as $metricName) {
                    $v = self::numericMetricFromRubricBlock($lBlock, $metricName);
                    if ($v !== null) {
                        $qualSums[$metricName] = ($qualSums[$metricName] ?? 0) + $v;
                        $qualN[$metricName] = ($qualN[$metricName] ?? 0) + 1;
                    }
                }
            }
        }

        $quantitative = [];
        foreach (self::QUANT_METRICS as $metricName) {
            $n = (int) ($quantN[$metricName] ?? 0);
            if ($n < 1) {
                continue;
            }
            $sum = $quantSums[$metricName] ?? 0.0;
            $quantitative[$metricName] = round($sum / $n, 2);
        }

        $qualitative = [];
        foreach (self::QUAL_METRICS as $metricName) {
            $n = (int) ($qualN[$metricName] ?? 0);
            if ($n < 1) {
                continue;
            }
            $sum = $qualSums[$metricName] ?? 0.0;
            $qualitative[$metricName] = round($sum / $n, 2);
        }

        $result = ['quantitative' => $quantitative, 'qualitative' => $qualitative];

        if ($signature !== null) {
            self::$memo[$signature] = $result;
        }

        return $result;
    }

    /**
     * Cheap identity for a set of observations: null when we cannot memo safely
     * (e.g. a lazy generator we must not exhaust).
     */
    private static function observationsSignature(iterable $observations): ?string
    {
        if (! is_array($observations) && ! $observations instanceof Collection) {
            return null;
        }

        $parts = [];
        foreach ($observations as $observation) {
            if (! $observation instanceof Observation || $observation->getKey() === null) {
                return null;
            }
            $parts[] = $observation->getKey().'@'.optional($observation->updated_at)->getTimestamp();
        }

        sort($parts);

        return 'metrics:'.md5(implode('|', $parts));
    }

    /**
     * Mean of per-metric rubric averages (1–5 scale) converted to a single 0–100 score.
     * Uses the same definition as the dashboard “rubric” panels for quantitative metrics.
     */
    public static function averageQuantPercent(iterable $observations): ?float
    {
        $row = self::averagedSessionMetrics($observations)['quantitative'];

        return self::averagePercentFromRubricAverages($row, self::QUANT_METRICS);
    }

    /**
     * Mean of per-metric rubric averages (1–5 scale) converted to a single 0–100 score.
     */
    public static function averageQualPercent(iterable $observations): ?float
    {
        $row = self::averagedSessionMetrics($observations)['qualitative'];

        return self::averagePercentFromRubricAverages($row, self::QUAL_METRICS);
    }

    /**
     * Fill width for a horizontal benchmark bar: green ≥85, amber 70–84.9, red &lt;70.
     */
    public static function kpiTierBarBgClass(?float $percent): string
    {
        if ($percent === null) {
            return 'bg-slate-300';
        }
        if ($percent >= 85.0) {
            return 'bg-emerald-500';
        }
        if ($percent >= 70.0) {
            return 'bg-amber-500';
        }

        return 'bg-rose-500';
    }

    /**
     * Same math as {@see averageQualPercent} but from a pre-aggregated metric row (e.g. dashboard arrays).
     *
     * @param  array<string, float|int>  $metricNameToAvgRating
     */
    public static function averageQualPercentFromMetricRow(array $metricNameToAvgRating): ?float
    {
        return self::averagePercentFromRubricAverages($metricNameToAvgRating, self::QUAL_METRICS);
    }

    /**
     * @param  array<string, float|int>  $metricNameToAvgRating
     */
    public static function averageQuantPercentFromMetricRow(array $metricNameToAvgRating): ?float
    {
        return self::averagePercentFromRubricAverages($metricNameToAvgRating, self::QUANT_METRICS);
    }

    /**
     * @param  array<string, float|int>  $metricNameToAvgRating
     * @param  list<string>  $metricOrder
     */
    private static function averagePercentFromRubricAverages(array $metricNameToAvgRating, array $metricOrder): ?float
    {
        $pcts = collect($metricOrder)
            ->map(function (string $name) use ($metricNameToAvgRating) {
                $s = $metricNameToAvgRating[$name] ?? null;
                if (! is_numeric($s)) {
                    return null;
                }

                return ((float) $s / 5) * 100;
            })
            ->filter(fn ($v) => $v !== null);

        if ($pcts->isEmpty()) {
            return null;
        }

        return round((float) $pcts->avg(), 1);
    }

    public static function totalSessionsInObservations(iterable $observations): int
    {
        $total = 0;
        foreach ($observations as $observation) {
            if (! $observation instanceof Observation) {
                continue;
            }
            $total += count(self::sessionsIterable($observation));
        }

        return $total;
    }

    public static function averageAggregatePercent(iterable $observations): ?float
    {
        $sum = 0.0;
        $n = 0;
        foreach ($observations as $observation) {
            if (! $observation instanceof Observation) {
                continue;
            }
            if ($observation->aggregate_percent === null) {
                continue;
            }
            $sum += (float) $observation->aggregate_percent;
            $n++;
        }

        if ($n < 1) {
            return null;
        }

        return round($sum / $n, 1);
    }

    public static function kpiQuantitativeCardsFromObservations(iterable $observations): array
    {
        $collection = self::sortedObservationCollection($observations);
        $quant = self::averagedSessionMetrics($collection)['quantitative'];

        $ratingToPct = function (?float $avgRating): ?float {
            if ($avgRating === null) {
                return null;
            }

            return round(($avgRating / 5) * 100, 1);
        };

        $cards = [];
        foreach (self::QUANT_METRICS as $name) {
            $cards[] = [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => $name,
                'display' => self::formatPct($ratingToPct($quant[$name] ?? null)),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', $name)),
            ];
        }

        return $cards;
    }

    public static function kpiQualitativeCardsFromObservations(iterable $observations): array
    {
        $collection = self::sortedObservationCollection($observations);
        $qual = self::averagedSessionMetrics($collection)['qualitative'];

        $ratingToPct = function (?float $avgRating): ?float {
            if ($avgRating === null) {
                return null;
            }

            return round(($avgRating / 5) * 100, 1);
        };

        $cards = [];
        foreach (self::QUAL_KPI_METRICS as $name) {
            $cards[] = [
                'category' => 'qualitative',
                'category_label' => 'Qualitative',
                'metric_name' => $name,
                'display' => self::formatPct($ratingToPct($qual[$name] ?? null)),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'qualitative', $name)),
            ];
        }

        return $cards;
    }

    public static function kpiCardsFromObservations(iterable $observations): array
    {
        $collection = self::sortedObservationCollection($observations);
        $metrics = self::averagedSessionMetrics($collection);
        $quant = $metrics['quantitative'];
        $qual = $metrics['qualitative'];

        $overallAvg = $collection->filter(fn (Observation $o) => $o->aggregate_percent !== null)->avg('aggregate_percent');
        $overallPct = $overallAvg !== null ? round((float) $overallAvg, 1) : null;

        $ratingToPct = function (?float $avgRating): ?float {
            if ($avgRating === null) {
                return null;
            }

            return round(($avgRating / 5) * 100, 1);
        };

        $pickQ = fn (string $name) => $ratingToPct($quant[$name] ?? null);
        $pickL = fn (string $name) => $ratingToPct($qual[$name] ?? null);

        return [
            [
                'category' => 'overall',
                'category_label' => 'Summary',
                'metric_name' => 'Overall observation score',
                'display' => $overallPct !== null ? $overallPct.'%' : '—',
                ...self::sparkPathsForCard(self::sparklineAggregatePaths($collection)),
            ],
            [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => 'Student Achievement',
                'display' => self::formatPct($pickQ('Student Achievement')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', 'Student Achievement')),
            ],
            [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => 'Lesson Planning',
                'display' => self::formatPct($pickQ('Lesson Planning')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', 'Lesson Planning')),
            ],
            [
                'category' => 'qualitative',
                'category_label' => 'Qualitative',
                'metric_name' => 'Student-Centricity',
                'display' => self::formatPct($pickL('Student-Centricity')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'qualitative', 'Student-Centricity')),
            ],
            [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => 'Attendance',
                'display' => self::formatPct($pickQ('Attendance')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', 'Attendance')),
            ],
            [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => 'Assessment Quality',
                'display' => self::formatPct($pickQ('Assessment Quality')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', 'Assessment Quality')),
            ],
            [
                'category' => 'quantitative',
                'category_label' => 'Quantitative',
                'metric_name' => 'Student Progress',
                'display' => self::formatPct($pickQ('Student Progress')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'quantitative', 'Student Progress')),
            ],
            [
                'category' => 'qualitative',
                'category_label' => 'Qualitative',
                'metric_name' => 'Innovation',
                'display' => self::formatPct($pickL('Innovation')),
                ...self::sparkPathsForCard(self::sparklineMetricPaths($collection, 'qualitative', 'Innovation')),
            ],
        ];
    }

    private static function sortedObservationCollection(iterable $observations): Collection
    {
        return Collection::wrap($observations)
            ->filter(fn ($o) => $o instanceof Observation)
            ->sortBy(fn (Observation $o) => $o->created_at?->timestamp ?? 0)
            ->values();
    }

    private static function formatPct(?float $pct): string
    {
        return $pct !== null ? $pct.'%' : '—';
    }

    private static function sparkPathsForCard(?array $paths): array
    {
        if ($paths === null) {
            return ['spark_line_path' => null, 'spark_area_path' => null, 'trend' => 'flat'];
        }

        return [
            'spark_line_path' => $paths['line'],
            'spark_area_path' => $paths['area'],
            'trend' => $paths['trend'],
        ];
    }

    private static function sparklineAggregatePaths(Collection $observations): ?array
    {
        $series = $observations
            ->filter(fn (Observation $o) => $o->aggregate_percent !== null)
            ->map(fn (Observation $o) => (float) $o->aggregate_percent)
            ->values()
            ->all();

        return self::smoothWavePathsFromSeries($series);
    }

    private static function sparklineMetricPaths(Collection $observations, string $bucket, string $metric): ?array
    {
        $series = [];
        foreach ($observations as $observation) {
            $avg = self::averageMetricPerObservationSessions($observation, $bucket, $metric);
            if ($avg !== null) {
                $series[] = ($avg / 5) * 100;
            }
        }

        return self::smoothWavePathsFromSeries($series);
    }

    private static function averageMetricPerObservationSessions(Observation $observation, string $bucket, string $metric): ?float
    {
        $sum = 0.0;
        $n = 0;
        foreach (self::sessionsIterable($observation) as $session) {
            if (! is_array($session)) {
                continue;
            }
            $block = $bucket === 'quantitative'
                ? self::quantitativeBlockFromSession($session)
                : self::qualitativeBlockFromSession($session);
            $val = self::numericMetricFromRubricBlock($block, $metric);
            if ($val !== null) {
                $sum += $val;
                $n++;
            }
        }

        return $n > 0 ? $sum / $n : null;
    }

    private static function smoothWavePathsFromSeries(array $series): ?array
    {
        $series = array_values(array_filter($series, fn ($v) => is_numeric($v)));
        if (count($series) < 2) {
            return null;
        }

        $first = (float) $series[0];
        $last = (float) $series[count($series) - 1];
        $trend = match (true) {
            $last > $first => 'up',
            $last < $first => 'down',
            default => 'flat',
        };

        $min = min($series);
        $max = max($series);
        $rawRange = max(0.001, $max - $min);

        $midVal = ($min + $max) / 2;
        $range = max($rawRange, 14.0);
        $min = max(0.0, $midVal - $range / 2);
        $max = min(100.0, $midVal + $range / 2);
        if ($max <= $min) {
            $max = min(100.0, $min + 0.001);
        }
        $range = max(0.001, $max - $min);

        $padX = 4.0;
        $padY = 5.0;
        $plotW = 92.0;
        $plotH = 18.0;
        $bottomY = $padY + $plotH + 3.0;

        $n = count($series);
        $pts = [];
        foreach ($series as $i => $v) {
            $x = $padX + ($n <= 1 ? $plotW / 2 : ($i / ($n - 1)) * $plotW);
            $norm = ($v - $min) / $range;

            $centred = ($norm - 0.5) * 1.22 + 0.5;
            $norm = max(0.0, min(1.0, $centred));
            $y = $padY + $plotH - $norm * $plotH;
            $pts[] = [$x, $y];
        }

        $linePath = count($pts) === 2
            ? self::quadraticWaveBetween($pts[0], $pts[1])
            : self::catmullRomBezierOpenPath($pts);

        $firstX = round($pts[0][0], 3);
        $lastX = round($pts[$n - 1][0], 3);
        $areaPath = $linePath.' L '.$lastX.' '.$bottomY.' L '.$firstX.' '.$bottomY.' Z';

        return ['line' => $linePath, 'area' => $areaPath, 'trend' => $trend];
    }

    private static function quadraticWaveBetween(array $p0, array $p1): string
    {
        $mx = ($p0[0] + $p1[0]) / 2;
        $my = ($p0[1] + $p1[1]) / 2;
        $dx = abs($p1[0] - $p0[0]);
        $dy = abs($p1[1] - $p0[1]);

        $bulge = min(9.5, max(4.0, $dx * 0.22 + $dy * 0.55));
        $cy = $my - $bulge;

        return 'M '.round($p0[0], 3).' '.round($p0[1], 3)
            .' Q '.round($mx, 3).' '.round($cy, 3).' '.round($p1[0], 3).' '.round($p1[1], 3);
    }

    private static function catmullRomBezierOpenPath(array $pts): string
    {
        $n = count($pts);
        if ($n < 2) {
            return '';
        }
        if ($n === 2) {
            return self::quadraticWaveBetween($pts[0], $pts[1]);
        }

        $d = '';
        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $pts[max(0, $i - 1)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min($n - 1, $i + 2)];

            $tension = 4;
            $cp1x = $p1[0] + ($p2[0] - $p0[0]) / $tension;
            $cp1y = $p1[1] + ($p2[1] - $p0[1]) / $tension;
            $cp2x = $p2[0] - ($p3[0] - $p1[0]) / $tension;
            $cp2y = $p2[1] - ($p3[1] - $p1[1]) / $tension;

            if ($i === 0) {
                $d = 'M '.round($p1[0], 3).' '.round($p1[1], 3)
                    .' C '.round($cp1x, 3).' '.round($cp1y, 3).', '.round($cp2x, 3).' '.round($cp2y, 3).', '.round($p2[0], 3).' '.round($p2[1], 3);
            } else {
                $d .= ' C '.round($cp1x, 3).' '.round($cp1y, 3).', '.round($cp2x, 3).' '.round($cp2y, 3).', '.round($p2[0], 3).' '.round($p2[1], 3);
            }
        }

        return $d;
    }

    /**
     * Per-session and per-visit text for the observee (faculty / section head) dashboard.
     *
     * @return array{session_blocks: list<array{n: int, text: ?string, observed_at: mixed, observer_name: string}>, overall_blocks: list<array{observed_at: mixed, observer_name: string, text: ?string}>}
     */
    public static function observeeDashboardRemarks(iterable $observations): array
    {
        $col = $observations instanceof Collection
            ? $observations
            : collect($observations);
        $col = $col
            ->filter(fn ($o) => $o instanceof Observation)
            ->sortBy('created_at')
            ->values();

        $sessionBlocks = [];
        $overallBlocks = [];
        $sessionNum = 0;

        foreach ($col as $observation) {
            $observation->loadMissing('observer', 'observationSessions');
            $observerName = $observation->observer?->name ?? 'Observer';
            $dt = $observation->created_at;

            $overallBlocks[] = [
                'observed_at' => $dt,
                'observer_name' => $observerName,
                'text' => filled($observation->notes) ? (string) $observation->notes : null,
            ];

            foreach ($observation->observationSessions->sortBy('sort_order') as $session) {
                $sessionNum++;
                $t = $session->session_notes;
                $text = is_string($t) && trim($t) !== '' ? $t : null;
                $sessionBlocks[] = [
                    'n' => $sessionNum,
                    'text' => $text,
                    'observed_at' => $dt,
                    'observer_name' => $observerName,
                ];
            }
        }

        return [
            'session_blocks' => $sessionBlocks,
            'overall_blocks' => $overallBlocks,
        ];
    }
}
