<?php

namespace Tests\Feature;

use App\Role;
use App\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke test for the Filament admin panel, so Filament and Livewire upgrades
 * that break a page show up here.
 */
class AdminPanelTest extends TestCase
{
    public static function adminPages(): array
    {
        return [
            'dashboard' => ['/admin'],
            'actions list' => ['/admin/actions'],
            'settings' => ['/admin/settings'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_an_admin_can_open_the_admin_page(string $url): void
    {
        $this->actingAs($this->createAdmin())->get($url)->assertOk();
    }

    public function test_the_admin_login_page_loads_for_guests(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_a_researcher_cannot_open_the_admin_panel(): void
    {
        $this->actingAs($this->user)->get('/admin')->assertForbidden();
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['description' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->sync([$role->id]);

        return $admin;
    }
}
