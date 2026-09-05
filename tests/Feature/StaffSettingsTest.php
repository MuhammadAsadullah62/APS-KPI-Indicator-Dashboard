<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = \Database\Seeders\RolePermissionSeeder::class;

    public function test_faculty_and_section_head_see_staff_settings_page(): void
    {
        $faculty = User::factory()->role(UserRole::Faculty)->create(['wing' => Wing::Senior]);
        $sectionHead = User::factory()->role(UserRole::SectionHead)->create(['wing' => Wing::Senior]);

        $this->actingAs($faculty)->get('/systemsettings')
            ->assertOk()
            ->assertSee('Account details')
            ->assertSee('My account');

        $this->actingAs($sectionHead)->get('/systemsettings')
            ->assertOk()
            ->assertSee('Account details')
            ->assertSee('My account');
    }

    public function test_faculty_can_update_name_email_and_password(): void
    {
        $user = User::factory()->role(UserRole::Faculty)->create([
            'wing' => Wing::Senior,
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->put('/systemsettings/profile', [
                'name' => 'New Spelling',
                'email' => 'new@example.com',
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ])
            ->assertRedirect(route('systemsettings'));

        $user->refresh();
        $this->assertSame('New Spelling', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_section_head_can_update_avatar_via_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::SectionHead)->create(['wing' => Wing::Senior]);

        $this->actingAs($user)
            ->put('/systemsettings/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('me.png', 800, 800),
            ])
            ->assertRedirect(route('systemsettings'));

        $this->assertTrue($user->mediaItems()->where('collection_name', 'avatar')->exists());
    }

    public function test_admin_cannot_use_staff_profile_update_route(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)
            ->put('/systemsettings/profile', [
                'name' => 'Hacker',
                'email' => 'hack@example.com',
            ])
            ->assertForbidden();
    }
}
