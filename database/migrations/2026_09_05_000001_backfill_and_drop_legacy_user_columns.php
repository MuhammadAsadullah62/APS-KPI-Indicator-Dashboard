<?php

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Backfill legacy users.department / users.role into their replacement
     * stores, then drop those columns.
     */
    public function up(): void
    {
        $this->backfillDepartments();
        $this->dropDepartmentColumn();
        $this->backfillSpatieRoles();
        $this->dropRoleColumn();
    }

    public function down(): void
    {
        $this->restoreRoleColumn();
        $this->restoreDepartmentColumn();
    }

    private function backfillDepartments(): void
    {
        if (! Schema::hasTable('user_departments') || ! Schema::hasColumn('users', 'department')) {
            return;
        }

        $rows = DB::table('users')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->pluck('department', 'id');

        $now = now();
        foreach ($rows as $userId => $dept) {
            if (! is_string($dept) || $dept === '') {
                continue;
            }
            if (DB::table('user_departments')->where('user_id', $userId)->exists()) {
                continue;
            }
            DB::table('user_departments')->insert([
                'user_id' => $userId,
                'department' => $dept,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function dropDepartmentColumn(): void
    {
        if (! Schema::hasColumn('users', 'department')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('department');
        });
    }

    private function backfillSpatieRoles(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('model_has_roles')) {
            return;
        }

        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(Rbac::matrix()) as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_keys(Rbac::matrix()))
            ->pluck('id', 'name');

        $modelType = (new User)->getMorphClass();
        $orphans = [];

        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunkById(500, function ($users) use ($roleIds, $modelType, &$orphans): void {
            foreach ($users as $user) {
                $roleName = is_string($user->role) ? $user->role : '';
                if ($roleName === '' || ! isset($roleIds[$roleName])) {
                    if ($roleName !== '') {
                        $orphans[] = "user #{$user->id} has unknown role \"{$roleName}\"";
                    }

                    continue;
                }

                $roleId = $roleIds[$roleName];

                $existing = DB::table('model_has_roles')
                    ->where('model_type', $modelType)
                    ->where('model_id', $user->id)
                    ->pluck('role_id')
                    ->all();

                if ($existing === [(int) $roleId] || $existing === [$roleId]) {
                    continue;
                }

                DB::table('model_has_roles')
                    ->where('model_type', $modelType)
                    ->where('model_id', $user->id)
                    ->delete();

                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => $modelType,
                    'model_id' => $user->id,
                ]);
            }
        });

        if ($orphans !== []) {
            throw new \RuntimeException(
                'Cannot backfill Spatie roles; unknown users.role values: '.implode('; ', $orphans)
            );
        }

        $missing = DB::table('users')
            ->whereNotNull('role')
            ->where('role', '!=', '')
            ->whereNotIn('id', function ($q) use ($modelType): void {
                $q->select('model_id')
                    ->from('model_has_roles')
                    ->where('model_type', $modelType);
            })
            ->pluck('id')
            ->all();

        if ($missing !== []) {
            throw new \RuntimeException(
                'Spatie role backfill left users without roles: '.implode(', ', $missing)
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function dropRoleColumn(): void
    {
        if (Schema::hasIndex('users', 'users_role_wing_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_role_wing_index');
            });
        }

        if (Schema::hasColumn('users', 'wing') && ! Schema::hasIndex('users', 'users_wing_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index('wing', 'users_wing_index');
            });
        }

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('role');
            });
        }
    }

    private function restoreRoleColumn(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('role')->default('faculty')->after('email');
            });
        }

        if (Schema::hasIndex('users', 'users_wing_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex('users_wing_index');
            });
        }

        if (! Schema::hasIndex('users', 'users_role_wing_index') && Schema::hasColumn('users', 'wing')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index(['role', 'wing'], 'users_role_wing_index');
            });
        }

        if (! Schema::hasTable('model_has_roles') || ! Schema::hasTable('roles')) {
            return;
        }

        $modelType = (new User)->getMorphClass();

        $pairs = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', $modelType)
            ->where('roles.guard_name', 'web')
            ->select('model_has_roles.model_id', 'roles.name')
            ->orderBy('model_has_roles.role_id')
            ->get()
            ->groupBy('model_id');

        foreach ($pairs as $userId => $roles) {
            if ($roles->count() !== 1) {
                continue;
            }
            DB::table('users')
                ->where('id', $userId)
                ->update(['role' => $roles->first()->name]);
        }
    }

    private function restoreDepartmentColumn(): void
    {
        if (Schema::hasColumn('users', 'department')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('department')->nullable()->after('wing');
        });

        if (! Schema::hasTable('user_departments')) {
            return;
        }

        $firstDepts = DB::table('user_departments')
            ->select('user_id', 'department')
            ->orderBy('id')
            ->get()
            ->unique('user_id');

        foreach ($firstDepts as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->whereNull('department')
                ->update(['department' => $row->department]);
        }
    }
};
