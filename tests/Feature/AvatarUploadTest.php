<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\Wing;
use App\Models\User;
use App\Support\AvatarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarUploadTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = \Database\Seeders\RolePermissionSeeder::class;

    public function test_uploaded_avatar_is_downscaled_and_reencoded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        $media = AvatarService::replaceFor($user, UploadedFile::fake()->image('huge.jpg', 1600, 1200));

        $this->assertNotNull($media);
        $this->assertSame('image/webp', $media->mime_type);
        Storage::disk('public')->assertExists($media->path);

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($media->path));
        $this->assertLessThanOrEqual(512, $w);
        $this->assertLessThanOrEqual(512, $h);
    }

    public function test_replacing_avatar_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        $first = AvatarService::replaceFor($user, UploadedFile::fake()->image('a.jpg', 600, 600));
        $second = AvatarService::replaceFor($user, UploadedFile::fake()->image('b.jpg', 600, 600));

        Storage::disk('public')->assertMissing($first->path);
        Storage::disk('public')->assertExists($second->path);
        $this->assertSame(1, $user->mediaItems()->where('collection_name', 'avatar')->count());
    }

    public function test_avatar_component_renders_a_graceful_fallback(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => UserRole::Principal, 'wing' => null]);
        $sh = User::factory()->create(['role' => UserRole::SectionHead, 'wing' => Wing::Senior, 'name' => 'Jane Roe']);
        AvatarService::replaceFor($sh, UploadedFile::fake()->image('a.jpg', 400, 400));

        $html = $this->actingAs($admin)->get('/sechead')->assertOk()->getContent();

        // The <img> degrades to the initials sibling instead of a broken-image icon.
        $this->assertStringContainsString('onerror=', $html);
        $this->assertStringContainsString('nextElementSibling', $html);
        $this->assertStringContainsString('>JR<', $html); // initials fallback is in the DOM
    }

    public function test_faculty_self_service_avatar_route_stores_media(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::Faculty, 'wing' => Wing::Senior]);

        $this->actingAs($user)
            ->put('/systemsettings/avatar', ['avatar' => UploadedFile::fake()->image('me.png', 800, 800)])
            ->assertRedirect(route('systemsettings'));

        $this->assertTrue($user->mediaItems()->where('collection_name', 'avatar')->exists());
    }
}
