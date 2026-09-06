<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = \Database\Seeders\RolePermissionSeeder::class;

    public function test_layout_uses_compiled_bundle_not_cdn(): void
    {
        $user = User::factory()->role(UserRole::Principal)->create(['wing' => null]);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('cdn.tailwindcss.com', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', $html);
        $this->assertStringContainsString('/build/assets/app-', $html);
    }
}
