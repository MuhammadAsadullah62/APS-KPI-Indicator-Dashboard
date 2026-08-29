<?php

use App\Support\Rbac;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Data backfill for an already-live `main` database.
 *
 * `create_permission_tables` only creates the empty Spatie tables. Without this
 * step every `$user->can(...)` check returns false after deploy and every gated
 * route 403s. This migration:
 *
 *   1. creates the `web`-guard permissions + roles from {@see \App\Support\Rbac},
 *   2. assigns each existing user the Spatie role that matches its `users.role`
 *      enum column (the app keeps them in sync on save from here on).
 *
 * It delegates to {@see RolePermissionSeeder}, which is fully idempotent, so it
 * is safe on a fresh install, a partially-seeded DB, or a re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // The seeder does findOrCreate + syncRoles/syncPermissions — no duplicates.
        (new RolePermissionSeeder)->run();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleNames = array_keys(Rbac::matrix());

        // FK cascade clears model_has_roles / role_has_permissions / model_has_permissions.
        DB::table(config('permission.table_names.roles'))
            ->where('guard_name', 'web')
            ->whereIn('name', $roleNames)
            ->delete();

        DB::table(config('permission.table_names.permissions'))
            ->where('guard_name', 'web')
            ->whereIn('name', Rbac::PERMISSIONS)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
