<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * DatabaseSeeder used to run both seeders on every `db:seed`, so a server
 * could end up with an admin whose password is in the repository, or with a
 * five-year bearer token that is in the repository.
 */
class DevSeedersTest extends TestCase
{
    /**
     * db:seed only resolves the global DatabaseSeeder, so run the seeder
     * directly with a silent console attached.
     */
    private function seedWith(string $class): void
    {
        $command = new Command;
        $command->setOutput(new OutputStyle(new ArrayInput([]), new NullOutput));

        (new $class)->setContainer($this->app)->setCommand($command)->__invoke();
    }

    /** @test */
    public function the_playwright_admin_is_never_seeded_in_production()
    {
        $this->app['env'] = 'production';

        $this->seedWith(\PlaywrightUserSeeder::class);

        $this->assertNull(User::where('email', 'playwright@test.com')->first());
    }

    /** @test */
    public function the_playwright_admin_is_never_seeded_on_staging()
    {
        $this->app['env'] = 'staging';

        $this->seedWith(\PlaywrightUserSeeder::class);

        $this->assertNull(User::where('email', 'playwright@test.com')->first());
    }

    /** @test */
    public function the_fixed_mart_token_is_never_seeded_in_production()
    {
        $this->app['env'] = 'production';

        $this->seedWith(\MartAuthSeeder::class);

        $this->assertNull(User::where('email', 'mart@metag-analyze.test')->first());
    }

    /** @test */
    public function the_fixed_mart_token_is_seeded_outside_production()
    {
        $this->seedWith(\MartAuthSeeder::class);

        $this->assertNotNull(User::where('email', 'mart@metag-analyze.test')->first());
    }
}
