<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Blade;
use Tests\CreatesApplication;

/**
 * Blade Icons components are disabled in config/blade-icons.php: registering them
 * scanned all heroicon and Filament SVG files on every request. Icons must still
 * render through svg(), which Filament uses.
 *
 * Extends the framework TestCase on purpose: no database needed.
 */
class BladeIconsComponentsTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Blade Icons registers its components when the view factory is resolved.
        $this->app->make('view');
    }

    public function test_icon_set_components_are_not_registered(): void
    {
        $this->assertArrayNotHasKey('heroicon-o-check', Blade::getClassComponentAliases());
    }

    public function test_the_default_icon_component_stays_registered(): void
    {
        $this->assertArrayHasKey('icon', Blade::getClassComponentAliases());
    }

    public function test_filament_icons_still_render(): void
    {
        $html = Blade::render('<x-filament::icon icon="heroicon-o-check" />');

        $this->assertStringContainsString('<svg', $html);
    }
}
