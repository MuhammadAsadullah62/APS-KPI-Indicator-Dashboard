<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Proves the backfill migration turns an already-live `main` database (Spatie
 * tables present but empty, users carrying only the `role` enum column) into a
 * fully working RBAC state, without a separate seeder step.
 */
class MigrationBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function simulateLegacyMainDatabase(): void
    {
        // Wipe everything the backfill is responsible for populating.
        DB::table(config('permission.table_names.model_has_roles'))->delete();
        DB::table(config('permission.table_names.model_has_permissions'))->delete();
        DB::table(config('permission.table_names.role_has_permissions'))->delete();
        DB::table(config('permission.table_names.roles'))->delete();
        DB::table(config('permission.table_names.permissions'))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function runBackfillMigration(): void
    {
        $migration = require database_path(
            'migrations/2026_08_30_120000_backfill_spatie_roles_permissions_and_user_assignments.php'
        );
        $migration->up();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_backfill_gives_every_existing_user_the_matching_permissions(): void
    {
        $this->simulateLegacyMainDatabase();

        $admin = User::factory()->create(['role' => UserRole::Admin, 'wing' => null]);
        $principal = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $sectionHead = User::factory()->create(['role' => UserRole::SectionHead, 'wing' => Wing::Senior]);
        $faculty = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        // Legacy state: enum column set, no Spatie roles.
        DB::table(config('permission.table_names.model_has_roles'))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertFalse($faculty->fresh()->can('metricpages.view'));

        $this->runBackfillMigration();

        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($principal->fresh()->can('faculty.manage'));
        $this->assertTrue($sectionHead->fresh()->can('observations.record'));
        $this->assertTrue($faculty->fresh()->can('metricpages.view'));
        $this->assertFalse($faculty->fresh()->can('faculty.manage'));
        $this->assertFalse($faculty->fresh()->can('adminpanel.view'));
    }

    public function test_backfill_is_idempotent(): void
    {
        $this->simulateLegacyMainDatabase();
        $faculty = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        $this->runBackfillMigration();
        $this->runBackfillMigration();

        $this->assertSame(1, $faculty->fresh()->roles()->count());
        $this->assertSame(
            1,
            DB::table(config('permission.table_names.roles'))->where('name', 'faculty')->count()
        );
    }

    public function test_gated_routes_work_after_backfill_without_running_the_seeder(): void
    {
        $this->simulateLegacyMainDatabase();
        $principal = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $faculty = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);
        DB::table(config('permission.table_names.model_has_roles'))->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->runBackfillMigration();

        $this->actingAs($principal)->get('/adminpanel')->assertOk();
        $this->actingAs($faculty)->get('/adminpanel')->assertForbidden();
        $this->actingAs($faculty)->get('/')->assertOk();
    }
}
