<?php

namespace Database\Factories;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->roles()->count() === 0) {
                $user->assignRole(UserRole::Faculty->value);
            }

            if (! $user->isFaculty()) {
                return;
            }
            if ($user->assignedDepartments()->exists()) {
                return;
            }
            $user->assignedDepartments()->create(['department' => Department::Mathematics->value]);
        });
    }

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'employee_id' => fake()->unique()->bothify('APS-KHN-FT-###'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'wing' => Wing::Senior,
            'title' => null,
        ];
    }

    public function role(UserRole|string $role): static
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return $this->afterCreating(function (User $user) use ($value): void {
            $user->syncRoles([$value]);
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
