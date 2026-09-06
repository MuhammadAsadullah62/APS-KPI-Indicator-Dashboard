<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = \Database\Seeders\RolePermissionSeeder::class;

    private function userWithRole(UserRole $role): User
    {
        return User::factory()
            ->role($role)
            ->create([
                'wing' => $role === UserRole::SectionHead ? Wing::Senior : ($role === UserRole::Faculty ? Wing::Senior : null),
            ]);
    }

    /**
     * @return array<string, array{0: UserRole, 1: string, 2: string, 3: int}>
     */
    public static function matrix(): array
    {
        $cases = [];
        $expectations = [
            // path, method, [role => status]
            ['/', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 200,
            ]],
            ['/academicreports', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 200,
            ]],
            ['/quantitative-observations', 'get', [
                'admin' => 302, 'principal' => 302, 'section_head' => 200, 'faculty' => 200,
            ]],
            ['/qualitative-observations', 'get', [
                'admin' => 302, 'principal' => 302, 'section_head' => 200, 'faculty' => 200,
            ]],
            ['/adminpanel', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 403, 'faculty' => 403,
            ]],
            ['/systemsettings', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 200,
            ]],
            ['/sechead', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 403,
            ]],
            ['/teachermanagement', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 403,
            ]],
            ['/observations', 'get', [
                'admin' => 200, 'principal' => 200, 'section_head' => 200, 'faculty' => 403,
            ]],
        ];

        foreach ($expectations as [$path, $method, $roleStatuses]) {
            foreach ($roleStatuses as $roleValue => $status) {
                $cases["{$method} {$path} as {$roleValue}"] = [
                    UserRole::from($roleValue), $path, $method, $status,
                ];
            }
        }

        return $cases;
    }

    #[DataProvider('matrix')]
    public function test_route_authorization(UserRole $role, string $path, string $method, int $expected): void
    {
        $response = $this->actingAs($this->userWithRole($role))->{$method}($path);

        $response->assertStatus($expected);
    }

    public function test_write_route_authorization(): void
    {
        $faculty = $this->userWithRole(UserRole::Faculty);
        $sectionHead = $this->userWithRole(UserRole::SectionHead);
        $target = User::factory()->role(UserRole::SectionHead)->create(['wing' => Wing::Middle]);

        // Faculty cannot create faculty or section heads.
        $this->actingAs($faculty)->post('/faculty', [])->assertForbidden();
        $this->actingAs($faculty)->post('/section-heads', [])->assertForbidden();

        // Section heads cannot manage or delete section heads.
        $this->actingAs($sectionHead)->post('/section-heads', [])->assertForbidden();
        $this->actingAs($sectionHead)->delete("/section-heads/{$target->id}")->assertForbidden();

        // Section heads may reach faculty creation (validation, not authorization, stops empty payload).
        $this->actingAs($sectionHead)->post('/faculty', [])->assertStatus(302);
    }

    public function test_wingless_section_head_cannot_reach_observations(): void
    {
        $sh = User::factory()->role(UserRole::SectionHead)->create(['wing' => null]);

        $this->actingAs($sh)->get('/observations')->assertForbidden();
    }

    public function test_sync_roles_updates_spatie_identity(): void
    {
        $user = User::factory()->role(UserRole::Faculty)->create();
        $this->assertTrue($user->hasRole('faculty'));

        $user->syncRoles([UserRole::Principal->value]);

        $this->assertTrue($user->fresh()->hasRole('principal'));
        $this->assertFalse($user->fresh()->hasRole('faculty'));
    }
}
