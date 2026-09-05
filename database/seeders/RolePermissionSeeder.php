<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
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

        // DatabaseSeeder uses WithoutModelEvents, which skips Spatie's
        // RefreshesPermissionCache hooks — flush so syncPermissions can resolve names.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Rbac::matrix() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
