<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $principal = User::query()->updateOrCreate(
            ['email' => 'farhat.noor@apsacskhanewal.edu.pk'],
            [
                'name' => 'Farhat Noor',
                'employee_id' => 'APS-KHN-P001',
                'title' => 'Principal APSAC Khanewal',
                'password' => Hash::make((string) env('ADMIN_SEED_PASSWORD', 'password')),
            ]
        );

        $principal->syncRoles([UserRole::Principal->value]);
    }
}
