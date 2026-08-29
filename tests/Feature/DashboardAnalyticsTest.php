<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\Observation;
use App\Models\User;
use App\Support\ObservationAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = \Database\Seeders\RolePermissionSeeder::class;

    /**
     * @param  array<string, float>  $override  metric => rating
     */
    private function sessionPayload(float $fill = 4.0, array $override = []): array
    {
        $block = fn (array $names) => collect($names)
            ->mapWithKeys(fn (string $n) => [$n => $override[$n] ?? $fill])
            ->all();

        return [
            'quantitative' => $block(ObservationAnalytics::QUANT_METRICS),
            'qualitative' => $block(ObservationAnalytics::QUAL_METRICS),
            'session_notes' => '',
        ];
    }

    private function recordObservation(User $observer, User $observee, float $fill): Observation
    {
        $sessions = [$this->sessionPayload($fill)];

        $observation = Observation::create([
            'observer_id' => $observer->id,
            'observee_id' => $observee->id,
            'aggregate_percent' => ObservationAnalytics::computeWeightedAggregateFromSessionsPayload($sessions),
        ]);
        $observation->syncSessionsFromPayload($sessions);

        return $observation;
    }

    public function test_rankings_order_by_average_score(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $strong = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior, 'name' => 'Strong']);
        $weak = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior, 'name' => 'Weak']);

        $this->recordObservation($principal, $strong, 5.0);
        $this->recordObservation($principal, $weak, 2.0);

        $ranked = ObservationAnalytics::rankedFaculty();

        $this->assertSame(['Strong', 'Weak'], $ranked->pluck('user.name')->all());
        $this->assertSame([1, 2], $ranked->pluck('rank')->all());
    }

    public function test_principal_dashboard_query_count_is_bounded(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $sh = User::factory()->create(['role' => UserRole::SectionHead, 'wing' => Wing::Senior]);

        foreach (range(1, 6) as $i) {
            $faculty = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::cases()[$i % 3]]);
            $this->recordObservation($principal, $faculty, 3.0 + ($i % 3));
            $this->recordObservation($principal, $sh, 4.0);
        }

        ObservationAnalytics::flushCaches();

        DB::enableQueryLog();
        $this->actingAs($principal)->get('/')->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        if (getenv('SHOW_QUERY_COUNT')) {
            fwrite(STDERR, "\nPrincipal dashboard queries: {$count}\n");
        }

        $this->assertLessThan(20, $count, "Principal dashboard issued {$count} queries");
    }

    public function test_new_observation_invalidates_cached_rankings(): void
    {
        $principal = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $a = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);
        $b = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        $this->recordObservation($principal, $a, 5.0);
        $this->recordObservation($principal, $b, 4.0);

        $this->assertSame($a->id, ObservationAnalytics::rankedFaculty()->first()['user']->id);

        // Push b above a with a strong second observation.
        $this->recordObservation($principal, $b, 5.0);
        $this->recordObservation($principal, $a, 1.0);

        $this->assertSame($b->id, ObservationAnalytics::rankedFaculty()->first()['user']->id);
    }
}
