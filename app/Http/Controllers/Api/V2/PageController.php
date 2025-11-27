<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\MartPage as OldMartPage;
use App\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Get pages for a project from MART database
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        // Get pages from MART database
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'No MART project found',
            ]);
        }

        $pages = MartPage::forProject($martProject->id)->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
            'message' => 'Pages retrieved successfully',
        ]);
    }

    /**
     * Store a new page in MART database
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'show_on_first_app_start' => 'boolean',
            'show_in_menu' => 'boolean',
            'button_text' => 'required|string|max:255',
            'sort_order' => 'integer|min:0',
            'page_type' => 'nullable|string|in:success,android_stats_permission,android_notification_permission,ios_notification_permission',
        ]);

        // Get or create MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            $martProject = MartProject::create(['main_project_id' => $project->id]);
        }

        $attributes['mart_project_id'] = $martProject->id;

        // If no sort_order provided, set to last
        if (! isset($attributes['sort_order'])) {
            $attributes['sort_order'] = MartPage::forProject($martProject->id)->count();
        }

        $page = MartPage::create($attributes);

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page created successfully',
        ], 201);
    }

    /**
     * Show a specific page from MART database
     */
    public function show(Project $project, MartPage $page): JsonResponse
    {
        $this->authorize('view', $project);

        // Get MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Ensure page belongs to project
        if ($page->mart_project_id !== $martProject->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page retrieved successfully',
        ]);
    }

    /**
     * Update a page in MART database
     */
    public function update(Request $request, Project $project, MartPage $page): JsonResponse
    {
        $this->authorize('update', $project);

        // Get MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Ensure page belongs to project
        if ($page->mart_project_id !== $martProject->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        $attributes = $request->validate([
            'name' => 'string|max:255',
            'content' => 'string',
            'show_on_first_app_start' => 'boolean',
            'show_in_menu' => 'boolean',
            'button_text' => 'string|max:255',
            'sort_order' => 'integer|min:0',
            'page_type' => 'nullable|string|in:success,android_stats_permission,android_notification_permission,ios_notification_permission',
        ]);

        $page->update($attributes);

        return response()->json([
            'success' => true,
            'data' => $page,
            'message' => 'Page updated successfully',
        ]);
    }

    /**
     * Delete a page from MART database
     */
    public function destroy(Project $project, MartPage $page): JsonResponse
    {
        $this->authorize('update', $project);

        // Get MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        // Ensure page belongs to project
        if ($page->mart_project_id !== $martProject->id) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found for this project',
            ], 404);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }

    /**
     * Bulk update page order in MART database
     */
    public function updateOrder(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        // Get MART project
        $martProject = $project->martProject();
        if (! $martProject) {
            return response()->json([
                'success' => false,
                'message' => 'MART project not found',
            ], 404);
        }

        $request->validate([
            'pages' => 'required|array',
            'pages.*.id' => 'required|integer',
            'pages.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->pages as $pageData) {
            $page = MartPage::find($pageData['id']);

            // Ensure page belongs to project
            if ($page && $page->mart_project_id === $martProject->id) {
                $page->update(['sort_order' => $pageData['sort_order']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Page order updated successfully',
        ]);
    }
}
