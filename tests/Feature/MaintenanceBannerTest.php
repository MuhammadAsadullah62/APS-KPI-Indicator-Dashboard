<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceBannerTest extends TestCase
{
    use RefreshDatabase;

    private function window(?string $ends, ?string $starts = null): void
    {
        config([
            'maintenance.banner.enabled' => true,
            'maintenance.banner.timezone' => 'UTC',
            'maintenance.banner.starts_at' => $starts ?? now()->subDay()->toDateTimeString(),
            'maintenance.banner.ends_at' => $ends,
        ]);
    }

    public function test_banner_shows_on_the_login_page_before_the_window_ends(): void
    {
        $this->window(now()->addDays(2)->toDateTimeString());

        $this->get('/registration')
            ->assertOk()
            ->assertSee('Scheduled maintenance')
            ->assertSee('sorry for the inconvenience', false);
    }

    public function test_banner_shows_on_authenticated_pages(): void
    {
        $this->window(now()->addDays(2)->toDateTimeString());

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('Scheduled maintenance');
    }

    public function test_banner_removes_itself_once_the_window_has_passed(): void
    {
        $this->window(now()->subMinute()->toDateTimeString(), now()->subWeek()->toDateTimeString());

        $this->get('/registration')->assertOk()->assertDontSee('Scheduled maintenance');
    }

    public function test_banner_can_be_disabled(): void
    {
        $this->window(now()->addDays(2)->toDateTimeString());
        config(['maintenance.banner.enabled' => false]);

        $this->get('/registration')->assertOk()->assertDontSee('Scheduled maintenance');
    }
}
