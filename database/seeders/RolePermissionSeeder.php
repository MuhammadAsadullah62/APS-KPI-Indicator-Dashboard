<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (Rbac::matrix() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        // Align every existing user's Spatie role with its `role` column.
        DB::transaction(function (): void {
            User::query()->select(['id', 'role'])->chunkById(500, function ($users): void {
                foreach ($users as $user) {
                    $value = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
                    if (in_array($value, array_keys(Rbac::matrix()), true)) {
                        $user->syncRoles([$value]);
                    }
                }
            });
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
