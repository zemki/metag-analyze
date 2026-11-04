<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectCasesController;
use Tests\TestCase;

/**
 * The graph breadcrumb read env('APP_ENV'), which is null once
 * `php artisan config:cache` has run, so production lost the /metag prefix.
 */
class GraphBreadcrumbTest extends TestCase
{
    private function breadcrumb(): array
    {
        $method = new \ReflectionMethod(ProjectCasesController::class, 'getGraphBreadcrumb');

        return $method->invoke($this->app->make(ProjectCasesController::class), $this->project, $this->case, [])['breadcrumb'];
    }

    /** @test */
    public function production_links_the_project_under_the_metag_prefix()
    {
        $this->app['env'] = 'production';

        $this->assertArrayHasKey('/metag' . $this->project->path(), $this->breadcrumb());
    }

    /** @test */
    public function other_environments_link_the_project_without_a_prefix()
    {
        $this->assertArrayHasKey($this->project->path(), $this->breadcrumb());
    }
}
